<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Buat Sertifikat | ProCert</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="{{ $template ? 'template-success-page' : 'template-upload-page' }}">
    <div class="app-shell">

        @include('partials.wizard-header', [
            'currentStep' => 1,
            'showSteps' => false
        ])

        <main class="page-content {{ $template ? 'page-content--success' : 'page-content--upload' }}">
            
            @if ($template)
                <section class="page-intro">
                    <h1>Template berhasil diunggah!</h1>
                    <p>Pastikan desain yang Anda unggah sudah sesuai.</p>
                </section>

                @error('template')
                    <p class="form-error" role="alert">{{ $message }}</p>
                @enderror

                <div class="success-layout">
                    <div class="certificate-preview" aria-label="Pratinjau template sertifikat"> 
                        @if (str_starts_with($template['mime'], 'image/'))
                        <img
                            src="{{ route('templates.preview', [
                                'v' => ($template['id'] ?? 'upload') . '-' . $template['size'] . '-' . md5($template['name']) ]) }}"
                            alt="Pratinjau {{ $template['name'] }}"
                        >
                        @else
                            <iframe src="{{ route('templates.preview') }}"
                                title="Pratinjau {{ $template['name'] }}"></iframe>
                        @endif
                    </div>

                    <aside class="file-column" aria-label="Template yang diunggah">
                        <section class="file-panel">
                            <div class="file-panel-header">
                                
                                <span class="file-icon" aria-hidden="true">  
                                    <svg viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M13.5 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5L13.5 3Z"/>
                                        <path d="M13 3v7h7M8 14h8M8 17.5h8"/>
                                    </svg>
                                </span>

                                <span class="file-panel-copy">
                                    <strong title="{{ $template['name'] }}">
                                        {{ $template['name'] }}
                                    </strong>

                                    <span>
                                        {{ number_format($template['size'] / 1024 / 1024, 1) }} MB
                                        &nbsp; {{ $template['extension'] }}
                                    </span>
                                </span>

                                <form method="POST" action="{{ route('templates.destroy') }}">
                                    
                                    @csrf
                                    @method('DELETE')
                                    
                                    <button class="icon-button" type="submit"
                                            aria-label="Hapus template"
                                            title="Hapus template">

                                        <svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.7"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            
                                            <path d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <div class="file-panel-body">
                                @if (str_starts_with($template['mime'], 'image/'))
                                <div class="file-thumbnail">
                                    <img
                                        src="{{ route('templates.preview', [
                                            'v' => ($template['id'] ?? 'upload') . '-' . $template['size'] . '-' . md5($template['name']) ]) }}"
                                        alt=""
                                    >
                                </div>

                                @else

                                <div class="file-thumbnail file-thumbnail--pdf"
                                    aria-label="Dokumen PDF">
                                    <svg viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round">

                                        <path d="M13.5 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5L13.5 3Z"/>
                                        <path d="M13 3v7h7M8 15h8M8 18h5"/>
                                    </svg>
                                </div>
                                @endif

                                <form method="POST"
                                    action="{{ route('templates.store') }}"
                                    enctype="multipart/form-data"
                                    data-replace-form>

                                    @csrf
                                    <input class="visually-hidden"
                                        id="replace-template"
                                        name="template"
                                        type="file"
                                        accept="image/png,image/jpeg,application/pdf"
                                        data-replace-input>

                                    <label class="replace-picker" for="replace-template">
                                        <svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M7 7h13l-3-3M17 17H4l3 3M20 7l-4 3M4 17l4-3"/>
                                        </svg>
                                        Ganti File
                                    </label>
                                </form>
                            </div>
                        </section>

                        <div class="continue-area">
                            <a class="primary-button" href="{{ route('recipients.index') }}">
                                Lanjut
                                <svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">

                                    <path d="M5 12h14M13 6l6 6-6 6"/>
                                </svg>
                            </a>
                        </div>
                    </aside>
                </div>

            @else

                <div class="template-home-layout">

                    <!-- SIDEBAR CARI TEMPLATE -->
                    <aside class="template-sidebar" id="templateSidebar">
                        <div class="sidebar-top">
                            <div class="sidebar-heading">
                                <span class="sidebar-eyebrow">LIBRARY</span>
                                <h2 class="sidebar-title">Pilih Desain</h2>
                                
                                <p class="sidebar-subtitle">
                                    Temukan template sertifikat yang sesuai.
                                </p>
                            </div>

                            <button type="button"
                                    class="sidebar-close"
                                    id="closeSidebar"
                                    aria-label="Tutup sidebar">
                                &times;
                            </button>
                        </div>

                        <div class="sidebar-search-wrap">
                            <span class="sidebar-search-icon">
                                <svg viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round">
                                    <circle cx="11" cy="11" r="7"/>
                                    <path d="m16 16 4 4"/>
                                </svg>
                            </span>

                            <input type="search"
                                class="template-search"
                                id="templateSearch"
                                placeholder="Cari nama template...">
                        </div>

                        <div class="template-list" id="templateList">
                            
                            @foreach ($builtinTemplates as $id => $item)
                            <div class="template-card">
                                <form method="POST"
                                    action="{{ route('templates.select', ['id' => $id]) }}"
                                    class="template-item"
                                    data-template-name="{{ strtolower($item['name']) }}">

                                    @csrf
                                    <button type="submit" class="template-option">
                                        <img src="{{ asset($item['path']) }}"
                                            alt="{{ $item['name'] }}">
                                        <span>{{ $item['name'] }}</span>
                                    </button>
                                </form>

                                <button type="button"
                                    class="template-preview-button"
                                    data-preview-src="{{ asset($item['path']) }}"
                                    data-preview-name="{{ $item['name'] }}"
                                    aria-label="Preview {{ $item['name'] }}"
                                    title="Lihat preview">
                                    
                                    <svg viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round">

                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>

                            @endforeach
                            <p class="template-empty" id="templateEmpty" hidden>
                                Template tidak ditemukan.
                            </p>
                        </div>
                    </aside>

                    <!-- TOMBOL BUKA SIDEBAR -->
                    <button type="button"
                            class="sidebar-open is-visible"
                            id="openSidebar"
                            aria-label="Buka pencarian template">
                        &rsaquo;
                    </button>

                    <!-- BAGIAN UTAMA -->
                    <div class="template-main">
                        <section class="page-intro page-intro--hero">
                            <h1>Buat <span class="title-accent">Sertifikat</span></h1>
                            <p>Unggah desain sertifikat untuk memulai.</p>
                        </section>

                        @error('template')
                            <p class="form-error" role="alert">{{ $message }}</p>
                        @enderror

                        @if ($errors->has('template'))
                            <p class="template-error">
                                {{ $errors->first('template') }}
                            </p>
                        @endif

                        <form class="upload-card"
                            method="POST"
                            action="{{ route('templates.store') }}"
                            enctype="multipart/form-data"
                            data-upload-form>

                            @csrf
                            <div class="dropzone" data-dropzone>
                                <span class="upload-symbol" aria-hidden="true">
                                    <svg viewBox="0 0 48 48" fill="none"
                                        stroke="currentColor" stroke-width="4.5"
                                        stroke-linecap="round" stroke-linejoin="round">

                                        <rect x="3.5" y="4.5" width="41" height="39" rx="3"/>
                                        <path d="m9 35 10-11 7 8 5-6 8 9H9Z" fill="currentColor" stroke="none"/>
                                    </svg>
                                </span>

                                <h2>Drag &amp; Drop File Here</h2>
                                <p class="or-copy">atau</p>

                                <input class="visually-hidden"
                                    id="template-file"
                                    name="template"
                                    type="file"
                                    accept="image/png,image/jpeg,application/pdf"
                                    data-template-input>

                                <button class="file-picker"
                                        type="button"
                                        data-file-picker>

                                        Pilih File
                                </button>

                                <p class="format-copy">
                                    Format: PNG, JPG, atau PDF<br>
                                    Ukuran maks. 10 MB
                                </p>

                                <div class="selected-file"
                                    data-selected-file
                                    aria-live="polite"
                                    hidden>

                                    <span class="selected-file-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">

                                            <path d="M13.5 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5L13.5 3Z"/>
                                            <path d="M13 3v7h7M8 14h8M8 17.5h8"/>
                                        </svg>
                                    </span>

                                    <span class="selected-file-copy">
                                        <strong data-file-name></strong>

                                        <span>
                                            <span data-file-size></span>
                                            &middot;
                                            <span data-file-status></span>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </main>

        <!-- TEMPLATE PREVIEW MODAL -->
        <div class="template-preview-modal"
            id="templatePreviewModal"
            aria-hidden="true">

            <div class="template-preview-content"
                role="dialog"
                aria-modal="true"
                aria-labelledby="templatePreviewTitle">

                <button type="button"
                    class="template-preview-close"
                    id="closeTemplatePreview"
                    aria-label="Tutup preview">
                    &times;
                </button>

                <div class="template-preview-heading"
                    id="templatePreviewTitle">
                    Preview Template
                </div>

                <div class="template-preview-image-wrap">
                    <img src=""
                        alt="Preview template sertifikat"
                        class="template-preview-image"
                        id="templatePreviewImage">
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('templateSidebar');
            const openButton = document.getElementById('openSidebar');
            const closeButton = document.getElementById('closeSidebar');
            const searchInput = document.getElementById('templateSearch');
            const templateItems = document.querySelectorAll('.template-item');
            const emptyMessage = document.getElementById('templateEmpty');

            if (sidebar && openButton && closeButton) {
                openButton.addEventListener('click', function () {
                    sidebar.classList.add('is-open');

                    openButton.classList.remove('is-visible');

                    sidebar.setAttribute('aria-hidden', 'false');
                    openButton.setAttribute('aria-expanded', 'true');
                });

                closeButton.addEventListener('click', function () {
                    sidebar.classList.remove('is-open');

                    openButton.classList.add('is-visible');

                    sidebar.setAttribute('aria-hidden', 'true');
                    openButton.setAttribute('aria-expanded', 'false');
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const keyword = this.value.toLowerCase().trim();
                    let visibleCount = 0;

                    templateItems.forEach(function (item) {
                        const name = item.dataset.templateName || '';
                        const matches = name.includes(keyword);
                        item.hidden = !matches;

                            if (matches) {
                                visibleCount++;
                            }
                    });

                    if (emptyMessage) {
                        emptyMessage.hidden = visibleCount !== 0;
                    }
                });
            }

            // Form upload template yang sudah ada
            const fileInput = document.querySelector('[data-template-input]');
            const filePicker = document.querySelector('[data-file-picker]');
            const uploadForm = document.querySelector('[data-upload-form]');
            const dropzone = document.querySelector('[data-dropzone]');

            if (fileInput && filePicker) {
                filePicker.addEventListener('click', function () {
                    fileInput.click();
                });

                fileInput.addEventListener('change', function () {
                    if (this.files.length > 0 && uploadForm) {
                        uploadForm.submit();
                    }
                });
            }

            if (dropzone && fileInput && uploadForm) {
                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.add('is-dragging');
                    });

                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.remove('is-dragging');
                    });
                });

                dropzone.addEventListener('drop', function (event) {
                    const files = event.dataTransfer.files;

                    if (files.length > 0) {
                        fileInput.files = files;
                        uploadForm.submit();
                    }
                });
            }

            // Form ganti template
            const replaceInput = document.querySelector('[data-replace-input]');
            const replaceForm = document.querySelector('[data-replace-form]');

            if (replaceInput && replaceForm) {
                replaceInput.addEventListener('change', function () {
                    if (this.files.length > 0) {
                        replaceForm.submit();
                    }
                });
            }

            // Preview template bawaan
            const previewModal = document.getElementById('templatePreviewModal');
            const previewImage = document.getElementById('templatePreviewImage');
            const previewTitle = document.getElementById('templatePreviewTitle');
            const closePreviewButton = document.getElementById('closeTemplatePreview');
            const previewButtons = document.querySelectorAll('.template-preview-button');

            function closeTemplatePreview() {
                if (!previewModal) return;
                    previewModal.classList.remove('is-active');
                    previewModal.setAttribute('aria-hidden', 'true');
                    previewImage.src = '';
                    document.body.style.overflow = '';
            }

            previewButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const imageSrc = this.dataset.previewSrc;
                    const templateName = this.dataset.previewName;

                    if (!previewModal || !previewImage) return;
                    previewImage.src = imageSrc;
                    previewImage.alt = 'Preview ' + templateName;
                    previewTitle.textContent = templateName;
                    previewModal.classList.add('is-active');
                    previewModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                });
            });

            if (closePreviewButton) {
                    closePreviewButton.addEventListener('click', closeTemplatePreview);
            }

            if (previewModal) {
                previewModal.addEventListener('click', function (event) {
                    if (event.target === previewModal) {
                        closeTemplatePreview();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeTemplatePreview();
                }
            });
        });
    </script>
</body>
</html>
