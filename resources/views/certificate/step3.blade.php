
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Certificate Number Settings') }} | ProCert</title>
    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app-shell">

        {{-- Header dan progress bar bersama dengan Step 1 dan Step 2. --}}
        @include('partials.wizard-header', ['currentStep' => 3])

        <main class="number-page">

            <header class="number-heading">
                <h1>{{ __('Certificate Number Settings') }}</h1>
                <p>
                    {{ __('Set certificate numbering according to your program requirements. The preview updates automatically.') }}
                </p>
            </header>

            <form
                id="certificateNumberForm"
                method="POST"
                action="{{ route('certificate-number.store') }}"
            > @csrf

                <section class="number-card">
                    <h2 class="number-card-title">{{ __('Certificate Number Format') }}</h2>

                    <p class="number-card-description">
                        {{ __('Fixed program codes are already included and do not need to be entered again.') }}
                    </p>

                    <div class="number-form-grid">

                        <div class="number-field">
                            <label for="faculty">{{ __('Faculty Code') }}</label>
                            <input id="faculty" value="UP-DKN2.2" disabled>
                            <small>{{ __('Fixed program code.') }}</small>
                        </div>

                        <div class="number-field">
                            <label for="document">{{ __('Document Type') }}</label>
                            <input id="document" value="SERTIFIKAT" disabled>
                            <small>{{ __('Document type is fixed.') }}</small>
                        </div>

                        <div class="number-field">
                            <label for="studyProgram">{{ __('Program Code') }}</label>
                            <input id="studyProgram" value="PP05" disabled>
                            <small>{{ __('Fixed program code.') }}</small>
                        </div>

                        <div class="number-field">
                            <label for="startNumber">{{ __('Starting Number') }}</label>
                            <input
                                id="startNumber"
                                type="number"
                                name="start_number"
                                min="1"
                                max="{{ $maximumStartNumber }}"
                                step="1"
                                value="{{ $initialStartNumber }}"
                                inputmode="numeric"
                                required
                                aria-describedby="startNumberHelp"
                            >
                            <small id="startNumberHelp">
                                {{ __('Choose the sequence number for this year. Previously used numbers can still be selected. Example: 52 becomes 052.') }}
                            </small>
                            @error('start_number')
                                <small class="form-error" role="alert">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="number-field">
                            <label for="month">{{ __('Certificate Month') }}</label>
                            <select id="month" name="month" required>
                                <option value="1" {{ $selectedMonth == 1 ? 'selected' : '' }}>
                                    {{ __('January (I)') }}
                                </option>
                                <option value="2" {{ $selectedMonth == 2 ? 'selected' : '' }}>
                                    {{ __('February (II)') }}
                                </option>
                                <option value="3" {{ $selectedMonth == 3 ? 'selected' : '' }}>
                                    {{ __('March (III)') }}
                                </option>
                                <option value="4" {{ $selectedMonth == 4 ? 'selected' : '' }}>
                                    {{ __('April (IV)') }}
                                </option>
                                <option value="5" {{ $selectedMonth == 5 ? 'selected' : '' }}>
                                    {{ __('May (V)') }}
                                </option>
                                <option value="6" {{ $selectedMonth == 6 ? 'selected' : '' }}>
                                    {{ __('June (VI)') }}
                                </option>
                                <option value="7" {{ $selectedMonth == 7 ? 'selected' : '' }}>
                                    {{ __('July (VII)') }}
                                </option>
                                <option value="8" {{ $selectedMonth == 8 ? 'selected' : '' }}>
                                    {{ __('August (VIII)') }}
                                </option>
                                <option value="9" {{ $selectedMonth == 9 ? 'selected' : '' }}>
                                    September (IX)
                                </option>
                                <option value="10" {{ $selectedMonth == 10 ? 'selected' : '' }}>
                                    {{ __('October (X)') }}
                                </option>
                                <option value="11" {{ $selectedMonth == 11 ? 'selected' : '' }}>
                                    November (XI)
                                </option>
                                <option value="12" {{ $selectedMonth == 12 ? 'selected' : '' }}>
                                    {{ __('December (XII)') }}
                                </option>
                            </select>
                            <small>{{ __('The month will be converted to a Roman numeral.') }}</small>
                        </div>

                        <div class="number-field">
                            <label for="year">{{ __('Certificate Year') }}</label>
                            <input
                                id="year"
                                type="number"
                                name="year"
                                min="2000"
                                max="2100"
                                value="{{ $selectedYear }}"
                                required
                            >
                            <small>{{ __('The sequence restarts each year.') }}</small>
                        </div>
                    </div>
                </section>

                <section class="number-preview" aria-label="{{ __('Certificate number preview') }}">
                    <p class="number-preview-label">{{ __('Certificate Number Preview') }}</p>
                    <p class="number-preview-code" id="previewCode"></p>
                    <p class="number-preview-next" id="previewNext"></p>
                </section>

                <p
                    id="usedNumberNotice"
                    class="number-notice is-warning"
                    role="status"
                    aria-live="polite"
                    @if (! $selectedNumberWasUsed) hidden @endif
                >{{ __('Number :number has already been used in :year. You can still reuse it.', [
                    'number' => str_pad((string) $initialStartNumber, 3, '0', STR_PAD_LEFT),
                    'year' => $selectedYear,
                ]) }}</p>

                <section class="number-reminder">
                    <div class="number-reminder-icon" aria-hidden="true">↻</div>

                    <div>
                        <h2>{{ __('Certificate Number Reminder') }}</h2>
                        <p id="reminderText">
                            @if ($lastNumber === 0)
                                {{ __('No certificates have been recorded for :year yet. The suggested next number is :number.', [
                                    'year' => $selectedYear,
                                    'number' => str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT),
                                ]) }}
                            @else
                                {{ __('The highest number assigned or used in :year is :number. The suggested next number is :next.', [
                                    'year' => $selectedYear,
                                    'number' => str_pad((string) $lastNumber, 3, '0', STR_PAD_LEFT),
                                    'next' => str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT),
                                ]) }}
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
                    <a class="number-button" href="{{ route('recipients.index', ['back' => 1]) }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        {{ __('Back') }}
                    </a>

                    <button
                        class="number-button number-button-primary"
                        id="continueButton"
                        type="submit"
                    >
                        {{ __('Continue') }}
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

    @php
        $numberUsedMessage = __('Number :number has already been used in :year. You can still reuse it.');
        $invalidNumberMessage = __('Enter a valid starting number and year');
        $sameNumberMessage = __('This number will be used for every recipient.');
    @endphp

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
        const suggestedNumbersByYear = @json($suggestedNumbersByYear);
        const usedNumbersByYear = @json($usedNumbersByYear);
        const usedNumberMessage = {{ \Illuminate\Support\Js::from($numberUsedMessage) }};
        const invalidNumberMessage = {{ \Illuminate\Support\Js::from($invalidNumberMessage) }};
        const sameNumberMessage = {{ \Illuminate\Support\Js::from($sameNumberMessage) }};

        const previewCode = document.getElementById('previewCode');
        const previewNext = document.getElementById('previewNext');
        const notice = document.getElementById('numberNotice');
        const usedNumberNotice = document.getElementById('usedNumberNotice');

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
            updateUsedNumberNotice();

            if (!isValidInput()) {
                previewCode.textContent = invalidNumberMessage;
                previewNext.textContent = '';
                return;
            }

            const number = Number(startNumberInput.value);

            previewCode.textContent = buildCertificateNumber(number);
            previewNext.textContent = sameNumberMessage;
        }

        function updateUsedNumberNotice() {
            const number = Number(startNumberInput.value);
            const usedNumbers = usedNumbersByYear[yearInput.value] ?? [];
            const hasBeenUsed = usedNumbers.includes(number);

            usedNumberNotice.hidden = !hasBeenUsed;

            if (hasBeenUsed) {
                usedNumberNotice.textContent = usedNumberMessage
                    .replace(':number', formatNumber(number))
                    .replace(':year', yearInput.value);
            }
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
            const lastNumber = Number(suggestedNumbersByYear[yearInput.value] ?? 0);
            startNumberInput.value = String(Math.min(lastNumber + 1, 4294967295));
            updatePreview();
        });

        updatePreview();
    </script>
</body>
</html>
