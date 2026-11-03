
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

    <style>
    /* Layout homepage */
    .template-home-layout {
        display: flex;
        align-items: flex-start;
        gap: 0;
        width: 100%;
        position: relative;
        min-height: 0;
    }

    .template-sidebar {
        width: 242px;
        min-width: 242px;
        height: 620px;
        background: #D9D9D9;
        padding: 16px 12px;
        display: none;
        flex-direction: column;
        gap: 16px;
        align-self: flex-start;
        border-radius: 0;
        flex-shrink: 0;
    }

    .template-sidebar.is-open {
        display: flex;
    }

    .sidebar-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar-title {
        font-size: 15px;
        font-weight: 700;
        color: #222;
    }

    .sidebar-close {
        border: none;
        background: transparent;
        font-size: 23px;
        cursor: pointer;
        color: #333;
        line-height: 1;
        padding: 2px 5px;
    }

    .sidebar-close:hover {
        color: #2454FF;
    }

    .template-search {
        width: 100%;
        height: 36px;
        padding: 8px 14px;
        border: 1px solid #D5D5D5;
        border-radius: 25px;
        background: #fff;
        font-family: inherit;
        font-size: 12px;
        outline: none;
        box-sizing: border-box;
    }

    .template-search:focus {
        border-color: #2454FF;
    }

    .template-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        overflow-y: auto;
        max-height: 520px;
    }

    .template-option {
        display: block;
        width: 100%;
        padding: 0;
        border: 2px solid transparent;
        border-radius: 5px;
        overflow: hidden;
        background: #fff;
        cursor: pointer;
        text-align: left;
        transition: border-color .2s ease;
    }

    .template-option:hover {
        border-color: #2454FF;
    }

    .template-option img {
        display: block;
        width: 100%;
        aspect-ratio: 1.414 / 1;
        object-fit: cover;
    }

    .template-option span {
        display: block;
        padding: 7px 9px;
        font-size: 11px;
        font-weight: 700;
        color: #333;
    }

    .template-empty {
        font-size: 12px;
        color: #777;
        text-align: center;
        padding: 15px 5px;
    }

    .template-main {
        flex: 1;
        min-width: 0;
        position: relative;
        padding: 0 12px;
    }

    .template-main-top {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 4px;
        min-height: 36px;
    }

    .sidebar-open {
        display: none;
        position: absolute;
        left: -1px;
        top: 180px;
        width: 32px;
        height: 70px;
        align-items: center;
        justify-content: center;
        border: 1px solid #D5D5D5;
        border-left: none;
        border-radius: 0 35px 35px 0;
        background: #fff;
        color: #222;
        font-size: 22px;
        cursor: pointer;
        z-index: 2;
    }

    .sidebar-open.is-visible {
        display: flex;
    }

    .excel-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 7px 13px;
        border: none;
        border-radius: 8px;
        background: #104F16;
        color: #fff;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .excel-button:hover:not(:disabled) {
        background: #0B3F11;
    }

    .excel-button:disabled {
        background: #BFC5D3;
        color: #fff;
        cursor: not-allowed;
    }

    .template-main .page-intro--hero {
        margin-top: 0;
        margin-bottom: 22px;
    }

    .template-main .page-intro--hero h1 {
        font-size: 38px;
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .template-main .page-intro--hero p {
        font-size: 12px;
        margin: 0;
    }

    .template-main .upload-card {
        width: 100%;
        max-width: 560px;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .template-main .dropzone {
        min-height: 285px;
        box-sizing: border-box;
        padding: 26px 20px;
    }

    .template-main .upload-symbol {
        width: 78px !important;
        height: 78px !important;
        min-width: 78px;
        min-height: 78px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
        background: #D9D9D9;
    }

    .template-main .upload-symbol svg {
        width: 42px !important;
        height: 42px !important;
        display: block;
        flex-shrink: 0;
    }

    .template-main .dropzone h2 {
        font-size: 15px;
    }

    .template-main .file-picker {
        min-width: 145px;
    }

    @media (max-width: 760px) {
        .template-sidebar {
            width: 210px;
            min-width: 210px;
        }

        .template-main {
            padding: 0 8px;
        }

        .template-main .page-intro--hero h1 {
            font-size: 30px;
        }
    }

    @media (max-width: 520px) {
        .template-sidebar {
            width: 180px;
            min-width: 180px;
        }

        .template-main .page-intro--hero h1 {
            font-size: 24px;
        }

        .excel-button {
            padding: 7px 9px;
            font-size: 10px;
        }
    }

        /* Penyesuaian tinggi homepage */
    .template-main .upload-card {
        max-width: 560px;
        margin: 0 auto;
    }

    .template-main .dropzone {
        min-height: 285px;
        height: 285px;
        padding: 20px;
        justify-content: center;
    }

    .template-main-top {
        margin-bottom: 0;
    }

    .template-main .page-intro--hero {
        margin-bottom: 16px;
    }

    .sidebar-open {
        top: 180px;
    }

    </style>
</head>

<body>
<div class="app-shell">

    @include('partials.wizard-header', ['currentStep' => 1])

    <main class="page-content {{ $template ? 'page-content--success' : 'page-content--upload' }}">

        @if ($template)

            <div class="template-main-top">
                <a class="excel-button"
                   href="{{ route('recipients.index', ['mode' => 'import']) }}">
                    <span>▤</span>
                    Template Excel
                </a>
            </div>

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
                    'v' => ($template['id'] ?? 'upload') . '-' . $template['size'] . '-' . md5($template['name'])
                ]) }}"
                alt="Pratinjau {{ $template['name'] }}">
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
                    'v' => ($template['id'] ?? 'upload') . '-' . $template['size'] . '-' . md5($template['name'])
                ]) }}"
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
                        <span class="sidebar-title">Cari Template</span>

                        <button type="button"
                                class="sidebar-close"
                                id="closeSidebar"
                                aria-label="Tutup sidebar">
                            &times;
                        </button>
                    </div>

                    <input type="search"
                           class="template-search"
                           id="templateSearch"
                           placeholder="Cari template sertifikat...">

                    <div class="template-list" id="templateList">

                        @foreach ($builtinTemplates as $id => $item)
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
                        @endforeach

                        <p class="template-empty" id="templateEmpty" hidden>
                            Template tidak ditemukan.
                        </p>

                    </div>
                </aside>

                <!-- BAGIAN UTAMA -->
                <div class="template-main">

                    <button type="button"
                            class="sidebar-open is-visible"
                            id="openSidebar"
                            aria-label="Buka pencarian template">
                        &rsaquo;
                    </button>

                    <div class="template-main-top">
                        <button type="button"
                                class="excel-button"
                                disabled
                                title="Pilih template sertifikat terlebih dahulu">
                            <span>▤</span>
                            Template Excel
                        </button>
                    </div>

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
                                    <path d="m9 35 10-11 7 8 5-6 8 9H9Z"
                                          fill="currentColor" stroke="none"/>
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
        });

        closeButton.addEventListener('click', function () {
            sidebar.classList.remove('is-open');
            openButton.classList.add('is-visible');
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
});
</script>

</body>
</html>