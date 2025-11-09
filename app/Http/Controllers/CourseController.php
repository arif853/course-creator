<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Jobs\ProcessCourseUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::with(['modules.contents'])->latest()->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'category'       => 'required|string|max:255',
            'level'          => 'required|string|max:100',
            'course_price'   => 'required|numeric|min:0',
            'feature_video'  => 'required|file|mimes:mp4,avi,mov,qt|max:204800',
            'feature_image.*'=> 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'feature_image'  => 'array|min:1|max:10',

            'modules'        => 'required|array|min:1',
            'modules.*.title'=> 'required|string|max:255',
            'modules.*.contents' => 'array',
            'modules.*.contents.*.type' => 'required|in:text,image,video,link',
            'modules.*.contents.*.data' => 'required',
        ]);

        DB::beginTransaction();
        try {
            // Create course
            $course = Course::create([
                'title'        => $request->title,
                'description'  => $request->description,
                'category'     => $request->category,
                'level'        => $request->level,
                'course_price' => $request->course_price,
            ]);

            // Handle content files immediately (small)
            foreach ($request->modules as $mIdx => $moduleData) {
                $module = $course->modules()->create(['title' => $moduleData['title']]);

                if (!empty($moduleData['contents'])) {
                    foreach ($moduleData['contents'] as $cIdx => $contentData) {
                        $type = $contentData['type'];
                        $data = $contentData['data'];

                        if (in_array($type, ['image', 'video'])) {
                            $file = $request->file("modules.{$mIdx}.contents.{$cIdx}.data");
                            if ($file) {
                                $path = $file->store("content/{$type}s/{$course->id}/{$module->id}", 'public');
                                $data = $path;
                            }
                        }

                        $module->contents()->create(['type' => $type, 'data' => $data]);
                    }
                }
            }

            // Store large files in temp directory
            $tempPaths = [
                'feature_video' => null,
                'feature_images' => [],
            ];

            if ($request->hasFile('feature_video')) {
                $tempPaths['feature_video'] = $request->file('feature_video')
                    ->storeAs('temp/course_' . $course->id, 'feature_video.' . $request->file('feature_video')->extension(), 'local');
            }

            if ($request->hasFile('feature_image')) {
                foreach ($request->file('feature_image') as $index => $image) {
                    $ext = $image->extension();
                    $tempPaths['feature_images'][] = $image->storeAs(
                        'temp/course_' . $course->id,
                        "feature_image_{$index}.{$ext}",
                        'local'
                    );
                }
            }

            DB::commit();

            // Dispatch job with **paths only**
            ProcessCourseUploads::dispatch($course->id, $tempPaths)
                ->afterCommit()
                ->onQueue('uploads');

            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'Course created! Media is being uploaded in the background.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Course creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create course.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function status(Course $course)
    {
        $course->update(['status' => !$course->status]);
        return back()->with('success', 'Status updated successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $course->load('modules.contents');
        return view('admin.courses.edit', compact('course'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'category'     => 'required|string|max:255',
            'level'        => 'required|string|max:100',
            'course_price' => 'required|numeric|min:0',

            'feature_video'  => 'nullable|file|mimes:mp4,avi,mov,qt|max:204800',
            'feature_image.*'=> 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'feature_image'  => 'array|max:10',

            'modules'                     => 'required|array|min:1',
            'modules.*.title'             => 'required|string|max:255',
            'modules.*.contents'          => 'nullable|array',
            // 'modules.*.contents.*.type'   => 'required_with:modules.*.contents.*|in:text,image,video,link',
            // 'modules.*.contents.*.data'   => 'required_if:modules.*.contents.*.type,text,link',
            // 'modules.*.contents.*.data'   => 'required_if:modules.*.contents.*.type,image,video',
        ]);

        DB::beginTransaction();
        try {
            $course->update($request->only([
                'title', 'description', 'category', 'level', 'course_price'
            ]));

            $existingModuleIds = $course->modules->pluck('id')->toArray();

            foreach ($request->input('modules', []) as $mIdx => $moduleData) {
                $moduleId = $moduleData['id'] ?? null;
                if ($moduleId && in_array($moduleId, $existingModuleIds)) {
                    $module = $course->modules()->find($moduleId);
                    $module->update(['title' => $moduleData['title']]);
                } else {
                    $module = $course->modules()->create(['title' => $moduleData['title']]);
                }

                $existingContentIds = $module->contents->pluck('id')->toArray();

                foreach ($moduleData['contents'] ?? [] as $cIdx => $contentData) {
                    $contentId = $contentData['id'] ?? null;
                    $type = $contentData['type'];
                    $data = $contentData['data'] ?? null;

                    if (in_array($type, ['image', 'video'])) {
                        $fileKey = "modules.{$mIdx}.contents.{$cIdx}.data";
                        if ($request->hasFile($fileKey)) {
                            $file = $request->file($fileKey);
                            $data = $file->store("content/{$type}s/{$course->id}/{$module->id}", 'public');
                        } elseif ($contentId && in_array($contentId, $existingContentIds)) {
                            $old = $module->contents()->find($contentId);
                            $data = $old->data;
                        }
                    }
                    if ($contentId && in_array($contentId, $existingContentIds)) {
                        $module->contents()->find($contentId)->update(['type' => $type, 'data' => $data]);
                    } else {
                        $module->contents()->create(['type' => $type, 'data' => $data]);
                    }
                }
                $submittedIds = collect($moduleData['contents'] ?? [])->pluck('id')->filter()->toArray();
                $toDelete = array_diff($existingContentIds, $submittedIds);
                $module->contents()->whereIn('id', $toDelete)->delete();
            }
            $submittedModuleIds = collect($request->input('modules', []))->pluck('id')->filter()->toArray();
            $toDeleteModules = array_diff($existingModuleIds, $submittedModuleIds);
            $course->modules()->whereIn('id', $toDeleteModules)->delete();

            DB::commit();

            $tempPaths = ['feature_video' => null, 'feature_images' => []];
            $keptImages = $request->input('existing_feature_images', []);

            $currentImages = array_intersect((array)$course->feature_images, $keptImages);

            if ($request->hasFile('feature_video')) {
                $tempPaths['feature_video'] = $request->file('feature_video')
                    ->storeAs('temp/course_' . $course->id, 'feature_video.' . $request->file('feature_video')->extension(), 'local');
            }

            if ($request->hasFile('feature_image')) {
                foreach ($request->file('feature_image') as $index => $image) {
                    $ext = $image->extension();
                    $tempPaths['feature_images'][] = $image->storeAs(
                        'temp/course_' . $course->id,
                        "feature_image_{$index}.{$ext}",
                        'local'
                    );
                }
            }
            if ($tempPaths['feature_video'] || !empty($tempPaths['feature_images'])) {
                ProcessCourseUploads::dispatch($course->id, $tempPaths, $currentImages)
                    ->afterCommit()
                    ->onQueue('uploads');
            }

            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'Course updated successfully! Media is being processed.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Course update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        // Delete associated files
        if ($course->feature_video) {
            Storage::disk('public')->delete($course->feature_video);
        }

        foreach ($course->modules as $module) {
            foreach ($module->contents as $content) {
                if (in_array($content->type, ['image', 'video'])) {
                    Storage::disk('public')->delete($content->data);
                }
            }
        }

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
