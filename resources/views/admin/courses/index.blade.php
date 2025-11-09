{{-- resources/views/admin/courses/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <div class="page-inner ms-lg-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Course Listing</h1>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">Create New Course</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($courses->count() == 0)
            <p>No courses found. <a href="{{ route('admin.courses.create') }}">Create one</a>.</p>
        @else
            <div class="accordion" id="coursesAccordion">
                @foreach ($courses as $courseIndex => $course)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center"
                            id="heading{{ $course->id }}">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed text-dark" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse{{ $course->id }}" aria-expanded="false">
                                    {{ $course->title }} <small class="text-muted">({{ $course->modules->count() }}
                                        modules)</small>
                                </button>
                            </h5>
                            <div>
                                @if ($course->status == 1)
                                <a href="{{route('admin.courses.status', $course)}}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Click to Draft Course">Published</a>
                                @else
                                <a href="{{route('admin.courses.status', $course)}}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Click to Publish Course">Drafted</a>
                                @endif
                                <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Click to Edit Course">Edit</a>
                                <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this course and all its content?')" data-bs-toggle="tooltip" title="Click to Delete Course">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div id="collapse{{ $course->id }}" class="collapse" data-bs-parent="#coursesAccordion">
                            <div class="card-body">
                                <div><strong>Description:</strong> {!! Str::limit($course->description, 200) !!}</div>

                                <p><strong>Category:</strong> {{ $course->category }}</p>
                                <p><strong>Level:</strong> {{ $course->level }}</p>
                                <p><strong>Price:</strong> ${{ $course->course_price }}</p>
                                @if ($course->feature_video)
                                    <p><strong>Feature Video:</strong></p>
                                    <video width="300" controls class="mb-3">
                                        <source src="{{ Storage::url($course->feature_video) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                @endif
                                @if ($course->feature_images)
                                    @foreach($course->feature_images as $img)
                                        <img src="{{ Storage::url($img) }}" width="100" class="img-thumbnail me-1">
                                    @endforeach
                                @endif
                                <hr>

                                <h5>Modules</h5>
                                @if ($course->modules->count() == 0)
                                    <p class="text-muted">No modules added.</p>
                                @else
                                    <div class="ms-3">
                                        @foreach ($course->modules as $module)
                                            <div class="border-start ps-3 mb-3" style="border-color: #007bff !important;">
                                                <h6>
                                                    <button class="btn btn-sm btn-link p-0 text-primary"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#module{{ $module->id }}">
                                                        {{ $module->title }}
                                                        <small>({{ $module->contents->count() }} items)</small>
                                                    </button>
                                                </h6>

                                                <div id="module{{ $module->id }}" class="collapse show">
                                                    <div class="ms-4">
                                                        @if ($module->contents->count() == 0)
                                                            <p class="text-muted">No content.</p>
                                                        @else
                                                            <ul class="list-group list-group-flush">
                                                                @foreach ($module->contents as $content)
                                                                    <li class="list-group-item py-2">
                                                                        <strong>{{ ucfirst($content->type) }}:</strong>
                                                                        @if ($content->type === 'text')
                                                                            <span>{{ Str::limit($content->data, 100) }}</span>
                                                                        @elseif($content->type === 'link')
                                                                            <a href="{{ $content->data }}"
                                                                                target="_blank">{{ $content->data }}</a>
                                                                        @elseif($content->type === 'image')
                                                                            <br>
                                                                            <img src="{{ Storage::url($content->data) }}"
                                                                                alt="Image" width="200"
                                                                                class="img-thumbnail mt-1">
                                                                        @elseif($content->type === 'video')
                                                                            <br>
                                                                            <video width="250" controls class="mt-1">
                                                                                <source
                                                                                    src="{{ Storage::url($content->data) }}"
                                                                                    type="video/mp4">
                                                                            </video>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
