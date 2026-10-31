
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Review & Download | ProCert</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Lato:ital,wght@0,400;0,700;1,400;1,700&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Roboto:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Merriweather:ital,wght@0,400;0,700;1,400;1,700&family=Nunito:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&family=Raleway:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&family=Oswald:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Roboto+Slab:wght@400;500;600;700&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            background: #fff;
            color: #17191f;
        }

        .app-shell {
            width: 100%;
            min-height: 100vh;
            padding: 0 6%;
        }

        .review-page {
            margin: 43px auto 0;
            padding-bottom: 40px;
        }

        .review-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 12px;
        }

        .review-heading h1 {
            margin: 0;
            font-size: 29px;
            font-weight: 800;
        }

        .review-heading p {
            margin-top: 8px;
            color: #8c9099;
            font-size: 14px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-button {
            width: 40px;
            height: 40px;
            min-width: 40px;
            flex: 0 0 40px;

            display: inline-flex;
            justify-content: center;
            align-items: center;

            border: 2px solid #2454ff;
            border-radius: 50%;

            color: #2454ff;
            background: #ffffff;
            text-decoration: none;
            transition: all .2s ease;
        }

        .back-button svg {
            width: 17px;
            height: 17px;

            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .back-button:hover {
            background: #2454FF;
            color: #ffffff;
            border-color: #2454FF;
        }

        .download-button {
            width: 140px;
            height: 42px;
            padding: 0 14px;
            border: 1px solid #dedfe3;
            border-radius: 7px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-family: inherit;
            font-size: 12px;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            cursor: pointer;
        }

        .zip {
            background: #FFE014;
            border-color: #FFE014;
        }

        .pdf {
            background: #EE4545;
            border-color: #EE4545;
        }

        .review-layout {
            display: grid;
            grid-template-columns: minmax(220px, 29.5%) minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .actions {
            display: flex !important;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-shrink: 0;
        }

        /* DAFTAR PENERIMA */
        .recipient-panel {
            height: 570px;
            display: flex;
            flex-direction: column;
            border: 1px solid #dedfe3;
            border-radius: 9px;
            background: #fff;
            overflow: hidden;
        }

        /* HEADER */
        .recipient-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 18px;
            border-bottom: 1px solid #eceef2;
        }

        .recipient-header h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #17191f;
        }

        .recipient-header p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #8c9099;
        }

        .recipient-count {
            min-width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #f0f4ff;
            color: #2454ff;
            font-size: 12px;
            font-weight: 800;
        }

            /* SEARCH */
            .search-wrapper {
                position: relative;
                padding: 13px 14px;
                border-bottom: 1px solid #eceef2;
            }

            .search-icon {
                position: absolute;
                left: 27px;
                top: 50%;
                transform: translateY(-50%);
                color: #8c9099;
                font-size: 19px;
            }

            .search-input {
                width: 100%;
                height: 40px;
                padding: 0 12px 0 37px;
                border: 1px solid #e1e5ed;
                border-radius: 7px;
                outline: none;
                background: #fafbfe;
                font-family: inherit;
                font-size: 12px;
            }

            .search-input:focus {
                border-color: #2454ff;
                background: #fff;
            }

            /* LIST */
            .recipient-list {
                flex: 1;
                overflow-y: auto;
                padding: 8px;
            }

            .recipient-item {
                width: 100%;
                min-height: 52px;
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 12px;
                margin-bottom: 5px;
                border: 1px solid transparent;
                border-radius: 7px;
                background: #fff;
                text-align: left;
                font-family: inherit;
                cursor: pointer;
                transition: .2s ease;
            }

            .recipient-number {
                width: 30px;
                height: 30px;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-shrink: 0;
                border-radius: 6px;
                background: #f3f5f9;
                color: #7c8493;
                font-size: 10px;
                font-weight: 800;
            }

            .recipient-name {
                flex: 1;
                min-width: 0;
                overflow: hidden;
                color: #343943;
                font-size: 12px;
                font-weight: 600;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .recipient-arrow {
                color: #a0a6b2;
                font-size: 20px;
            }

            .recipient-item:hover {
                background: #f6f8ff;
                border-color: #e1e8ff;
            }

            .recipient-item.active {
                background: #edf2ff;
                border-color: #dce5ff;
                box-shadow: inset 3px 0 0 #2454ff;
            }

            .recipient-item.active .recipient-number {
                background: #2454ff;
                color: #fff;
            }

            .recipient-item.active .recipient-name,
            .recipient-item.active .recipient-arrow {
                color: #2454ff;
            }

            .recipient-panel {
                height: 250px;
            }

            /* AREA PREVIEW SERTIFIKAT */
            .certificate-area {
                width: 100%;
                min-height: 340px;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 16px;
                border: 1px solid #dedfe3;
                border-radius: 8px;
                background: #f8faff;
                overflow: hidden;
            }

            /* UKURAN TEMPLATE MENGIKUTI GAMBAR ASLI */
            .certificate-stage {
                position: relative;
                width: 100%;
                max-width: 580px;
                height: auto;
                aspect-ratio: auto;
                flex: 0 1 auto;
                overflow: hidden;
                background: white;
            }

            /* GAMBAR TEMPLATE */
            .certificate-stage img {
                display: block;
                position: relative;
                width: 100%;
                height: auto;
                object-fit: contain;
                pointer-events: none;
            }

            /* TEMPLATE PDF */
            .certificate-stage iframe {
                display: block;
                width: 100%;
                aspect-ratio: 1.414;
                border: none;
                pointer-events: none;
            }

            .certificate-text {
                position: absolute;
                z-index: 2;
                width: max-content;
                max-width: 90%;
                padding: 4px 8px;
                line-height: 1.25;
                white-space: normal;
                overflow-wrap: anywhere;
                transform: translate(-50%, -50%);
                text-align: center;
            }

            .pagination {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 8px;
                margin-top: 10px;
                color: #888;
                font-size: 12px;
            }

            .pagination button {
                width: 28px;
                height: 25px;
                border: 1px solid #dedfe3;
                border-radius: 4px;
                background: white;
                color: #777;
                font-size: 18px;
                cursor: pointer;
            }

            .download-button:disabled {
                opacity: 1;
                cursor: not-allowed;
            }

            @media(max-width: 700px) {
                .app-shell {
                    padding: 0 4%;
                }

                .review-heading {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .actions {
                    flex-wrap: wrap;
                }

                .review-layout {
                    grid-template-columns: 1fr;
                }

                .recipient-panel {
                    height: 200px;
                }

                .certificate-area {
                    min-height: 0;
                    padding: 15px;
                }
            }

            .download-button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
                pointer-events: none;
            }
        </style>
    </head>

    <body>

    <div class="app-shell">

        {{-- HEADER / PROGRESS --}}
        @include('partials.wizard-header', ['currentStep' => 5])

        <main class="review-page">


        {{-- JUDUL DAN TOMBOL --}}
        <div class="review-heading">

            <div class="heading-text">
                <h1>Review & Download</h1>
                <p>Tambahkan daftar nama penerima sertifikat</p>
            </div>

            {{-- KELOMPOK TOMBOL --}}
            <div class="actions">

                {{-- KEMBALI --}}
                <a href="{{ route('certificate-edit.index') }}"
                class="back-button"
                title="Kembali ke Edit Sertifikat">
                    <span class="back-arrow">←</span>
                </a>

                {{-- DOWNLOAD ZIP --}}
                <button type="button" class="download-button zip" disabled>
                    Download (ZIP)
                </button>

                {{-- DOWNLOAD PDF --}}
                <button type="button" class="download-button pdf" disabled>
                    Download PDF
                </button>

            </div>

        </div>

            
            {{-- KONTEN REVIEW --}}
            <div class="review-layout">

            {{-- KOLOM KIRI: DAFTAR PENERIMA --}}
            <div class="recipient-panel">

                <div class="recipient-header">
                    <div>
                        <h3>Daftar Penerima</h3>
                            <p>Pilih nama untuk melihat sertifikat</p>
                    </div>

                <span class="recipient-count">
                    {{ count($recipients) }}
                </span>
            </div>

            <div class="search-wrapper">
                <span class="search-icon">⌕</span>

                <input
                    type="text"
                    class="search-input"
                    id="searchName"
                    placeholder="Cari nama penerima..."
                >
            </div>

            <div class="recipient-list" id="recipientList">

                @foreach($recipients as $index => $recipient)

                    <button
                        type="button"
                        class="recipient-item {{ $index === 0 ? 'active' : '' }}"
                        data-index="{{ $index }}"
                    >
                        <span class="recipient-number">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <span class="recipient-name">
                            {{ $recipient['name'] }}
                        </span>

                        <span class="recipient-arrow">›</span>
                    </button>

                @endforeach

            </div>

        </div>

        {{-- KOLOM KANAN: PREVIEW SERTIFIKAT --}}
        <div class="preview-section">

            <div class="certificate-area">

                <div class="certificate-stage" id="certificateStage">

                    @if(str_starts_with($template['mime'], 'image/'))

                        <img
                            src="{{ route('templates.preview') }}"
                            alt="Template Sertifikat"
                        >

                    @elseif($template['mime'] === 'application/pdf')

                        <iframe
                            src="{{ route('templates.preview') }}"
                            title="Template Sertifikat"
                        ></iframe>

                    @endif

                    <div class="certificate-text" id="nameElement"></div>

                    <div class="certificate-text" id="numberElement"></div>

                </div>

            </div>

            {{-- NOMOR HALAMAN --}}
            <div class="pagination">

                <button type="button" id="previousPage">‹</button>

                <span>
                    <span id="currentPage">1</span>
                    /
                    <span id="totalPage">{{ count($recipients) }}</span>
                </span>

                <button type="button" id="nextPage">›</button>

            </div>

        </div>

    </div>

                    {{-- NOMOR HALAMAN --}}
                    <div class="pagination">

                        <button type="button" id="previousPage">‹</button>

                        <span>
                            <span id="currentPage">1</span>
                            /
                            <span id="totalPage">{{ count($recipients) }}</span>
                        </span>

                        <button type="button" id="nextPage">›</button>

                    </div>

                </div>

            </div>

        </main>
    </div>

    <script>

        const recipients = @json($recipients);
        const numberSettings = @json($numberSettings);
        const editSettings = @json($editSettings);

        let currentIndex = 0;

        const nameElement = document.getElementById('nameElement');
        const numberElement = document.getElementById('numberElement');

        const romanMonths = {
            1: 'I', 2: 'II', 3: 'III', 4: 'IV',
            5: 'V', 6: 'VI', 7: 'VII', 8: 'VIII',
            9: 'IX', 10: 'X', 11: 'XI', 12: 'XII'
        };

        // MENGHASILKAN NOMOR SERTIFIKAT
        function buildNumber(index) {

            const sequence =
                Number(numberSettings.start_number) + index;

            return [
                String(sequence).padStart(3, '0'),
                'UP-DKN2.2',
                'SERTIFIKAT',
                'PP05',
                romanMonths[numberSettings.month],
                numberSettings.year
            ].join('/');
        }

        // MENGAMBIL STYLE DARI STEP 4
        function getStyle(element) {

            const individual =
                editSettings.individual?.[currentIndex]?.[element];

            return {
                ...(editSettings.global?.[element] ?? {}),
                ...(individual ?? {})
            };
        }

        // MENAMPILKAN STYLE SERTIFIKAT
        function renderElement(element, style, content) {

            element.textContent = content;

            element.style.left = style.x + '%';
            element.style.top = style.y + '%';

            element.style.fontFamily =
                `"${style.font_family}", sans-serif`;

            element.style.fontSize = style.font_size + 'px';
            element.style.color = style.color;

            element.style.fontWeight = style.bold ? '700' : '400';
            element.style.fontStyle = style.italic ? 'italic' : 'normal';

            element.style.textDecoration =
                style.underline ? 'underline' : 'none';

            element.style.textAlign = style.alignment;
        }

        // RENDER PREVIEW
        function render() {

            const recipient = recipients[currentIndex];

            renderElement(
                nameElement,
                getStyle('name'),
                recipient.name
            );

            renderElement(
                numberElement,
                getStyle('number'),
                buildNumber(currentIndex)
            );

            document.getElementById('currentPage').textContent =
                currentIndex + 1;

            document.getElementById('previousPage').disabled =
                currentIndex === 0;

            document.getElementById('nextPage').disabled =
                currentIndex === recipients.length - 1;

            document.querySelectorAll('.recipient-item').forEach((item, index) => {
                item.classList.toggle('active', index === currentIndex);
            });
        }

        // PILIH NAMA
        document.querySelectorAll('.recipient-item').forEach(item => {

            item.addEventListener('click', () => {

                currentIndex = Number(item.dataset.index);

                render();

            });

        });

        // HALAMAN SEBELUMNYA
        document.getElementById('previousPage').addEventListener('click', () => {

            if (currentIndex > 0) {
                currentIndex--;
                render();
            }

        });

        // HALAMAN SELANJUTNYA
        document.getElementById('nextPage').addEventListener('click', () => {

            if (currentIndex < recipients.length - 1) {
                currentIndex++;
                render();
            }

        });

        // PENCARIAN NAMA
        document.getElementById('searchName').addEventListener('input', function() {

            const keyword = this.value.toLowerCase();

            document.querySelectorAll('.recipient-item').forEach(item => {

                item.style.display =
                    item.textContent.toLowerCase().includes(keyword)
                    ? 'flex'
                    : 'none';

            });

        });

        // TAMPILKAN SERTIFIKAT PERTAMA
        render();

    </script>

    </body>
    </html>
