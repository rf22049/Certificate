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
<body>
    <div class="app-shell">
        <header class="topbar">
            <a class="brand" href="{{ route('home') }}" aria-label="ProCert, beranda">
                <span>Pro</span><span class="brand-mark">C</span><span>ert</span>
            </a>
        </header>

        <nav class="steps" aria-label="Tahapan pembuatan sertifikat">
            <ol class="steps-list">
                <li class="step is-active" aria-current="step">
                    <span class="step-number">1</span><span class="step-label">Upload Template</span>
                </li>
                <li class="step"><span class="step-number">2</span><span class="step-label">Upload Data</span></li>
                <li class="step"><span class="step-number">3</span><span class="step-label">Nomor Sertifikat</span></li>
                <li class="step"><span class="step-number">4</span><span class="step-label">Edit Sertifikat</span></li>
                <li class="step"><span class="step-number">5</span><span class="step-label">Review</span></li>
            </ol>
        </nav>

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
                            <img src="{{ route('templates.preview') }}" alt="Pratinjau {{ $template['name'] }}">
                        @else
                            <iframe src="{{ route('templates.preview') }}" title="Pratinjau {{ $template['name'] }}"></iframe>
                        @endif
                    </div>

                    <aside class="file-column" aria-label="Template yang diunggah">
                        <section class="file-panel">
                            <div class="file-panel-header">
                                <span class="file-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M13.5 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5L13.5 3Z" />
                                        <path d="M13 3v7h7M8 14h8M8 17.5h8" />
                                    </svg>
                                </span>
                                <span class="file-panel-copy">
                                    <strong title="{{ $template['name'] }}">{{ $template['name'] }}</strong>
                                    <span>{{ number_format($template['size'] / 1024 / 1024, 1) }} MB &nbsp; {{ $template['extension'] }}</span>
                                </span>
                                <form method="POST" action="{{ route('templates.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-button" type="submit" aria-label="Hapus template" title="Hapus template">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3" />
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <div class="file-panel-body">
                                @if (str_starts_with($template['mime'], 'image/'))
                                    <div class="file-thumbnail">
                                        <img src="{{ route('templates.preview') }}" alt="">
                                    </div>
                                @else
                                    <div class="file-thumbnail file-thumbnail--pdf" aria-label="Dokumen PDF">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M13.5 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5L13.5 3Z" />
                                            <path d="M13 3v7h7M8 15h8M8 18h5" />
                                        </svg>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('templates.store') }}" enctype="multipart/form-data" data-replace-form>
                                    @csrf
                                    <input class="visually-hidden" id="replace-template" name="template" type="file" accept="image/png,image/jpeg,application/pdf" data-replace-input>
                                    <label class="replace-picker" for="replace-template">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M7 7h13l-3-3M17 17H4l3 3M20 7l-4 3M4 17l4-3" />
                                        </svg>
                                        Ganti File
                                    </label>
                                </form>
                            </div>
                        </section>

                        <div class="continue-area">
                            <button class="primary-button" type="button" data-next-step>
                                Lanjut
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </button>
                            <p class="toast" role="status" data-next-step-notice hidden>Langkah berikutnya belum tersedia.</p>
                        </div>
                    </aside>
                </div>
            @else
                <section class="page-intro page-intro--hero">
                    <h1>Buat <span class="title-accent">Sertifikat</span></h1>
                    <p>Unggah desain sertifikat untuk memulai.</p>
                </section>

                @error('template')
                    <p class="form-error" role="alert">{{ $message }}</p>
                @enderror

                <form class="upload-card" method="POST" action="{{ route('templates.store') }}" enctype="multipart/form-data" data-upload-form>
                    @csrf
                    <div class="dropzone" data-dropzone>
                        <span class="upload-symbol" aria-hidden="true">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3.5" y="4.5" width="41" height="39" rx="3" />
                                <path d="m9 35 10-11 7 8 5-6 8 9H9Z" fill="currentColor" stroke="none" />
                            </svg>
                        </span>
                        <h2>Drag &amp; Drop File Here</h2>
                        <p class="or-copy">atau</p>
                        <input class="visually-hidden" id="template-file" name="template" type="file" accept="image/png,image/jpeg,application/pdf" data-template-input>
                        <button class="file-picker" type="button" data-file-picker>Pilih File</button>
                        <p class="format-copy">Format: PNG, JPG, atau PDF<br>Ukuran maks. 10 MB</p>
                        <div class="selected-file" data-selected-file hidden>
                            <span class="selected-file-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M13.5 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5L13.5 3Z" />
                                    <path d="M13 3v7h7M8 14h8M8 17.5h8" />
                                </svg>
                            </span>
                            <span class="selected-file-copy">
                                <strong data-file-name></strong>
                                <span data-file-size></span>
                            </span>
                        </div>
                        <button class="upload-submit" type="submit" data-upload-submit hidden disabled>Unggah Template</button>
                    </div>
                </form>
            @endif
        </main>
    </div>
</body>
</html>