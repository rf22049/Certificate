<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    
    <title>{{ __('Create Certificate') }} | ProCert</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="{{ $template ? 'template-success-page' : 'template-upload-page' }}">
        <div class="app-shell">

        @include('partials.wizard-header', [
            'currentStep' => 1,
            'showSteps' => false,
            'showExcel' => !$template
        ])

        <main class="page-content {{ $template ? 'page-content--success' : 'page-content--upload' }}">  
            @if ($template)
                <section class="design-workspace-page">
                    @error('template')
                        <p class="form-error" role="alert">{{ $message }}</p>
                    @enderror

                    {{-- Main two-column workspace --}}
                    <div class="success-layout workspace-layout">

                        {{-- LEFT: Certificate workspace --}}
                        <section class="certificate-workspace"
                            aria-label="{{ __('Certificate workspace') }}">

                            <div class="canvas-toolbar">
                                <div class="canvas-zoom-controls">
                                    <button type="button" data-zoom-out
                                        aria-label="{{ __('Zoom out') }}"
                                        title="{{ __('Zoom out') }}">
                                        −
                                    </button>

                                    <output data-zoom-value>100%</output>

                                    <button type="button" data-zoom-in
                                        aria-label="{{ __('Zoom in') }}"
                                        title="{{ __('Zoom in') }}">
                                        +
                                    </button>

                                    <span class="canvas-toolbar-divider"></span>

                                    <button type="button" data-zoom-fit
                                        aria-label="{{ __('Fit to workspace') }}"
                                        title="{{ __('Fit to workspace') }}">
                                        <svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            aria-hidden="true">
                                            <path d="M8 3H5a2 2 0 0 0-2 2v3m13-5h3a2 2 0 0 1 2 2v3M3 16v3a2 2 0 0 0 2 2h3m13-5v3a2 2 0 0 1-2 2h-3"/>
                                        </svg>
                                    </button>                                
                                </div>
                            </div>

                            <div class="canvas-viewport" data-canvas-viewport>
                                <div class="canvas-stage" data-canvas-stage>
                                    @if (str_starts_with($template['mime'], 'image/'))
                                        <img
                                            class="canvas-template-image"
                                            data-canvas-image
                                            src="{{ route('templates.preview', [
                                                'v' => ($template['id'] ?? 'upload')
                                                    . '-' . $template['size']
                                                    . '-' . md5($template['name'])
                                            ]) }}"
                                            alt="{{ __('Preview :name', ['name' => $template['name']]) }}"
                                            draggable="false"
                                        >
                                    @else
                                        <iframe
                                            class="canvas-template-pdf"
                                            data-canvas-pdf
                                            src="{{ route('templates.preview') }}"
                                            title="{{ __('Preview :name', ['name' => $template['name']]) }}"
                                        ></iframe>
                                    @endif

                                </div>
                            </div>

                            <div class="canvas-footer">
                                <span>{{ __('Scroll inside the workspace to view the template.') }}</span>
                                <span data-canvas-dimensions> </span>
                            </div>
                        </section>

                        {{-- RIGHT: File information --}}
                        <aside class="file-column workspace-file-column"
                            aria-label="{{ __('Uploaded template') }}">

                            <section class="file-panel workspace-file-panel">

                                <div class="file-panel-header workspace-file-header">
                                    <span class="file-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M13.5 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5L13.5 3Z"/>
                                            <path d="M13 3v7h7M8 14h8M8 17.5h8"/>
                                        </svg>
                                    </span>

                                    <div class="file-panel-copy">
                                        <strong title="{{ $template['name'] }}">
                                            {{ $template['name'] }}
                                        </strong>
                                        <span>{{ __('Uploaded template') }}</span>
                                    </div>

                                    <form method="POST" action="{{ route('templates.destroy') }}">
                                        @csrf
                                        @method('DELETE')

                                        <button class="icon-button" type="submit"
                                            aria-label="{{ __('Delete template') }}"
                                            title="{{ __('Delete template') }}">
                                            <svg viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.7"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                <div class="workspace-file-details">                       
                                    <div class="workspace-file-meta">
                                        <div>
                                            <span>{{ __('File size') }}</span>
                                            <strong>
                                                {{ number_format($template['size'] / 1024 / 1024, 2) }} MB
                                            </strong>
                                        </div>

                                        <div>
                                            <span>{{ __('File type') }}</span>
                                            <strong>{{ $template['extension'] }}</strong>
                                        </div>

                                        <div>
                                            <span>{{ __('Upload date') }}</span>
                                            <strong>
                                                {{ isset($template['uploaded_at'])
                                                ? \Illuminate\Support\Carbon::parse($template['uploaded_at'])
                                                    ->timezone('Asia/Jakarta')
                                                    ->translatedFormat('d M Y, H:i')
                                                : '—' }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="workspace-file-actions">
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
                                            {{ __('Replace File') }}
                                        </label>
                                    </form>
                                </div>
                            </section>

                            {{-- Optional Save to Gallery --}}
                            <form method="POST" action="{{ route('templates.save') }}"
                                class="workspace-save-form">
                                @csrf

                                <button type="submit" class="workspace-save-button">
                                    <svg viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="M6 3h12l3 3v15H3V3h3Z"/>
                                        <path d="M7 3v6h10V3M7 21v-8h10v8"/>
                                    </svg>

                                    <span>
                                        <strong>{{ __('Save to Gallery') }}</strong>
                                        <small>{{ __('Optional — keep this template for later.') }}</small>
                                    </span>
                                </button>
                            </form>

                            {{-- Continue --}}
                            <div class="continue-area workspace-continue-area">
                                <a class="primary-button" href="{{ route('recipients.index') }}">
                                    {{ __('Continue') }}
                                    <svg viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="M5 12h14M13 6l6 6-6 6"/>
                                    </svg>
                                </a>
                            </div>

                        </aside>
                    </div>
                </section>
            @else
                <div class="template-home-layout">

                    <!-- SIDEBAR CARI TEMPLATE -->
                    <aside class="template-sidebar" id="templateSidebar">
                        <div class="sidebar-top">
                            <div class="sidebar-heading">
                                <span class="sidebar-eyebrow">{{ __('LIBRARY') }}</span>
                                <h2 class="sidebar-title">{{ __('Choose a Design') }}</h2>
                                
                                <p class="sidebar-subtitle">
                                    {{ __('Find a certificate template that suits your needs.') }}
                                </p>
                            </div>

                            <button type="button"
                                    class="sidebar-close"
                                    id="closeSidebar"
                                    aria-label="{{ __('Close sidebar') }}">
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
                                placeholder="{{ __('Search template names...') }}">
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
                                    aria-label="{{ __('Preview :name', ['name' => $item['name']]) }}"
                                    title="{{ __('View preview') }}">
                                    
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

                            @if (!empty($savedTemplates))
                                <div class="sidebar-section-divider">
                                    <span>{{ __('MY TEMPLATES') }}</span>
                                </div>

                                @foreach ($savedTemplates as $saved)
                                    <div class="template-card">
                                        <form
                                            method="POST"
                                            action="{{ route('templates.gallery.select', $saved['id']) }}"
                                            class="template-item"
                                            data-template-name="{{ strtolower($saved['name']) }}"
                                        >
                                            @csrf

                                            <button type="submit" class="template-option">
                                                @if (str_starts_with($saved['mime'], 'image/'))
                                                    <img
                                                        src="{{ route('templates.gallery.preview', $saved['id']) }}"
                                                        alt="{{ $saved['name'] }}"
                                                    >
                                                @else
                                                    <span class="template-pdf-thumbnail">PDF</span>
                                                @endif

                                                <span>{{ $saved['name'] }}</span>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            @endif

                            <p class="template-empty" id="templateEmpty" hidden>
                                {{ __('No templates found.') }}
                            </p>
                        </div>
                    </aside>

                    <!-- TOMBOL BUKA SIDEBAR -->
                    <button type="button"
                            class="sidebar-open is-visible"
                            id="openSidebar"
                            aria-label="{{ __('Open template search') }}">
                        &rsaquo;
                        <span class="sidebar-open-label">{{ __('Browse Templates') }}</span>
                    </button>

                    <!-- BAGIAN UTAMA -->
                    <div class="template-main">
                        <section class="page-intro page-intro--hero">
                            <h1>{{ __('Create') }} <span class="title-accent">{{ __('Certificate') }}</span></h1>
                            <p>{{ __('Upload a certificate design to get started.') }}</p>
                        </section>

                        @error('template')
                            <p class="form-error" role="alert">{{ $message }}</p>
                        @enderror

                        <form class="upload-card"
                            method="POST"
                            action="{{ route('templates.store') }}"
                            enctype="multipart/form-data"
                            data-upload-form>

                            @csrf
                            <div class="dropzone" data-dropzone>

                                {{-- Tampilan awal --}}
                                <div class="upload-content" data-upload-content>
                                    <span class="upload-symbol" aria-hidden="true">
                                        <svg viewBox="0 0 48 48" fill="none"
                                            stroke="currentColor" stroke-width="4.5"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3.5" y="4.5" width="41" height="39" rx="3"/>
                                            <path d="m9 35 10-11 7 8 5-6 8 9H9Z"
                                                fill="currentColor" stroke="none"/>
                                        </svg>
                                    </span>

                                    <h2>{{ __('Drag & Drop File Here') }}</h2>
                                    <p class="or-copy">{{ __('or') }}</p>

                                    <input class="visually-hidden"
                                        id="template-file"
                                        name="template"
                                        type="file"
                                        accept="image/png,image/jpeg,application/pdf"
                                        aria-hidden="true"
                                        tabindex="-1"
                                        data-template-input>

                                    <button class="file-picker"
                                        type="button"
                                        data-file-picker>
                                        {{ __('Choose File') }}
                                    </button>

                                    <p class="format-copy">
                                        {{ __('Formats: PNG, JPG, or PDF') }}<br>
                                        {{ __('Maximum size: 10 MB') }}
                                    </p>
                                </div>

                                {{-- Tampilan loading --}}
                                <div class="upload-loading" data-upload-loading hidden>
                                    <span class="upload-spinner" aria-hidden="true"></span>

                                    <h2>{{ __('Uploading certificate...') }}</h2>

                                    <p data-upload-loading-name>
                                        {{ __('Please wait while your file is being uploaded.') }}
                                    </p>

                                    <div class="upload-progress-track">
                                        <span></span>
                                    </div>

                                    <span class="upload-loading-caption">
                                        {{ __('Do not close this page.') }}
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
                    aria-label="{{ __('Close preview') }}">
                    &times;
                </button>

                <div class="template-preview-heading"
                    id="templatePreviewTitle">
                    {{ __('Template Preview') }}
                </div>

                <div class="template-preview-image-wrap">
                    <img src=""
                        alt="{{ __('Certificate template preview') }}"
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
                    // Mulai tutup sidebar
                    sidebar.classList.remove('is-open');

                    // Mulai munculkan tombol secara bersamaan
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
                    previewImage.alt = window.procertTranslations.previewPrefix + ' ' + templateName;
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
