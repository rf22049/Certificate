
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pengaturan Nomor Sertifikat | ProCert</title>
    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app-shell">

        {{-- Header dan progress bar bersama dengan Step 1 dan Step 2. --}}
        @include('partials.wizard-header', ['currentStep' => 3])

        <main class="number-page">

            <header class="number-heading">
                <h1>Pengaturan Nomor Sertifikat</h1>
                <p>
                    Atur penomoran sertifikat sesuai ketentuan program studi.
                    Preview akan diperbarui secara otomatis.
                </p>
            </header>

            <form
                id="certificateNumberForm"
                method="POST"
                action="{{ route('certificate-number.store') }}"
            > @csrf

                <section class="number-card">
                    <h2 class="number-card-title">Format Nomor Sertifikat</h2>

                    <p class="number-card-description">
                        Kode tetap dari program studi sudah tersedia dan tidak perlu diisi ulang.
                    </p>

                    <div class="number-form-grid">

                        <div class="number-field">
                            <label for="faculty">Kode Fakultas</label>
                            <input id="faculty" value="UP-DKN2.2" disabled>
                            <small>Kode tetap program studi.</small>
                        </div>

                        <div class="number-field">
                            <label for="document">Jenis Dokumen</label>
                            <input id="document" value="SERTIFIKAT" disabled>
                            <small>Jenis dokumen tetap.</small>
                        </div>

                        <div class="number-field">
                            <label for="studyProgram">Kode Program Studi</label>
                            <input id="studyProgram" value="PP05" disabled>
                            <small>Kode tetap program studi.</small>
                        </div>

                        <div class="number-field">
                            <label for="startNumber">Nomor Awal</label>
                            <input
                                id="startNumber"
                                type="number"
                                min="1"
                                step="1"
                                value="{{ $nextNumber }}"
                                inputmode="numeric"
                                readonly
                            >
                            <small>Contoh: 1 menjadi 001, 52 menjadi 052.</small>
                        </div>

                        <div class="number-field">
                            <label for="month">Bulan Sertifikat</label>
                            <select id="month" name="month" required>
                                <option value="1" {{ $selectedMonth == 1 ? 'selected' : '' }}>
                                    Januari (I)
                                </option>
                                <option value="2" {{ $selectedMonth == 2 ? 'selected' : '' }}>
                                    Februari (II)
                                </option>
                                <option value="3" {{ $selectedMonth == 3 ? 'selected' : '' }}>
                                    Maret (III)
                                </option>
                                <option value="4" {{ $selectedMonth == 4 ? 'selected' : '' }}>
                                    April (IV)
                                </option>
                                <option value="5" {{ $selectedMonth == 5 ? 'selected' : '' }}>
                                    Mei (V)
                                </option>
                                <option value="6" {{ $selectedMonth == 6 ? 'selected' : '' }}>
                                    Juni (VI)
                                </option>
                                <option value="7" {{ $selectedMonth == 7 ? 'selected' : '' }}>
                                    Juli (VII)
                                </option>
                                <option value="8" {{ $selectedMonth == 8 ? 'selected' : '' }}>
                                    Agustus (VIII)
                                </option>
                                <option value="9" {{ $selectedMonth == 9 ? 'selected' : '' }}>
                                    September (IX)
                                </option>
                                <option value="10" {{ $selectedMonth == 10 ? 'selected' : '' }}>
                                    Oktober (X)
                                </option>
                                <option value="11" {{ $selectedMonth == 11 ? 'selected' : '' }}>
                                    November (XI)
                                </option>
                                <option value="12" {{ $selectedMonth == 12 ? 'selected' : '' }}>
                                    Desember (XII)
                                </option>
                            </select>
                            <small>Bulan akan dikonversi menjadi angka Romawi.</small>
                        </div>

                        <div class="number-field">
                            <label for="year">Tahun Sertifikat</label>
                            <input
                                id="year"
                                type="number"
                                name="year"
                                min="2000"
                                max="2100"
                                value="{{ $selectedYear }}"
                                required
                            >
                            <small>Nomor urut dimulai kembali setiap tahun.</small>
                        </div>
                    </div>
                </section>

                <section class="number-preview" aria-label="Preview nomor sertifikat">
                    <p class="number-preview-label">Preview Nomor Sertifikat</p>
                    <p class="number-preview-code" id="previewCode"></p>
                    <p class="number-preview-next" id="previewNext"></p>
                </section>

                <section class="number-reminder">
                    <div class="number-reminder-icon" aria-hidden="true">↻</div>

                    <div>
                        <h2>Pengingat Nomor Sertifikat</h2>
                        <p id="reminderText">
                            @if ($lastNumber === 0)
                                Belum ada sertifikat yang diterbitkan pada tahun {{ $selectedYear }}.
                                Nomor berikutnya yang disarankan adalah
                                <strong>{{ str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT) }}</strong>.
                            @else
                                Nomor terakhir yang digunakan pada tahun {{ $selectedYear }} adalah
                                <strong>{{ str_pad((string) $lastNumber, 3, '0', STR_PAD_LEFT) }}</strong>.
                                Nomor berikutnya yang disarankan adalah
                                <strong>{{ str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT) }}</strong>.
                            @endif
                        </p> 
                    </div>
                </section>

                <div
                    id="numberNotice"
                    class="number-notice {{ session('status') ? 'is-visible' : '' }}"
                    role="status"
                    aria-live="polite"
                >
                    {{ session('status') }}
                </div>

                <footer class="number-actions">
                    <a class="number-button" href="{{ route('recipients.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Kembali
                    </a>

                    <button
                        class="number-button number-button-primary"
                        id="continueButton"
                        type="submit"
                    >
                        Lanjut
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </button>
                </footer>
            </form>
        </main>
    </div>

    <script>
        const romanMonths = {
            1: 'I',
            2: 'II',
            3: 'III',
            4: 'IV',
            5: 'V',
            6: 'VI',
            7: 'VII',
            8: 'VIII',
            9: 'IX',
            10: 'X',
            11: 'XI',
            12: 'XII'
        };

        const startNumberInput = document.getElementById('startNumber');
        const monthInput = document.getElementById('month');
        const yearInput = document.getElementById('year');
        const lastNumbersByYear = @json($lastNumbersByYear);

        const previewCode = document.getElementById('previewCode');
        const previewNext = document.getElementById('previewNext');
        const notice = document.getElementById('numberNotice');

        function formatNumber(number) {
            return String(number).padStart(3, '0');
        }

        function buildCertificateNumber(number) {
            return [
                formatNumber(number),
                document.getElementById('faculty').value,
                document.getElementById('document').value,
                document.getElementById('studyProgram').value,
                romanMonths[Number(monthInput.value)],
                yearInput.value
            ].join('/');
        }

        function isValidInput() {
            const number = Number(startNumberInput.value);
            const year = Number(yearInput.value);

            return (
                startNumberInput.value.trim() !== '' &&
                Number.isSafeInteger(number) &&
                number >= 1 &&
                Number.isSafeInteger(year) &&
                year >= 2000 &&
                year <= 2100
            );
        }

        function updatePreview() {
            if (!isValidInput()) {
                previewCode.textContent = 'Masukkan nomor awal dan tahun yang valid';
                previewNext.textContent = '';
                return;
            }

            const number = Number(startNumberInput.value);

            previewCode.textContent = buildCertificateNumber(number);
            previewNext.textContent = 'Nomor berikutnya: ' +
                buildCertificateNumber(number + 1);
        }

        function showNotice(message, isError = false) {
            notice.textContent = message;
            notice.className = 'number-notice is-visible' +
                (isError ? ' is-error' : '');
        }

        [startNumberInput, monthInput, yearInput].forEach((input) => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });

        yearInput.addEventListener('input', () => {
            const lastNumber = Number(lastNumbersByYear[yearInput.value] ?? 0);
            startNumberInput.value = String(lastNumber + 1);
            updatePreview();
        });

        updatePreview();
    </script>
</body>
</html>
