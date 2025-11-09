@extends('layouts.admin')

@section('content')
    <div class="page-inner ms-lg-0">
        <h1>Create Course</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form id="courseForm" method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Course Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="feature_video" class="form-label">Feature Video</label>
                        <input type="file" id="feature_video" name="feature_video" accept="video/*" required class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="level" class="form-label">Level</label>
                        <input type="text" id="level" name="level" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" id="category" name="category" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="course_price" class="form-label">Course Price</label>
                        <input type="text" id="course_price" name="course_price" class="form-control" required>
                        <small>If the course price is $0, the user can enroll in the course for free.
                             Otherwise the user must pay the course price.</small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control summernote" required></textarea>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold mb-2">Feature Images</label>
                    <div id="imageDropzone" class="imageDropzone">
                        <p class="mb-0 text-secondary">Drag & drop images here or click to upload</p>
                        <input type="file" id="feature_image" name="feature_image[]" accept="image/*" multiple hidden>
                        <div id="preview-container" class="preview-container"></div>
                    </div>
                </div>
                <div class="col-md-12">
                    <h2 class="mt-4">Modules</h2>
                    <div id="modulesAccordion" class="accordion mt-3"></div>

                </div>
                <div class="col-md-12 d-flex justify-content-between  mt-3">
                    <button type="button" id="addModule" class="btn btn-primary me-2">Add Module</button>
                    <button type="submit" class="btn btn-success ">Save Course</button>
                </div>
            </div>


        </form>
    </div>

