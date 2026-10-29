<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Penerima | ProCert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        @include('partials.wizard-header', ['currentStep' => 2])

        <main class="recipient-page">
            <div class="recipient-heading">
                <div>
                    <h1>Masukkan Data Penerima</h1>
                    <p>Tambahkan daftar nama penerima sertifikat</p>
                </div>

                <nav class="recipient-mode" aria-label="Metode input data penerima">
                    <a class="recipient-mode-button {{ $mode === 'import' ? 'is-selected' : '' }}" href="{{ route('recipients.index', ['mode' => 'import']) }}" @if ($mode === 'import') aria-current="page" @endif>
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3.5" width="14" height="13" rx="1" />
                            <path d="M3 8h14M8 3.5v13" />
                        </svg>
                        Upload Excel / CSV
                    </a>
                    <a class="recipient-mode-button {{ $mode === 'manual' ? 'is-selected' : '' }}" href="{{ route('recipients.index', ['mode' => 'manual']) }}" @if ($mode === 'manual') aria-current="page" @endif>
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11.5 4H4.25A1.25 1.25 0 0 0 3 5.25v10.5A1.25 1.25 0 0 0 4.25 17h10.5A1.25 1.25 0 0 0 16 15.75V9" />
                            <path d="m9 11 1-.2 6.3-6.3a1.4 1.4 0 0 0-2-2L8 9l-1 3 2-.4Z" />
                        </svg>
                        Input Manual
                    </a>
                </nav>
            </div>

            @if (session('status'))
                <p class="recipient-status" role="status">{{ session('status') }}</p>
            @endif

            <section class="recipient-workspace recipient-workspace--manual" data-recipient-panel="manual" @if ($mode !== 'manual') hidden @endif>
                <form class="recipient-manual-form" method="POST" action="{{ route('recipients.store') }}" data-manual-form>
                    @csrf
                    @php($manualRecipients = old('recipients', $recipients))
                    <div class="recipient-table-wrap">
                        @error('recipients')
                            <p class="form-error recipient-error" role="alert">{{ $message }}</p>
                        @enderror
                        <table class="recipient-table recipient-table--manual">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Penerima</th>
                                    <th scope="col">Ket</th>
                                </tr>
                            </thead>
                            <tbody data-manual-rows>
                                @forelse ($manualRecipients as $index => $recipient)
                                    <tr data-recipient-row>
                                        <td class="recipient-number">
                                            <input type="checkbox" aria-label="Pilih baris {{ $index + 1 }}" data-row-select>
                                            <span data-row-number>{{ $index + 1 }}</span>
                                        </td>
                                        <td><input name="recipients[{{ $index }}][name]" value="{{ $recipient['name'] ?? '' }}" maxlength="255" aria-label="Nama penerima baris {{ $index + 1 }}" data-recipient-name></td>
                                        <td><input name="recipients[{{ $index }}][note]" value="{{ $recipient['note'] ?? '' }}" maxlength="255" aria-label="Keterangan baris {{ $index + 1 }}" data-recipient-note></td>
                                    </tr>
                                @empty
                                    <tr data-recipient-row>
                                        <td class="recipient-number">
                                            <input type="checkbox" aria-label="Pilih baris 1" data-row-select>
                                            <span data-row-number>1</span>
                                        </td>
                                        <td><input name="recipients[0][name]" maxlength="255" aria-label="Nama penerima baris 1" data-recipient-name></td>
                                        <td><input name="recipients[0][note]" maxlength="255" aria-label="Keterangan baris 1" data-recipient-note></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <aside class="recipient-actions">
                        <div class="row-actions">
                            <button class="outline-action" type="button" data-add-recipient>
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                    <path d="M10 3v14M3 10h14" />
                                </svg>
                                Tambah Baris
                            </button>
                            <button class="quiet-action" type="button" data-delete-recipient>
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 5h14M7 5V3h6v2m-8 0 1 12h8l1-12M8 8v6m4-6v6" />
                                </svg>
                                Hapus Baris
                            </button>
                        </div>
                        <button class="primary-button" type="submit">
                            Lanjut
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </button>
                        <a class="secondary-button" href="{{ route('home') }}">Kembali</a>
                    </aside>
                </form>
            </section>

            <section class="recipient-workspace recipient-workspace--import" data-recipient-panel="import" @if ($mode !== 'import') hidden @endif>
                <form class="recipient-import-form" method="POST" action="{{ route('recipients.import') }}" enctype="multipart/form-data" data-recipient-import-form>
                    @csrf
                    <div class="recipient-dropzone" data-recipient-dropzone>
                        <span class="spreadsheet-symbol" aria-hidden="true">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M27 5H12a3 3 0 0 0-3 3v32a3 3 0 0 0 3 3h24a3 3 0 0 0 3-3V17L27 5Z" />
                                <path d="M27 5v12h12M16 25h16M16 32h16M16 39h16" />
                            </svg>
                        </span>
                        <h2>Drag &amp; Drop File Here</h2>
                        <p>atau</p>
                        <input class="visually-hidden" id="recipients-file" name="recipients_file" type="file" accept=".xlsx,.xls,.csv" data-recipient-file>
                        <button class="file-picker" type="button" data-recipient-picker>Pilih File</button>
                        <p class="format-copy">Format: XLSX, XLS, atau CSV<br>Ukuran maks. 10 MB</p>
                        <span class="selected-file-copy recipient-uploading" data-recipient-uploading hidden></span>
                    </div>
                </form>

                <aside class="recipient-import-preview">
                    @error('recipients_file')
                        <p class="form-error recipient-error" role="alert">{{ $message }}</p>
                    @enderror
                    <h2>Pratinjau data <span>({{ count($recipients) }})</span></h2>
                    <div class="recipient-preview-scroll" data-preview-scroll>
                        <table class="recipient-table recipient-table--preview">
                            <thead>
                                <tr><th scope="col">No</th><th scope="col">Nama Penerima</th><th scope="col">Ket</th></tr>
                            </thead>
                            <tbody id="recipient-preview-rows">
                                @forelse ($recipients as $index => $recipient)
                                    <tr data-preview-row @if ($index >= 10) hidden @endif>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $recipient['name'] }}</td>
                                        <td>{{ $recipient['note'] }}</td>
                                    </tr>
                                @empty
                                    <tr class="preview-empty"><td colspan="3">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button class="preview-toggle" type="button" aria-expanded="false" aria-controls="recipient-preview-rows" data-preview-toggle @if (count($recipients) <= 10) hidden @endif>
                        <span data-preview-label>Tampilkan Semua</span>
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m4 6 4 4 4-4" />
                        </svg>
                    </button>
                    <form class="import-actions" method="POST" action="{{ route('recipients.next') }}">
                        @csrf
                        <button class="primary-button" type="submit">
                            Lanjut
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </button>
                        <a class="secondary-button" href="{{ route('home') }}">Kembali</a>
                    </form>
                </aside>
            </section>
        </main>
    </div>
</body>
</html>