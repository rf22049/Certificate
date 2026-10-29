
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProCert — Penomoran Sertifikat</title>

    <style>
        :root {
            --blue: #2864e8;
            --blue-dark: #1748b5;
            --blue-light: #edf4ff;
            --text: #172b4d;
            --muted: #77849a;
            --border: #e2e8f0;
            --background: #f6f8fc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        button, input, select {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        .topbar {
            height: 76px;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 6%;
        }

        .brand {
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .brand span {
            color: var(--blue);
        }

        .admin {
            color: #52627a;
            font-size: 13px;
        }

        .container {
            max-width: 1120px;
            margin: 38px auto;
            padding: 0 24px 40px;
        }

        .steps {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
            color: var(--muted);
            font-size: 13px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step-number {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #e8edf5;
            font-weight: 700;
        }

        .step.active {
            color: var(--blue);
            font-weight: 700;
        }

        .step.active .step-number,
        .step.done .step-number {
            background: var(--blue);
            color: white;
        }

        .step-line {
            width: 38px;
            height: 1px;
            background: #d7deea;
        }

        .heading {
            margin-bottom: 28px;
        }

        .heading h1 {
            margin: 0 0 10px;
            font-size: clamp(25px, 4vw, 32px);
            letter-spacing: -0.8px;
        }

        .heading p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
            font-size: 14px;
        }

        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 22px;
            box-shadow: 0 4px 18px rgba(31, 57, 94, 0.035);
        }

        .card-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .card-description {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .field input,
        .field select {
            width: 100%;
            height: 46px;
            border: 1px solid #dce3ed;
            border-radius: 9px;
            padding: 0 13px;
            background: white;
            color: var(--text);
            outline: none;
        }

        .field input:focus,
        .field select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(40, 100, 232, 0.1);
        }

        .field input:disabled {
            color: #f4f7fb;
            background: #34445d;
            border-color: #34445d;
            opacity: 1;
            -webkit-text-fill-color: #f4f7fb;
        }

        .field small {
            display: block;
            color: var(--muted);
            margin-top: 8px;
            font-size: 11px;
            line-height: 1.5;
        }

        .preview-card {
            border-radius: 16px;
            padding: 30px;
            background: linear-gradient(135deg, #2158cf, #3b82f6);
            color: white;
            margin-bottom: 22px;
            overflow: hidden;
        }

        .preview-label {
            font-size: 12px;
            letter-spacing: 1.3px;
            font-weight: 700;
            opacity: 0.8;
            text-transform: uppercase;
        }

        .preview-code {
            margin: 22px 0;
            padding: 22px 18px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 12px;
            background: rgba(255,255,255,.12);
            font-size: clamp(14px, 2.2vw, 23px);
            font-weight: 700;
            line-height: 1.8;
            overflow-wrap: anywhere;
            text-align: center;
        }

        .preview-note {
            font-size: 12px;
            opacity: .85;
            line-height: 1.6;
        }

        .reminder {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
            background: #eff6ff;
            border: 1px solid #d8e7ff;
            border-radius: 13px;
            margin-bottom: 22px;
        }

        .reminder-icon {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: white;
            color: var(--blue);
            font-size: 20px;
        }

        .reminder h3 {
            margin: 1px 0 8px;
            font-size: 14px;
        }

        .reminder p {
            margin: 0;
            font-size: 13px;
            color: #536782;
            line-height: 1.7;
        }

        .reminder strong {
            color: #1748b5;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 9px;
            border: 1px solid var(--border);
            background: white;
            color: var(--text);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--blue);
            color: white;
            border-color: var(--blue);
        }

        .btn-primary:hover {
            background: var(--blue-dark);
        }

        .btn:focus-visible {
            outline: 3px solid #a9c6ff;
            outline-offset: 2px;
        }

        .notice {
            display: none;
            padding: 12px 15px;
            border-radius: 9px;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 18px;
        }

        .notice.error {
            display: block;
            color: #9f2525;
            background: #fff1f1;
            border: 1px solid #ffd3d3;
        }

        .notice.info {
            display: block;
            color: #1748b5;
            background: #eff6ff;
            border: 1px solid #d8e7ff;
        }

        @media (max-width: 700px) {
            .topbar {
                padding: 0 20px;
            }

            .container {
                margin-top: 24px;
                padding: 0 16px 30px;
            }

            .steps {
                gap: 6px;
                overflow-x: auto;
                padding-bottom: 5px;
            }

            .step span:last-child {
                display: none;
            }

            .step-line {
                width: 24px;
                flex-shrink: 0;
            }

            .card,
            .preview-card {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .actions .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="brand">Pro<span>C</span>ert</div>
        <div class="admin">Admin Prodi</div>
    </header>

    <main class="container">
        <nav class="steps" aria-label="Tahapan pembuatan sertifikat">
            <div class="step done">
                <span class="step-number">✓</span>
                <span>Upload Template</span>
            </div>
            <span class="step-line"></span>
            <div class="step done">
                <span class="step-number">✓</span>
                <span>Data Penerima</span>
            </div>
            <span class="step-line"></span>
            <div class="step active" aria-current="step">
                <span class="step-number">3</span>
                <span>Penomoran</span>
            </div>
            <span class="step-line"></span>
            <div class="step">
                <span class="step-number">4</span>
                <span>Edit Sertifikat</span>
            </div>
        </nav>

        <section class="heading">
            <h1>Pengaturan Nomor Sertifikat</h1>
            <p>
                Atur penomoran sertifikat sesuai ketentuan program studi.
                Preview akan diperbarui secara otomatis.
            </p>
        </section>

        <section class="card">
            <div class="card-title">Format Nomor Sertifikat</div>
            <div class="card-description">
                Kode berwarna gelap merupakan ketentuan tetap dari program
                studi dan tidak perlu diisi ulang.
            </div>

            <div class="form-grid">
                <div class="field">
                    <label for="faculty">Kode Fakultas</label>
                    <input id="faculty" value="UP-DKN2.2" disabled>
                    <small>Kode tetap dari program studi.</small>
                </div>

                <div class="field">
                    <label for="document">Jenis Dokumen</label>
                    <input id="document" value="SERTIFIKAT" disabled>
                    <small>Jenis dokumen tetap.</small>
                </div>

                <div class="field">
                    <label for="studyProgram">Kode Program Studi</label>
                    <input id="studyProgram" value="PP05" disabled>
                    <small>Kode tetap dari program studi.</small>
                </div>

                <div class="field">
                    <label for="startNumber">Nomor Awal</label>
                    <input
                        id="startNumber"
                        type="number"
                        min="1"
                        step="1"
                        value="58"
                        inputmode="numeric"
                        required
                    >
                    <small>Contoh: 1 menjadi 001, 52 menjadi 052.</small>
                </div>

                <div class="field">
                    <label for="month">Bulan Sertifikat</label>
                    <select id="month">
                        <option value="1">Januari (I)</option>
                        <option value="2">Februari (II)</option>
                        <option value="3">Maret (III)</option>
                        <option value="4">April (IV)</option>
                        <option value="5">Mei (V)</option>
                        <option value="6">Juni (VI)</option>
                        <option value="7">Juli (VII)</option>
                        <option value="8">Agustus (VIII)</option>
                        <option value="9">September (IX)</option>
                        <option value="10" selected>Oktober (X)</option>
                        <option value="11">November (XI)</option>
                        <option value="12">Desember (XII)</option>
                    </select>
                    <small>Bulan dikonversi menjadi angka Romawi.</small>
                </div>

                <div class="field">
                    <label for="year">Tahun Sertifikat</label>
                    <input
                        id="year"
                        type="number"
                        min="2000"
                        max="2100"
                        value="2026"
                        required
                    >
                    <small>Penomoran dimulai kembali setiap tahun.</small>
                </div>
            </div>
        </section>

        <section class="preview-card">
            <div class="preview-label">Preview Nomor Sertifikat</div>
            <div class="preview-code" id="previewCode" aria-live="polite">
                058/UP-DKN2.2/SERTIFIKAT/PP05/X/2026
            </div>
            <div class="preview-note">
                Ini adalah preview format nomor. Ketersediaan nomor harus
                diverifikasi sebelum sertifikat diterbitkan.
            </div>
        </section>

        <section class="reminder">
            <div class="reminder-icon" aria-hidden="true">↻</div>
            <div>
                <h3>Pengingat Penomoran</h3>
                <p id="reminderText">
                    Data contoh: nomor terakhir yang digunakan adalah
                    <strong>057</strong>. Nomor berikutnya yang disarankan
                    adalah <strong>058</strong>.
                </p>
                <p style="margin-top: 7px">
                    Pengingat ini masih berupa data contoh dan belum terhubung
                    ke database.
                </p>
            </div>
        </section>

        <div id="notice" class="notice" role="status" aria-live="polite"></div>

        <div class="actions">
            <button class="btn" type="button" id="backButton">
                ← Kembali
            </button>

            <button class="btn btn-primary" type="button" id="continueButton">
                Lanjut ke Edit Sertifikat →
            </button>
        </div>
    </main>

    <script>
        const monthRoman = {
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

        const startNumber = document.getElementById('startNumber');
        const month = document.getElementById('month');
        const year = document.getElementById('year');
        const previewCode = document.getElementById('previewCode');
        const notice = document.getElementById('notice');

        function formatNumber(value) {
            return String(value).padStart(3, '0');
        }

        function updatePreview() {
            const number = Number(startNumber.value);
            const selectedMonth = Number(month.value);
            const selectedYear = Number(year.value);

            if (
                startNumber.value.trim() === '' ||
                !Number.isInteger(number) ||
                number < 1 ||
                !Number.isInteger(selectedYear) ||
                selectedYear < 2000 ||
                selectedYear > 2100
            ) {
                previewCode.textContent = 'Lengkapi input yang valid';
                return;
            }

            previewCode.textContent = [
                formatNumber(number),
                document.getElementById('faculty').value,
                document.getElementById('document').value,
                document.getElementById('studyProgram').value,
                monthRoman[selectedMonth],
                selectedYear
            ].join('/');
        }

        [startNumber, month, year].forEach((element) => {
            element.addEventListener('input', updatePreview);
            element.addEventListener('change', updatePreview);
        });

        document.getElementById('continueButton').addEventListener('click', () => {
            const number = Number(startNumber.value);
            const selectedYear = Number(year.value);

            if (
                startNumber.value.trim() === '' ||
                !Number.isInteger(number) ||
                number < 1 ||
                !Number.isInteger(selectedYear) ||
                selectedYear < 2000 ||
                selectedYear > 2100
            ) {
                notice.className = 'notice error';
                notice.textContent =
                    'Masukkan nomor awal dan tahun yang valid terlebih dahulu.';
                startNumber.focus();
                return;
            }

            notice.className = 'notice info';
            notice.textContent =
                'Preview berhasil dibuat. Validasi nomor dengan database dan ' +
                'navigasi ke Step 4 akan dihubungkan pada tahap backend.';
        });

        document.getElementById('backButton').addEventListener('click', () => {
            notice.className = 'notice info';
            notice.textContent =
                'Navigasi kembali ke Step 2 akan dihubungkan setelah rute Step 2 tersedia.';
        });

        updatePreview();
    </script>
</body>
</html>
