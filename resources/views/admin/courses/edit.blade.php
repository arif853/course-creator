@extends('layouts.admin')

@section('content')
<div class="page-inner ms-lg-0">
    <h1 class="mb-4">Edit Course – {{ $course->title }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="courseForm" method="POST"
          action="{{ route('admin.courses.update', $course) }}"
          enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Course Title</label>
                    <input type="text" name="title" class="form-control"
                           value="{{ old('title', $course->title) }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Feature Video <small class="text-muted">(optional – leave blank to keep current)</small></label>
                    <input type="file" name="feature_video" accept="video/*" class="form-control">
                    @if($course->feature_video)
                        <video width="250" controls class="mt-2 d-block">
                            <source src="{{ Storage::url($course->feature_video) }}" type="video/mp4">
                        </video>
                    @endif
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Level</label>
                    <input type="text" name="level" class="form-control"
                           value="{{ old('level', $course->level) }}" required>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control"
                           value="{{ old('category', $course->category) }}" required>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="course_price" class="form-control"
                           value="{{ old('course_price', $course->course_price) }}" required>
                </div>
            </div>

            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control summernote" required>{{ old('description', $course->description) }}</textarea>
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold mb-2">Feature Images</label>
                <div id="imageDropzone" class="imageDropzone">
                    <p class="mb-0 text-secondary">Drag & drop images here or click to upload</p>
                    <input type="file" id="feature_image" name="feature_image[]" accept="image/*" multiple hidden>
                    <div id="preview-container" class="preview-container">
                        @foreach($course->feature_images ?? [] as $path)
                            <div class="image-preview existing" data-path="{{ $path }}">
                                <img src="{{ Storage::url($path) }}" alt="">
                                <button type="button" class="remove-btn">×</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="col-md-12 mt-4">
                <h2>Modules</h2>
                <div id="modulesAccordion" class="accordion mt-3"></div>

                <div class="d-flex gap-2 mt-3">
                    <button type="button" id="addModule" class="btn btn-primary">Add Module</button>
                    <button type="submit" class="btn btn-success">Update Course</button>
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let moduleIndex = 0;
    const $accordion = $('#modulesAccordion');

    @foreach ($course->modules as $module)
        addModule(
            {!! $module->toJson() !!},
            {!! json_encode([
                'contents' => $module->contents->map(function ($c) {
                    return [
                        'id'   => $c->id,
                        'type' => $c->type,
                        'data' => $c->data,
                        'url'  => in_array($c->type, ['image', 'video']) ? Storage::url($c->data) : $c->data,
                    ];
                })->toArray()
            ]) !!}
        );
    @endforeach

    $('#addModule').on('click', () => addModule());

    function addModule(module = null, extra = {}) {
        const id        = module?.id ?? '';
        const title     = module?.title ?? '';
        const contents  = extra.contents ?? module?.contents ?? [];

        const accId     = `moduleAcc_${moduleIndex}`;
        const collId    = `moduleColl_${moduleIndex}`;

        const html = `
            <div class="accordion-item module" data-index="${moduleIndex}" data-id="${id}">
                <h2 class="accordion-header" id="heading_${accId}">
                    <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#${collId}"
                            aria-expanded="false" aria-controls="${collId}">
                        <span class="module-title">${title || 'New Module'}</span>
                        <input type="hidden" name="modules[${moduleIndex}][id]" value="${id}">
                    </button>
                </h2>

                <div id="${collId}" class="accordion-collapse collapse"
                     aria-labelledby="heading_${accId}" data-bs-parent="#modulesAccordion">
                    <div class="accordion-body">
                        <input type="text" name="modules[${moduleIndex}][title]"
                               class="form-control module-title-input mb-3"
                               value="${escapeHtml(title)}" placeholder="Module title" required>

                        <div class="contentsContainer"></div>

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-secondary addContent">Add Content</button>
                            <button type="button" class="btn btn-sm btn-danger removeModule">Remove Module</button>
                        </div>
                    </div>
                </div>
            </div>`;

        $accordion.append(html);
        const $container = $accordion.find(`[data-index="${moduleIndex}"] .contentsContainer`);
        contents.forEach(c => addContent($container, c, moduleIndex));
        moduleIndex++;
    }

    function addContent($container, content = null, mIdx) {
        const cIdx = $container.find('.content').length;
        const id   = content?.id ?? '';
        const type = content?.type ?? 'text';
        const data = content?.data ?? '';
        const url  = content?.url ?? '';

        const html = `
            <div class="content card p-3 mb-2" data-index="${cIdx}">
                <input type="hidden" name="modules[${mIdx}][contents][${cIdx}][id]" value="${id}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <select name="modules[${mIdx}][contents][${cIdx}][type]"
                                class="form-select contentType" required>
                            <option value="text"   ${type==='text'?'selected':''}>Text</option>
                            <option value="image"  ${type==='image'?'selected':''}>Image</option>
                            <option value="video"  ${type==='video'?'selected':''}>Video</option>
                            <option value="link"   ${type==='link'?'selected':''}>Link</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <div class="dataInput">
                            ${inputHtml(type, data, url, mIdx, cIdx)}
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm removeContent">×</button>
                    </div>
                </div>
            </div>`;

        $container.append(html);
    }
    function inputHtml(type, data, url, mIdx, cIdx) {
        const name = `modules[${mIdx}][contents][${cIdx}][data]`;

        if (type === 'text')
            return `<textarea name="${name}" class="form-control" required>${escapeHtml(data)}</textarea>`;

        if (type === 'link')
            return `<input type="url" name="${name}" class="form-control" value="${escapeHtml(data)}" required>`;

        const preview = type === 'image'
            ? `<img src="${url}" width="120" class="mt-2 d-block img-thumbnail" alt="Current">`
            : `<video width="180" controls class="mt-2 d-block"><source src="${url}"></video>`;

        return `
            <input type="file" name="${name}" accept="${type}/*" class="form-control">
            <small class="text-muted d-block mt-1">Current file:</small>
            ${preview}
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    $(document).on('change', '.contentType', function () {
        const $content = $(this).closest('.content');
        const type = $(this).val();
        const mIdx = $content.closest('.module').data('index');
        const cIdx = $content.data('index');

        const currentData = $content.find('textarea, input[type=url]').val() || '';
        const currentUrl = $content.find('img, video source').attr('src') || '';

        $content.find('.dataInput').html(inputHtml(type, currentData, currentUrl, mIdx, cIdx));
    });

    $(document).on('click', '.removeModule', () => $(this).closest('.accordion-item').remove());
    $(document).on('click', '.removeContent', () => $(this).closest('.content').remove());

    $(document).on('input', '.module-title-input', function () {
        const title = $(this).val().trim() || 'New Module';
        $(this).closest('.accordion-item').find('.module-title').text(title);
    });

    $('#courseForm').on('submit', function () {
        const $form = $(this);

        $form.find('.module').each(function (mIdx) {
            const $module = $(this);
            $module.attr('data-index', mIdx);

            $module.find('input, select, textarea').each(function () {
                const $el = $(this);
                const name = $el.attr('name');
                if (name && name.includes('modules[')) {
                    $el.attr('name', name.replace(/modules\[\d+\]/, `modules[${mIdx}]`));
                }
            });

            $module.find('.content').each(function (cIdx) {
                const $content = $(this);
                $content.attr('data-index', cIdx);

                $content.find('input, select, textarea').each(function () {
                    const $el = $(this);
                    const name = $el.attr('name');
                    if (name && name.includes('contents[')) {
                        $el.attr('name', name.replace(/contents\[\d+\]/, `contents[${cIdx}]`));
                    }
                });
            });
        });
    });

    (function () {
        const $dropzone = $('#imageDropzone');
        const $fileInput = $('#feature_image');
        const $previewContainer = $('#preview-container');
        let dataTransfer = new DataTransfer();

        $dropzone.on('click', () => $fileInput.click());
        $fileInput.on('change', e => { handleFiles(e.target.files); e.target.value = ''; });
        $dropzone.on('dragover', e => { e.preventDefault(); $dropzone.addClass('dragover'); });
        $dropzone.on('dragleave drop', e => { e.preventDefault(); $dropzone.removeClass('dragover'); });
        $dropzone.on('drop', e => handleFiles(e.originalEvent.dataTransfer.files));

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
                        </div>`);
                    $previewContainer.append($prev);
                    simulateProgress($prev.find('.progress-bar'));
                };
                reader.readAsDataURL(file);
            }
            $fileInput[0].files = dataTransfer.files;
        }

        $previewContainer.on('click', '.remove-btn', function () {
            const $prev = $(this).closest('.image-preview');
            const idx = $previewContainer.children('.image-preview').index($prev);
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

        let dragged;
        $previewContainer.on('dragstart', '.image-preview', function () { dragged = this; $(this).css('opacity', .5); });
        $previewContainer.on('dragend', '.image-preview', () => $(dragged).css('opacity', 1));
        $previewContainer.on('dragover', '.image-preview', function (e) {
            e.preventDefault();
            if (dragged === this) return;
            const $prevs = $previewContainer.children('.image-preview');
            const from = $prevs.index(dragged);
            const to = $prevs.index(this);
            if (from < to) $(this).after(dragged); else $(this).before(dragged);
            const newDT = new DataTransfer();
            $prevs.each((i, el) => { const f = $fileInput[0].files[i]; if (f) newDT.items.add(f); });
            dataTransfer = newDT;
            $fileInput[0].files = dataTransfer.files;
        });

        // Send existing images
        $('.image-preview.existing').each(function () {
            const path = $(this).data('path');
            $(this).append(`<input type="hidden" name="existing_feature_images[]" value="${path}">`);
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
