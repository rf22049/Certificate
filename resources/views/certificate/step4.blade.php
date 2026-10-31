<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Edit Sertifikat | ProCert</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700&family=Lato:ital,wght@0,400;0,700;1,400;1,700&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Roboto:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Merriweather:ital,wght@0,400;0,700;1,400;1,700&family=Nunito:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&family=Raleway:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&family=Oswald:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Roboto+Slab:wght@400;500;600;700&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; }

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

        .edit-page {
            margin: 43px auto 0;
            padding-bottom: 40px;
        }

        .edit-heading h1 {
            margin: 0;
            font-size: 29px;
            font-weight: 800;
        }

        .edit-heading p {
            margin-top: 8px;
            color: #8c9099;
            font-size: 14px;
        }

        .editor-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(320px, .85fr);
            margin-top: 26px;
            border: 1px solid #dedfe3;
            border-radius: 10px;
            overflow: hidden;
            min-height: 620px;
        }

        .preview-panel {
            padding: 22px;
            border-right: 1px solid #dedfe3;
            min-width: 0;
        }

        .preview-toolbar,
        .toolbar-group,
        .element-heading,
        .setting-row,
        .editor-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .preview-toolbar { margin-bottom: 18px; }

        .toolbar-group { justify-content: flex-start; }

        .tool-button,
        .nav-button,
        .action-button {
            border: 1px solid #dfe2e8;
            background: #fff;
            color: #34445d;
            border-radius: 7px;
            padding: 9px 12px;
            font: inherit;
            font-size: 12px;
            cursor: pointer;
        }

        .tool-button:hover,
        .nav-button:hover {
            border-color: #2454ff;
            color: #2454ff;
        }

        .tool-button:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        .tool-button:not(:disabled) {
            color: #2454ff;
        }

        .zoom-label {
            min-width: 48px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }

        .preview-canvas {
            min-height: 440px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
            padding: 20px;
            background: #f3f5f9;
            border: 1px solid #e1e4eb;
            border-radius: 8px;
        }

        .certificate-stage {
            position: relative;
            flex: 0 0 auto;
            width: 100%;
            max-width: none;
            margin: auto;
            transform-origin: top left;
            background: white;
            box-shadow: 0 2px 12px #00000012;
            user-select: none;
            flex-shrink: 0;
        }

        .certificate-stage > img {
            display: block;
            width: 100%;
            height: auto;
            pointer-events: none;
        }

        .certificate-stage > iframe {
            display: block;
            width: 100%;
            aspect-ratio: 1.414;
            border: 0;
            pointer-events: none;
        }

        .certificate-text {
            position: absolute;
            z-index: 2;
            width: max-content;
            max-width: 90%;
            padding: 4px 8px;
            border: 1px solid transparent;
            border-radius: 3px;
            cursor: grab;
            white-space: normal;
            overflow-wrap: anywhere;
            user-select: none;
            touch-action: none;
            line-height: 1.25;
            transform: translate(-50%, -50%);
            text-align: center;
            touch-action: none;
        }

        .certificate-text:active { cursor: grabbing; }

        .certificate-text.selected {
            border: 1px dashed #2454ff;
            outline: 2px solid #2454ff22;
        }

        .certificate-text.selected::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            right: -5px;
            bottom: -5px;
            border-radius: 50%;
            background: #2454ff;
        }

        .editor-panel {
            min-width: 0;
            padding: 24px;
            background: #fff;
        }

        .editor-panel h2 {
            margin: 0 0 5px;
            font-size: 19px;
            font-weight: 800;
        }

        .muted {
            color: #8c9099;
            font-size: 12px;
            line-height: 1.6;
        }

        .element-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 22px 0;
        }

        .element-tab {
            padding: 12px;
            border: 1px solid #dfe2e8;
            border-radius: 8px;
            background: #fff;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .element-tab.active {
            border-color: #2454ff;
            background: #f2f5ff;
            color: #2454ff;
        }

        .setting-section {
            padding: 18px 0;
            border-top: 1px solid #eceef2;
        }

        .setting-section h3 {
            margin: 0 0 15px;
            font-size: 13px;
            font-weight: 800;
        }

        .field { margin-bottom: 15px; }

        .field label {
            display: block;
            margin-bottom: 7px;
            font-size: 12px;
            font-weight: 700;
        }

        .field input:not([type="checkbox"]):not([type="color"]),
        .field select {
            width: 100%;
            height: 40px;
            padding: 0 10px;
            border: 1px solid #dfe2e8;
            border-radius: 7px;
            background: #fff;
            font: inherit;
            font-size: 12px;
        }

        .field input:focus,
        .field select:focus {
            outline: 2px solid #2454ff22;
            border-color: #2454ff;
        }

        .field input[type="color"] {
            width: 100%;
            height: 40px;
            padding: 3px;
            border: 1px solid #dfe2e8;
            border-radius: 7px;
            background: #fff;
        }

        .field input[type="range"] { width: 100%; }

        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .style-buttons {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .style-toggle {
            display: flex;
            width: 43px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid #dfe2e8;
            border-radius: 9px;
            background: #fff;
            color: #343943;
            cursor: pointer;
            transition: background.18s ease,
                        border-color .18s ease,
                        color .18s ease;
        }

        .style-toggle svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .style-toggle:hover {
            border-color: #2454ff;
            color: #2454ff;
        }

        .style-toggle.active {
            background: #2454ff;
            border-color: #2454ff;
            color: #fff;
        }

        .alignment-buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .alignment-buttons button {
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dfe2e8;
            border-radius: 9px;
            background: #fff;
            color: #17191f;
            cursor: pointer;
            transition: .2s ease;
        }

        .alignment-buttons button:hover {
            border-color: #2454ff;
        }

        .alignment-buttons button.active {
            background: #f2f5ff;
            border-color: #2454ff;
            color: #2454ff;
        }

        .action-button {
            min-height: 42px;
            font-weight: 800;
        }

        .primary-button {
            background: #2454ff;
            color: #fff;
            border-color: #2454ff;
        }

        .primary-button:hover {
            background: #1745ed;
            color: #fff;
        }

        .primary-button:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .full-width { width: 100%; }

        .editor-actions {
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .name-editor { margin-bottom: 15px; }

        .name-editor input {
            width: 100%;
            height: 42px;
            padding: 0 10px;
            border: 1px solid #dfe2e8;
            border-radius: 7px;
            font: inherit;
            font-size: 12px;
        }

        .locked-value {
            padding: 11px;
            border-radius: 7px;
            background: #f3f5f9;
            color: #68758a;
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        .status-message {
            margin-top: 15px;
            color: #16834b;
            font-size: 12px;
        }

        .preview-navigation {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            margin-top: 18px;
        }

        .preview-canvas {
            cursor: grab;
            overflow: auto;
            justify-content: flex-start;
            align-items: flex-start;
            touch-action: none;
        }

        .preview-canvas.is-panning {
            cursor: grabbing;
        }

        .nav-button {
            min-width: 115px;
            min-height: 40px;
            padding: 0 14px;
            border: 1px solid #dfe2e8;
            border-radius: 8px;
            background: #fff;
            color: #2454ff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            transition: .2s ease;
        }

        .nav-button:hover:not(:disabled) {
            border-color: #2454ff;
            background: #f2f5ff;
        }

        .nav-button:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        #pageIndicator {
            min-width: 55px;
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            color: #17191f;
        }

        .editor-empty {
            padding: 22px 14px;
            border: 1px dashed #dfe2e8;
            border-radius: 9px;
            background: #f8faff;
            color: #8c9099;
            font-size: 12px;
            line-height: 1.7;
            text-align: center;
        }

        .element-tab:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .editor-navigation {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: stretch;
            gap: 12px;
            width: 100%;
            margin-top: 24px;
        }

        .editor-navigation .action-button {
            display: flex;
            width: 100%;
            min-width: 0;
            min-height: 52px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 12px;
            border: 1px solid #dfe2e8;
            border-radius: 8px;
            background: #fff;
            color: #2454ff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .editor-navigation .action-button:hover {
            border-color: #2454ff;
            background: #f6f8ff;
        }

        .editor-navigation .action-button.primary-button {
            border-color: #2454ff;
            background: #2454ff;
            color: #fff;
        }

        .editor-navigation .action-button.primary-button:hover {
            border-color: #1745ed;
            background: #1745ed;
        }

        .editor-navigation .action-button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        @media (max-width: 600px) {
            .editor-navigation {
                gap: 8px;
            }

            .editor-navigation .action-button {
                min-height: 48px;
                font-size: 12px;
            }
        }

        @media (max-width: 950px) {
            .editor-layout { grid-template-columns: 1fr; }
            .preview-panel {
                border-right: 0;
                border-bottom: 1px solid #dedfe3;
            }
        }

        @media (max-width: 600px) {
            .app-shell { padding: 0 4%; }
            .edit-page { margin-top: 28px; }
            .preview-panel, .editor-panel { padding: 15px; }
            .preview-canvas { min-height: 280px; padding: 10px; }
        }
    </style>
</head>

<body>
<div class="app-shell">

    @include('partials.wizard-header', ['currentStep' => 4])

    <main class="edit-page">

        <header class="edit-heading">
            <h1>Edit Desain Sertifikat</h1>
            <p>Atur nama, nomor, dan tampilan sertifikat sebelum diterbitkan.</p>
        </header>

        <section class="editor-layout">

            {{-- PREVIEW --}}
            <div class="preview-panel">

                <div class="preview-toolbar">
                    <strong>Preview Sertifikat</strong>

                    <div class="toolbar-group">
                        <button type="button" class="tool-button" id="undoButton" title="Undo" disabled>↶</button>
                        <button type="button" class="tool-button" id="redoButton" title="Redo" disabled>↷</button>
                        <button type="button" class="tool-button" id="zoomOut">−</button>
                        <span class="zoom-label" id="zoomLabel">100%</span>
                        <button type="button" class="tool-button" id="zoomIn">+</button>
                    </div>
                </div>

                <div class="preview-canvas" id="previewCanvas">
                    <div class="certificate-stage" id="certificateStage">

                        @if (str_starts_with($template['mime'], 'image/'))
                            <img
                                src="{{ route('templates.preview') }}"
                                alt="Template Sertifikat"
                                id="templateImage"
                            >
                        @elseif ($template['mime'] === 'application/pdf')
                            <iframe
                                src="{{ route('templates.preview') }}"
                                title="Template Sertifikat"
                            ></iframe>
                        @endif

                        <div class="certificate-text recipient-name" id="nameElement"></div>
                        <div class="certificate-text certificate-number" id="numberElement"></div>

                    </div>
                </div>

                <div class="preview-navigation">
                    <button type="button" class="nav-button" id="previousRecipient">
                        ← 
                    </button>

                    <strong id="pageIndicator">1 / {{ count($recipients) }}</strong>

                    <button type="button" class="nav-button" id="nextRecipient">
                        →
                    </button>
                </div>

            </div>

            {{-- EDITOR TOOLS --}}
            <aside class="editor-panel">

                <h2>Pengaturan Elemen</h2>
                <p class="muted">Pilih elemen pada sertifikat untuk mengatur tampilannya.</p>

                <div class="element-tabs">
                    <button type="button" class="element-tab active" data-element="name">
                        Nama Penerima
                    </button>
                    <button type="button" class="element-tab" data-element="number">
                        Nomor Sertifikat
                    </button>
                </div>

                <div class="editor-empty" id="emptySelection" hidden>
                    Belum ada elemen yang dipilih.
                    <br>
                    Klik nama atau nomor pada preview untuk mulai mengedit.
                </div>

                <div id="nameSettings">

                    <div class="setting-section">
                        <h3>Data Penerima</h3>

                        <div class="name-editor field">
                            <label for="recipientNameInput">Nama Penerima</label>
                            <input type="text" id="recipientNameInput" maxlength="255">
                        </div>
                    </div>

                </div>

                <div id="numberSettingsPanel" hidden>
                    <div class="setting-section">
                        <h3>Nomor Sertifikat</h3>
                        <div class="locked-value" id="numberValue"></div>
                        <p class="muted">Isi nomor dikelola otomatis oleh sistem dan tidak dapat diedit.</p>
                    </div>
                </div>

                <div class="setting-section">
                    <h3>Tipografi</h3>

                    <div class="field">
                        <label for="fontFamily">Jenis Font</label>
                        <select id="fontFamily">
                            <option value="Manrope">Manrope</option>
                            <option value="Poppins">Poppins</option>
                            <option value="Montserrat">Montserrat</option>
                            <option value="Lato">Lato</option>
                            <option value="Open Sans">Open Sans</option>
                            <option value="Roboto">Roboto</option>
                            <option value="Nunito">Nunito</option>
                            <option value="Raleway">Raleway</option>
                            <option value="Oswald">Oswald</option>
                            <option value="Playfair Display">Playfair Display</option>
                            <option value="Merriweather">Merriweather</option>
                            <option value="Lora">Lora</option>
                            <option value="Roboto Slab">Roboto Slab</option>
                            <option value="DM Sans">DM Sans</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                        </select>
                    </div>

                    <div class="two-columns">
                        <div class="field">
                            <label for="fontSize">Ukuran Font</label>
                            <input type="number" id="fontSize" min="8" max="150" value="24">
                        </div>

                        <div class="field">
                            <label for="fontColor">Warna</label>
                            <input type="color" id="fontColor" value="#4042a4">
                        </div>
                    </div>

                    <div class="field">
                        <label>Gaya Teks</label>
                        <div class="style-buttons">
                            <button type="button" class="style-toggle" id="boldButton" title="Bold" aria-label="Bold">
                                <svg viewBox="0 0 24 24">
                                    <path d="M7 5h6a4 4 0 0 1 0 8H7z"/>
                                    <path d="M7 13h7a4 4 0 0 1 0 8H7z"/>
                                </svg>
                            </button>

                            <button type="button" class="style-toggle" id="italicButton" title="Italic" aria-label="Italic">
                                <svg viewBox="0 0 24 24">
                                    <path d="M19 4H10M14 20H5M15 4 9 20"/>
                                </svg>
                            </button>

                            <button type="button" class="style-toggle" id="underlineButton" title="Underline" aria-label="Underline">
                                <svg viewBox="0 0 24 24">
                                    <path d="M7 4v7a5 5 0 0 0 10 0V4"/>
                                    <path d="M5 21h14"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label>Alignment</label>
                        <div class="alignment-buttons">
                            <button type="button" data-align="left" title="Rata kiri">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 5h16M4 9h10M4 13h16M4 17h10"/>
                                </svg>
                            </button>

                            <button type="button" data-align="center" title="Rata tengah">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 5h16M7 9h10M4 13h16M7 17h10"/>
                                </svg>
                            </button>

                            <button type="button" data-align="right" title="Rata kanan">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 5h16M10 9h10M4 13h16M10 17h10"/>
                                </svg>
                            </button>

                            <button type="button" data-align="justify" title="Rata kanan kiri">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 5h16M4 9h16M4 13h16M4 17h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="editor-actions">
                    <button type="button"
                        class="action-button primary-button full-width"
                        id="applyAll">
                        Terapkan ke Semua Sertifikat
                    </button>
                </div>

                <div class="editor-actions editor-navigation">
                    <a href="{{ route('certificate-number.index') }}"
                    class="action-button">
                        <span>←</span>
                        Kembali
                    </a>

                    <button type="button"
                            class="action-button primary-button"
                            id="saveButton">
                        Lanjut
                        <span>→</span>
                    </button>
                </div>

                <div class="status-message" id="statusMessage" role="status"></div>

            </aside>

        </section>

        <form id="saveForm" method="POST" action="{{ route('certificate-edit.save') }}" hidden>
            @csrf
        </form>

    </main>
</div>

<script>

    const recipients = @json($recipients);
    const numberSettings = @json($numberSettings);
    const savedSettings = @json($editSettings);

    const romanMonths = {
        1: 'I', 2: 'II', 3: 'III', 4: 'IV',
        5: 'V', 6: 'VI', 7: 'VII', 8: 'VIII',
        9: 'IX', 10: 'X', 11: 'XI', 12: 'XII'
    };

    const defaultNameStyle = {
        font_family: 'Manrope',
        font_size: 24,
        color: '#4042A4',
        alignment: 'center',
        bold: true,
        italic: false,
        underline: false,
        x: 50,
        y: 50
    };

    const defaultNumberStyle = {
        ...defaultNameStyle,
        font_size: 14,
        bold: false,
        y: 70
    };

    const settings = {
        global: savedSettings.global ?? {
            name: { ...defaultNameStyle },
            number: { ...defaultNumberStyle }
        },
        individual: savedSettings.individual ?? {}
    };

    let currentIndex = 0;
    let selectedElement = 'name';
    let zoom = 1;

    let dragging = false;
    let dragPointerId = null;
    let dragStart = null;

    let isPanning = false;
    let panStartX = 0;
    let panStartY = 0;
    let scrollStartX = 0;
    let scrollStartY = 0;

    let undoStack = [];
    let redoStack = [];

    const nameElement = document.getElementById('nameElement');
    const numberElement = document.getElementById('numberElement');
    const stage = document.getElementById('certificateStage');
    const previewCanvas = document.getElementById('previewCanvas');

    const fontFamily = document.getElementById('fontFamily');
    const fontSize = document.getElementById('fontSize');
    const fontColor = document.getElementById('fontColor');

    const positionX = document.getElementById('positionX');
    const positionY = document.getElementById('positionY');

    // NOMOR SERTIFIKAT
    function buildNumber(index) {
        const sequence = Number(numberSettings.start_number) + index;

        return [
            String(sequence).padStart(3, '0'),
            'UP-DKN2.2',
            'SERTIFIKAT',
            'PP05',
            romanMonths[numberSettings.month],
            numberSettings.year
        ].join('/');
    }

    // STYLE ELEMENT
    function getStyle(element) {
        const style = settings.individual[currentIndex]?.[element]
            ?? settings.global[element];

        return {
            ...style,
            bold: style.bold === true || style.bold === 1 || style.bold === '1' || style.bold === 'true',
            italic: style.italic === true || style.italic === 1 || style.italic === '1' || style.italic === 'true',
            underline: style.underline === true || style.underline === 1 || style.underline === '1' || style.underline === 'true'
        };
    }

    function setStyle(element, style) {
        if (!settings.individual[currentIndex]) {
            settings.individual[currentIndex] = {};
        } settings.individual[currentIndex][element] = { ...style };
    }

    function renderElement(element, style, content) {
        element.textContent = content;

        element.style.left = style.x + '%';
        element.style.top = style.y + '%';
        element.style.fontFamily = `"${style.font_family}", sans-serif`;
        element.style.fontSize = style.font_size + 'px';
        element.style.color = style.color;
        element.style.fontWeight = style.bold ? '700' : '400';
        element.style.fontStyle = style.italic ? 'italic' : 'normal';
        element.style.textDecoration = style.underline ? 'underline' : 'none';
        element.style.textAlign = style.alignment;
        element.style.fontSynthesis = 'style';
    }

    // UNDO / REDO
    function createSnapshot() {
        return JSON.stringify({
            recipients,
            settings,
            currentIndex,
            selectedElement
        });
    }

    function updateHistoryButtons() {
        document.getElementById('undoButton').disabled = undoStack.length === 0;
        document.getElementById('redoButton').disabled = redoStack.length === 0;
    }

    function saveHistory() {
        undoStack.push(createSnapshot());

        if (undoStack.length > 100) {
            undoStack.shift();
        } redoStack = [];
        updateHistoryButtons();
    }

    function restoreSnapshot(snapshot) {
        const data = JSON.parse(snapshot);

        recipients.splice(0, recipients.length, ...data.recipients);

        settings.global = data.settings.global;
        settings.individual = data.settings.individual;

        currentIndex = data.currentIndex;
        selectedElement = data.selectedElement;

        render();
        updateHistoryButtons();
    }

    document.getElementById('undoButton').addEventListener('click', () => {
        if (undoStack.length === 0) return;

        redoStack.push(createSnapshot());
        restoreSnapshot(undoStack.pop());
    });

    document.getElementById('redoButton').addEventListener('click', () => {
        if (redoStack.length === 0) return;

        undoStack.push(createSnapshot());
        restoreSnapshot(redoStack.pop());
    });

    // RENDER PREVIEW
    function render() {
        const recipient = recipients[currentIndex];

        document.getElementById('recipientNameInput').value = recipient.name;
        document.getElementById('numberValue').textContent = buildNumber(currentIndex);

        renderElement(nameElement, getStyle('name'), recipient.name);
        renderElement(numberElement, getStyle('number'), buildNumber(currentIndex));

        nameElement.classList.toggle('selected', selectedElement === 'name');
        numberElement.classList.toggle('selected', selectedElement === 'number');

        document.getElementById('pageIndicator').textContent =
            `${currentIndex + 1} / ${recipients.length}`;

        document.getElementById('previousRecipient').disabled = currentIndex === 0;
        document.getElementById('nextRecipient').disabled =
            currentIndex === recipients.length - 1;

        const hasSelection = selectedElement !== null;

        document.getElementById('nameSettings').hidden =
            !hasSelection || selectedElement !== 'name';

        document.getElementById('numberSettingsPanel').hidden =
            !hasSelection || selectedElement !== 'number';

        document.getElementById('emptySelection').hidden = hasSelection;

        document.querySelectorAll('.element-tab').forEach(button => {
            button.classList.toggle(
                'active',
                button.dataset.element === selectedElement
            );
        });

        document.querySelectorAll('.setting-section').forEach(section => {
            section.hidden = !hasSelection;
        });

        document.getElementById('applyAll').disabled = !hasSelection;

        if (hasSelection) {
            updateControls();
        }
    }

    // PANEL PENGATURAN
    function updateControls() {
        const style = getStyle(selectedElement);

        fontFamily.value = style.font_family;
        fontSize.value = style.font_size;
        fontColor.value = style.color;

        if (positionX) positionX.value = Number(style.x.toFixed(1));
        if (positionY) positionY.value = Number(style.y.toFixed(1));

        document.getElementById('boldButton')
            .classList.toggle('active', style.bold);

        document.getElementById('italicButton')
            .classList.toggle('active', style.italic);

        document.getElementById('underlineButton')
            .classList.toggle('active', style.underline);

        document.querySelectorAll('[data-align]').forEach(button => {
            button.classList.toggle(
                'active',
                button.dataset.align === style.alignment
            );
        });
    }

    function updateCurrentStyle(field, value) {
        if (!selectedElement) return;
        saveHistory();

        const style = { ...getStyle(selectedElement) };
        style[field] = value;

        setStyle(selectedElement, style);
        render();
    }

    // PILIH ELEMENT
    document.querySelectorAll('.element-tab').forEach(button => {
        button.addEventListener('click', () => {
            selectedElement = button.dataset.element;
            render();
        });
    });

    nameElement.addEventListener('click', () => {
        selectedElement = 'name';
        render();
    });

    numberElement.addEventListener('click', () => {
        selectedElement = 'number';
        render();
    });

    previewCanvas.addEventListener('click', event => {
        const clickedEmptyArea =
            event.target === previewCanvas ||
            event.target === stage ||
            event.target.id === 'templateImage';

        if (clickedEmptyArea && selectedElement !== null) {
            selectedElement = null;
            render();
        }
    });

    // EDIT NAMA PENERIMA
    document.getElementById('recipientNameInput').addEventListener('input', event => {
        saveHistory();

        recipients[currentIndex].name = event.target.value;
        nameElement.textContent = event.target.value;
    });


    // FONT, WARNA, DAN STYLE
    fontFamily.addEventListener('change', event => {
        updateCurrentStyle('font_family', event.target.value);
    });

    fontSize.addEventListener('change', event => {
        const value = Math.max(8, Math.min(150, Number(event.target.value)));
        updateCurrentStyle('font_size', value);
    });

    fontColor.addEventListener('change', event => {
        updateCurrentStyle('color', event.target.value);
    });

    document.getElementById('boldButton').addEventListener('click', () => {
        if (!selectedElement) return;
        updateCurrentStyle('bold', !getStyle(selectedElement).bold);
    });

    document.getElementById('italicButton').addEventListener('click', () => {
        if (!selectedElement) return;
        updateCurrentStyle('italic', !getStyle(selectedElement).italic);
    });

    document.getElementById('underlineButton').addEventListener('click', () => {
        if (!selectedElement) return;
        updateCurrentStyle('underline', !getStyle(selectedElement).underline);
    });

    document.querySelectorAll('[data-align]').forEach(button => {
        button.addEventListener('click', () => {
            updateCurrentStyle('alignment', button.dataset.align);
        });
    });

    // DRAG NAMA DAN NOMOR
    function startDrag(event) {
        if (event.button !== 0) return;

        saveHistory();

        const element = event.currentTarget;

        selectedElement = element === nameElement ? 'name' : 'number';
        render();

        dragging = true;
        dragPointerId = event.pointerId;

        const rect = stage.getBoundingClientRect();
        const style = getStyle(selectedElement);

        dragStart = {
            pointerX: event.clientX,
            pointerY: event.clientY,
            x: style.x,
            y: style.y,
            width: rect.width,
            height: rect.height
        };

        element.setPointerCapture(event.pointerId);
        event.preventDefault();
    }

    function moveDrag(event) {
        if (!dragging || event.pointerId !== dragPointerId) return;

        const dx = (event.clientX - dragStart.pointerX) / dragStart.width * 100;
        const dy = (event.clientY - dragStart.pointerY) / dragStart.height * 100;

        const style = {
            ...getStyle(selectedElement),
            x: Math.max(0, Math.min(100, dragStart.x + dx)),
            y: Math.max(0, Math.min(100, dragStart.y + dy))
        };

        setStyle(selectedElement, style);
        render();
    }

    function endDrag(event) {
        if (!dragging || event.pointerId !== dragPointerId) return;

        dragging = false;
        dragPointerId = null;
    }

    [nameElement, numberElement].forEach(element => {
        element.addEventListener('pointerdown', startDrag);
        element.addEventListener('pointermove', moveDrag);
        element.addEventListener('pointerup', endDrag);
        element.addEventListener('pointercancel', endDrag);
    });


    // NAVIGASI SERTIFIKAT
    document.getElementById('previousRecipient').addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            render();
        }
    });

    document.getElementById('nextRecipient').addEventListener('click', () => {
        if (currentIndex < recipients.length - 1) {
            currentIndex++;
            render();
        }
    });


    // ZOOM
    function updateZoom() {
        const width = 100 * zoom;

        stage.style.width = width + '%';
        stage.style.flexBasis = width + '%';

        previewCanvas.style.justifyContent = 'flex-start';
        previewCanvas.style.alignItems = 'flex-start';

        document.getElementById('zoomLabel').textContent =
            Math.round(zoom * 100) + '%';
    }

    document.getElementById('zoomIn').addEventListener('click', () => {
        zoom = Math.min(3, Math.round((zoom + 0.1) * 10) / 10);
        updateZoom();
    });

    document.getElementById('zoomOut').addEventListener('click', () => {
        zoom = Math.max(0.5, Math.round((zoom - 0.1) * 10) / 10);
        updateZoom();
    });


    // GESER AREA PREVIEW
    previewCanvas.addEventListener('pointerdown', event => {
        if (event.target.closest('.certificate-text')) return;
        if (event.button !== 0) return;

        isPanning = true;

        panStartX = event.clientX;
        panStartY = event.clientY;

        scrollStartX = previewCanvas.scrollLeft;
        scrollStartY = previewCanvas.scrollTop;

        previewCanvas.classList.add('is-panning');
        previewCanvas.setPointerCapture(event.pointerId);

        event.preventDefault();
    });

    previewCanvas.addEventListener('pointermove', event => {
        if (!isPanning) return;

        const dx = event.clientX - panStartX;
        const dy = event.clientY - panStartY;

        previewCanvas.scrollLeft = scrollStartX - dx;
        previewCanvas.scrollTop = scrollStartY - dy;
    });

    function stopPanning() {
        isPanning = false;
        previewCanvas.classList.remove('is-panning');
    }

    previewCanvas.addEventListener('pointerup', stopPanning);
    previewCanvas.addEventListener('pointercancel', stopPanning);

    // APPLY ALL
    document.getElementById('applyAll').addEventListener('click', () => {
        if (!selectedElement) return;

        const confirmation = confirm(
            'Terapkan pengaturan elemen ini ke seluruh sertifikat?'
        );

        if (!confirmation) return;

        saveHistory();

        const style = { ...getStyle(selectedElement) };

        settings.global[selectedElement] = style;

        Object.keys(settings.individual).forEach(index => {
            if (settings.individual[index]) {
                delete settings.individual[index][selectedElement];

                if (Object.keys(settings.individual[index]).length === 0) {
                    delete settings.individual[index];
                }
            }
        });

        render();

        document.getElementById('statusMessage').textContent =
            'Pengaturan berhasil diterapkan ke semua sertifikat.';
    });


    // SIMPAN PERUBAHAN
    document.getElementById('saveButton').addEventListener('click', async () => {
        const button = document.getElementById('saveButton');
        const status = document.getElementById('statusMessage');

        button.disabled = true;
        button.textContent = 'Menyimpan...';
        status.textContent = '';

        const payload = {
            recipients,
            settings
        };

        try {
            const response = await fetch(
                '{{ route('certificate-edit.save') }}',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content
                    },
                    body: JSON.stringify(payload)
                }
            );

            if (!response.ok) {
                const result = await response.json().catch(() => ({}));
                throw new Error(result.message || 'Gagal menyimpan perubahan.');
            }

            status.textContent = 'Perubahan berhasil disimpan!';
        } catch (error) {
            status.textContent = error.message;
        } finally {
            button.disabled = false;
            button.textContent = 'Lanjut →';
        }
    });

    // MULAI EDITOR
    render();
    updateHistoryButtons();

</script>

</body>
</html>