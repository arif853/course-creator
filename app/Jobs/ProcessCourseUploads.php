<?php

namespace App\Jobs;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessCourseUploads implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $courseId;
    protected $tempPaths;

    public function __construct($courseId, array $tempPaths)
    {
        $this->courseId = $courseId;
        $this->tempPaths = $tempPaths;
    }

    public function handle()
    {
        $course = Course::findOrFail($this->courseId);
        $finalPaths = ['feature_images' => []];

        try {
            // Move Feature Video
            if (!empty($this->tempPaths['feature_video'])) {
                $tempPath = storage_path('app/' . $this->tempPaths['feature_video']);
                $finalPath = "videos/courses/{$course->id}/" . basename($this->tempPaths['feature_video']);

                Storage::disk('public')->putFileAs(
                    dirname($finalPath),
                    $tempPath,
                    basename($finalPath)
                );

                $finalPaths['feature_video'] = $finalPath;
                @unlink($tempPath); // delete temp
            }

            // Move Feature Images
            foreach ($this->tempPaths['feature_images'] as $tempImagePath) {
                $tempFullPath = storage_path('app/' . $tempImagePath);
                $finalImagePath = "images/courses/{$course->id}/" . basename($tempImagePath);

                Storage::disk('public')->putFileAs(
                    dirname($finalImagePath),
                    $tempFullPath,
                    basename($finalImagePath)
                );

                $finalPaths['feature_images'][] = $finalImagePath;
                @unlink($tempFullPath);
            }

            // Update course
            $course->update([
                'feature_video'   => $finalPaths['feature_video'] ?? null,
                'feature_images'  => $finalPaths['feature_images'],
            ]);

        } catch (\Exception $e) {
            Log::error("Upload job failed for course {$course->id}: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
