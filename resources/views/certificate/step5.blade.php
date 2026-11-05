
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Review & Download | ProCert</title>
    @include('partials.favicon')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Lato:ital,wght@0,400;0,700;1,400;1,700&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Roboto:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Merriweather:ital,wght@0,400;0,700;1,400;1,700&family=Nunito:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&family=Raleway:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&family=Oswald:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Roboto+Slab:wght@400;500;600;700&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

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
                <p>Periksa kembali seluruh sertifikat sebelum mengunduh.</p>
            </div>

            {{-- KELOMPOK TOMBOL --}}
            <div class="actions">

        <a href="{{ route('certificate-edit.index') }}"
        class="review-back-button"
        aria-label="Kembali ke Edit Sertifikat">
            <svg viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M19 12H5"/>
                <path d="m12 19-7-7 7-7"/>
            </svg>
        </a>

                <a href="{{ route('certificate-export.zip') }}" class="download-button zip">
                    Download (ZIP)
                </a>

                <a href="{{ route('certificate-export.pdf') }}" class="download-button pdf">
                    Download PDF
                </a>

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
                    <div id="textBoxLayer"></div>

                </div>
            </div>

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
        const textBoxLayer = document.getElementById('textBoxLayer');

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

            textBoxLayer.replaceChildren();
            const recipientTextBoxes = editSettings.individual?.[currentIndex]?.textBoxes;
            const textBoxes = editSettings.syncTextBoxes
                ? (editSettings.global?.textBoxes ?? [])
                : (recipientTextBoxes ?? editSettings.global?.textBoxes ?? []);
            const legacyTextBox = editSettings.global?.customText;

            if (textBoxes.length === 0 && legacyTextBox?.content) {
                textBoxes.push(legacyTextBox);
            }

            textBoxes.forEach(textBox => {
                const element = document.createElement('div');
                element.className = 'certificate-text';
                renderElement(element, textBox, textBox.content);
                textBoxLayer.append(element);
            });

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