@endsection
@push('scripts')
    <script>

    let moduleIndex = 0;
    const $modulesAccordion = $('#modulesAccordion');

    $('#addModule').on('click', function () {
        const accordionId   = `moduleAccordion_${moduleIndex}`;
        const collapseId     = `moduleCollapse_${moduleIndex}`;

        const moduleHtml = `
            <div class="accordion-item module" data-index="${moduleIndex}">
                <h2 class="accordion-header" id="heading_${accordionId}">
                    <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#${collapseId}"
                            aria-expanded="false"
                            aria-controls="${collapseId}">
                        <span class="module-title">Module ${moduleIndex + 1}</span>
                        <input type="hidden" name="modules[${moduleIndex}][index]" value="${moduleIndex}">
                    </button>
                </h2>

                <div id="${collapseId}" class="accordion-collapse collapse"
                     aria-labelledby="heading_${accordionId}"
                     data-bs-parent="#modulesAccordion">

                    <div class="accordion-body">
                        <!-- Module Title Input -->
                        <div class="mb-3">
                            <label class="form-label">Module Title</label>
                            <input type="text"
                                   name="modules[${moduleIndex}][title]"
                                   class="form-control module-title-input"
                                   placeholder="Enter module title"
                                   required>
                        </div>

                        <!-- Contents Section -->
                        <h5>Contents</h5>
                        <div class="contentsContainer nested mb-3"></div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-secondary addContent">Add Content</button>
                            <button type="button" class="btn btn-sm btn-danger removeModule">Remove Module</button>
                        </div>
                    </div>
                </div>
            </div>`;

        $modulesAccordion.append(moduleHtml);
        moduleIndex++;
    });

    $(document).on('input', '.module-title-input', function () {
        const $module = $(this).closest('.module');
        const title   = $(this).val().trim() || `Module ${$module.data('index') + 1}`;
        $module.find('.module-title').text(title);
    });

    $(document).on('click', '.addContent', function () {
        const $module       = $(this).closest('.module');
        const moduleIdx     = $module.data('index');
        const contentIndex  = $module.find('.content').length;

        const contentHtml = `
            <div class="content card mb-2 p-3" data-index="${contentIndex}">
                <input type="hidden"
                       name="modules[${moduleIdx}][contents][${contentIndex}][index]"
                       value="${contentIndex}">

                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="modules[${moduleIdx}][contents][${contentIndex}][type]"
                                class="form-select contentType" required>
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="video">Video</option>
                            <option value="link">Link</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Data</label>
                        <div class="dataInput">
                            <textarea name="modules[${moduleIdx}][contents][${contentIndex}][data]"
                                      class="form-control" required></textarea>
                        </div>
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm removeContent">x</button>
                    </div>
                </div>
            </div>`;

        $module.find('.contentsContainer').append(contentHtml);
    });

    $(document).on('change', '.contentType', function () {
        const $dataInput = $(this).closest('.content').find('.dataInput');
        const type       = $(this).val();

        let html = '';
        if (type === 'text') {
            html = `<textarea name="${$(this).attr('name').replace('[type]', '[data]')}"
                             class="form-control" required></textarea>`;
        } else if (type === 'link') {
            html = `<input type="url" name="${$(this).attr('name').replace('[type]', '[data]')}"
                           class="form-control" required>`;
        } else {
            const accept = type === 'image' ? 'image/*' : 'video/*';
            html = `<input type="file"
                           name="${$(this).attr('name').replace('[type]', '[data]')}"
                           accept="${accept}" class="form-control" required>`;
        }
        $dataInput.html(html);
    });

    $(document).on('click', '.removeModule', function () {
        $(this).closest('.accordion-item').remove();
    });

    $(document).on('click', '.removeContent', function () {
        $(this).closest('.content').remove();
    });

    $(document).ready(function () {
        $('#addModule').trigger('click');
        $('.accordion-button').first().removeClass('collapsed').attr('aria-expanded', 'true');
        $('.accordion-collapse').first().addClass('show');
    });

        $('#courseForm').submit(function(e) {
            let valid = true;
            if (!valid) {
                e.preventDefault();
            }
        });

    (function () {
        const $dropzone        = $('#imageDropzone');
        const $fileInput       = $('#feature_image');
        const $previewContainer = $('#preview-container');
        let dataTransfer       = new DataTransfer();
        let dragged            = null;

        $dropzone.on('click', () => $fileInput.click());

        $fileInput.on('change', e => {
            handleFiles(e.target.files);
            e.target.value = '';   // reset
        });
        $dropzone.on('dragover', e => {
            e.preventDefault();
            $dropzone.addClass('dragover');
        });

        $dropzone.on('dragleave drop', e => {
            e.preventDefault();
            $dropzone.removeClass('dragover');
        });

        $dropzone.on('drop', e => {
            const files = e.originalEvent.dataTransfer.files;
            handleFiles(files);
        });
        function handleFiles(files) {
            for (const file of files) {
                if (!file.type.startsWith('image/')) continue;

                dataTransfer.items.add(file);

                const reader = new FileReader();
                reader.onload = ev => {
                    const $prev = $(`
                        <div class="image-preview" draggable="true">
                            <img src="${ev.target.result}" alt="">
                            <button type="button" class="remove-btn">×</button>
                            <div class="progress"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                        </div>
                    `);
                    $previewContainer.append($prev);
                    simulateProgress($prev.find('.progress-bar'));
                };
                reader.readAsDataURL(file);
            }

            // Sync with hidden input
            $fileInput[0].files = dataTransfer.files;
        }
        $previewContainer.on('click', '.remove-btn', function () {
            const $prev = $(this).closest('.image-preview');
            const idx   = Array.from($previewContainer.children('.image-preview')).indexOf($prev[0]);

            dataTransfer.items.remove(idx);
            $fileInput[0].files = dataTransfer.files;
            $prev.remove();
        });
        function simulateProgress($bar) {
            let w = 0;
            const int = setInterval(() => {
                w += 10;
                $bar.css('width', w + '%');
                if (w >= 100) clearInterval(int);
            }, 80);
        }
        $previewContainer.on('dragstart', '.image-preview', function () {
            dragged = this;
            $(this).css('opacity', 0.5);
        });

        $previewContainer.on('dragend', '.image-preview', function () {
            $(this).css('opacity', 1);
            dragged = null;
        });

        $previewContainer.on('dragover', '.image-preview', function (e) {
            e.preventDefault();
            if (!dragged || dragged === this) return;

            const $prevs = $previewContainer.children('.image-preview');
            const from   = Array.from($prevs).indexOf(dragged);
            const to     = Array.from($prevs).indexOf(this);

            if (from < to) $(this).after(dragged);
            else $(this).before(dragged);
        });
        $previewContainer.on('drop', function (e) {
            e.preventDefault();
            const newDT = new DataTransfer();
            $previewContainer.children('.image-preview').each((i, el) => {
                const file = $fileInput[0].files[i];
                if (file) newDT.items.add(file);
            });
            dataTransfer = newDT;
            $fileInput[0].files = dataTransfer.files;
        });
    })();
    </script>
@endpush
@push('styles')
<style>
    .imageDropzone{border:2px dashed #6c757d;border-radius:8px;padding:30px;text-align:center;background:#f8f9fa;cursor:pointer;transition:.2s;}
    .imageDropzone.dragover{background:#e9f5ff;border-color:#0d6efd;}
    .preview-container{display:flex;flex-wrap:wrap;gap:10px;margin-top:15px;}
    .image-preview{position:relative;width:120px;height:120px;border-radius:8px;overflow:hidden;border:1px solid #dee2e6;background:#fff;cursor:move;}
    .image-preview img{width:100%;height:100%;object-fit:cover;}
    .remove-btn{position:absolute;top:5px;right:5px;background:rgba(0,0,0,.6);border:none;color:#fff;font-size:14px;line-height:1;border-radius:50%;width:22px;height:22px;text-align:center;cursor:pointer;}
    .progress{height:5px;margin-top:5px;}
</style>
@endpush
