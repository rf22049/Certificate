
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

    <style>
        .number-format-header { display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; }
        .number-mode-switch { display:flex; align-items:center; gap:10px; font-weight:600; color:#444; }
        .number-mode-switch .switch { position:relative; display:inline-block; width:48px; height:26px; flex:0 0 48px; }
        .number-mode-switch .switch input { opacity:0; width:0; height:0; position:absolute; }
        .number-mode-switch .slider { position:absolute; inset:0; cursor:pointer; background:#c8cbd2; border-radius:999px; transition:.2s; }
        .number-mode-switch .slider:before { content:''; position:absolute; width:20px; height:20px; left:3px; top:3px; background:white; border-radius:50%; box-shadow:0 1px 3px #0002; transition:.2s; }
        .number-mode-switch input:checked + .slider { background:#4f46e5; }
        .number-mode-switch input:checked + .slider:before { transform:translateX(22px); }
        .number-mode-switch input:focus-visible + .slider { outline:3px solid #a5b4fc; outline-offset:2px; }
        .number-mode-label { font-size:13px; color:#6b7280; }
        .number-mode-label.active { color:#4338ca; font-weight:700; }
        .number-field.mode-hidden { display:none !important; }
        .number-field select { appearance:none; -webkit-appearance:none; -moz-appearance:none; }
        .number-field .select-wrapper { position:relative; width:100%; }
        .number-field .select-wrapper::after {
            content:""; position:absolute; right:20px; top:50%; width:8px; height:8px;
            border-right:2px solid #555; border-bottom:2px solid #555;
            transform:translateY(-70%) rotate(45deg); pointer-events:none;
        }
        .number-field .select-wrapper select { width:100%; padding-right:42px; background-image:none !important; }
        .number-field .select-wrapper select:focus { outline-offset:2px; }
    </style>
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
                    <div class="number-format-header">
                        <h2 class="number-card-title">Format Nomor Sertifikat</h2>
                        <div class="number-mode-switch" aria-label="Jenis format nomor sertifikat">
                            <span id="campusModeLabel" class="number-mode-label active">Kampus</span>
                            <label class="switch" title="Ganti format Kampus / Umum">
                                <input type="checkbox" id="generalModeSwitch" name="number_mode" value="general" aria-label="Gunakan format umum" {{ ($numberSettings['number_mode'] ?? 'campus') === 'general' ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                            <span id="generalModeLabel" class="number-mode-label">Umum</span>
                        </div>
                    </div>

                    <p class="number-card-description" id="numberModeDescription">
                        Format Kampus menggunakan kode fakultas dan program studi. Kode dapat diedit manual.
                    </p>
                    <input type="hidden" id="numberModeValue" name="number_mode_value" value="{{ $numberSettings['number_mode'] ?? 'campus' }}">


                    <div class="number-form-grid">
                        <div class="number-field campus-only-field">
                            <label for="faculty">Kode Fakultas</label>
                            <input id="faculty" name="faculty_code" value="{{ old('faculty_code', $numberSettings['faculty_code'] ?? 'UP-DKN2.2') }}" maxlength="30">
                            <small>Kode fakultas bisa diedit manual.</small>
                        </div>

                        <div class="number-field">
                            <label for="document">Jenis Dokumen</label>
                            <input id="document" name="document_type" value="{{ old('document_type', $numberSettings['document_type'] ?? 'SERTIFIKAT') }}" maxlength="40" readonly aria-readonly="true">
                            <small>Jenis dokumen.</small>
                        </div>

                        <div class="number-field campus-only-field">
                            <label for="studyProgram">Kode Program Studi</label>
                            <input id="studyProgram" name="study_program_code" value="{{ old('study_program_code', $numberSettings['study_program_code'] ?? 'PP05') }}" maxlength="30">
                            <small>Kode program studi bisa diedit manual.</small>
                        </div>

                        <div class="number-field">
                            <label for="startNumber">Nomor Awal</label>
                            <input id="startNumber" name="start_number" type="number" min="1" step="1" value="{{ old('start_number', $numberSettings['start_number'] ?? $nextNumber) }}" inputmode="numeric" readonly>
                            <small>Contoh: 1 menjadi 001, 52 menjadi 052.</small>
                        </div>

                        <div class="number-field">
                            <label for="month">Bulan Sertifikat</label>
                            <div class="select-wrapper">
                                <select id="month" name="month" required>
                                    @foreach ([1=>'Januari (I)',2=>'Februari (II)',3=>'Maret (III)',4=>'April (IV)',5=>'Mei (V)',6=>'Juni (VI)',7=>'Juli (VII)',8=>'Agustus (VIII)',9=>'September (IX)',10=>'Oktober (X)',11=>'November (XI)',12=>'Desember (XII)'] as $monthValue => $monthLabel)
                                        <option value="{{ $monthValue }}" {{ (int) old('month', $selectedMonth) === $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <small>Bulan akan dikonversi menjadi angka Romawi.</small>
                        </div>


                        <div class="number-field">
                            <label for="year">Tahun Sertifikat</label>
                            <div class="select-wrapper">
                                <select id="year" name="year" required>
                                    @for ($yearOption = 2000; $yearOption <= 2100; $yearOption++)
                                        <option value="{{ $yearOption }}" {{ (int) old('year', $selectedYear) === $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                                    @endfor
                                </select>
                            </div>
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
        const facultyInput = document.getElementById('faculty');
        const documentInput = document.getElementById('document');
        const studyProgramInput = document.getElementById('studyProgram');
        const generalModeSwitch = document.getElementById('generalModeSwitch');
        const numberModeValue = document.getElementById('numberModeValue');
        const campusModeLabel = document.getElementById('campusModeLabel');
        const generalModeLabel = document.getElementById('generalModeLabel');
        const numberModeDescription = document.getElementById('numberModeDescription');

        function formatNumber(number) {
            return String(number).padStart(3, '0');
        }

        function buildCertificateNumber(number) {
            const parts = [formatNumber(number)];

        if (!generalModeSwitch.checked) {
            parts.push(facultyInput.value.trim());
        }

            parts.push(documentInput.value.trim());

        if (!generalModeSwitch.checked) {
            parts.push(studyProgramInput.value.trim());
        }

        parts.push(romanMonths[Number(monthInput.value)], yearInput.value);

        return parts.join('/');
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

        [startNumberInput, monthInput, documentInput].forEach((input) => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });

     function updateNumberMode() {
        const isGeneral = generalModeSwitch.checked;
        numberModeValue.value = isGeneral ? 'general' : 'campus';
        document.querySelectorAll('.campus-only-field').forEach(field => {
            field.classList.toggle('mode-hidden', isGeneral);
        });
        campusModeLabel.classList.toggle('active', !isGeneral);
        generalModeLabel.classList.toggle('active', isGeneral);
        numberModeDescription.textContent = isGeneral
            ? 'Format Umum: jenis dokumen dan nomor awal dapat diedit bebas.'
            : 'Format Kampus menggunakan kode fakultas dan program studi. Kode dapat diedit manual.';
        documentInput.readOnly = !isGeneral;
        documentInput.setAttribute('aria-readonly', String(!isGeneral));
        documentInput.style.background = isGeneral ? '' : '#f3f4f6';
        documentInput.style.cursor = isGeneral ? '' : 'not-allowed';
        startNumberInput.readOnly = !isGeneral;
        startNumberInput.style.background = isGeneral ? '' : '#f3f4f6';
        startNumberInput.style.cursor = isGeneral ? '' : 'not-allowed';
        updatePreview();
    }

        generalModeSwitch.addEventListener('change', updateNumberMode);
        updateNumberMode();

        yearInput.addEventListener('change', () => {
            if (!generalModeSwitch.checked) {
                const lastNumber = Number(lastNumbersByYear[yearInput.value] ?? 0);
                startNumberInput.value = String(lastNumber + 1);
            }
            updatePreview();
        });

        updatePreview();
    </script>
</body>
</html>
