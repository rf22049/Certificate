
@php
    $wizardSteps = [
        1 => __('Upload Template'),
        2 => __('Upload Data'),
        3 => __('Certificate Number'),
        4 => __('Edit Certificate'),
        5 => __('Review'),
    ];

    $previousStep = session('previous_wizard_step');
    $showSteps = $showSteps ?? true;
    $showExcel = $showExcel ?? false;
@endphp

<header class="topbar">
    <a class="brand" href="{{ route('home') }}" aria-label="{{ __('ProCert home') }}">
        <img
            class="brand-logo"
            src="{{ asset('images/procert_logo_transparent.png') }}"
            alt="ProCert"
        >
    </a>

    <div class="topbar-actions">
        @if ($showExcel)
            <a
                class="excel-button"
                href="{{ asset('images/templates/template_sertif.xlsx') }}"
                download
            >
                <span>▤</span>
                {{ __('Excel Template') }}
            </a>
        @endif

        <form class="language-picker" method="POST" action="{{ route('language.update') }}">
            @csrf
            <label class="visually-hidden" for="locale">{{ __('Language') }}</label>
            <select id="locale" name="locale" aria-label="{{ __('Language') }}" onchange="this.form.requestSubmit()">
                <option value="id" @selected(app()->getLocale() === 'id')>Bahasa Indonesia</option>
                <option value="en" @selected(app()->getLocale() === 'en')>English</option>
            </select>
        </form>

        <button
            class="theme-toggle"
            type="button"
            data-theme-toggle
            data-label-dark="{{ __('Switch to dark mode') }}"
            data-label-light="{{ __('Switch to light mode') }}"
            aria-label="{{ __('Switch color theme') }}"
            title="{{ __('Switch color theme') }}"
            aria-pressed="false"
        >
            <svg data-theme-icon="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20.2 15.3A8.5 8.5 0 0 1 8.7 3.8 8.6 8.6 0 1 0 20.2 15.3Z"/>
            </svg>
            <svg data-theme-icon="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>
            </svg>
        </button>
    </div>
</header>

@php
    $procertTranslations = [
        'uploading' => __('Uploading...'),
        'recipientUploading' => __('Uploading :name...'),
        'recipientName' => __('Recipient name, row :number'),
        'note' => __('Note, row :number'),
        'deleteRow' => __('Delete row'),
        'deleteRowNumber' => __('Delete row :number'),
        'showAll' => __('Show All'),
        'showLess' => __('Show Less'),
        'previewPrefix' => __('Preview'),
    ];
@endphp

<script>
    window.procertTranslations = {{ \Illuminate\Support\Js::from($procertTranslations) }};
</script>

@if ($showSteps)
    <nav class="steps" aria-label="{{ __('Certificate creation steps') }}">
        <ol class="steps-list">
            @foreach ($wizardSteps as $stepNumber => $stepLabel)
                @php
                    $isComplete = $stepNumber < $currentStep;
                    $isActive = $stepNumber === $currentStep;

                    $stepClass = $isActive
                        ? 'is-active'
                        : ($isComplete ? 'is-complete' : '');

                    $animateCheck = $isComplete
                        && $previousStep !== null
                        && $stepNumber === (int) $previousStep;

                    $animateConnector = $previousStep !== null
                        && $stepNumber === (int) $previousStep
                        && $stepNumber < $currentStep;
                    $isRegressing = $previousStep !== null
                        && (int) $previousStep > $currentStep;
                    $animateRegression = $isRegressing
                        && $stepNumber === (int) $previousStep;
                    $animateConnectorRegression = $isRegressing
                        && $stepNumber === $currentStep;
                @endphp

                <li
                    class="step {{ $stepClass }} {{ $animateCheck ? 'animate-check' : '' }} {{ $animateRegression ? 'animate-regression' : '' }}"
                    @if ($isActive) aria-current="step" @endif
                >
                    <span class="step-number">
                        @if ($isComplete)
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        @else
                            {{ $stepNumber }}
                        @endif
                    </span>

                    <span class="step-label">{{ $stepLabel }}</span>
                </li>

                @if ($stepNumber < count($wizardSteps))
                    <li
                        class="step-connector
                            {{ $stepNumber < $currentStep ? 'is-complete' : '' }}
                            {{ $animateConnector ? 'just-completed' : '' }}
                            {{ $animateConnectorRegression ? 'just-regressed' : '' }}"
                        aria-hidden="true"
                    >
                        <span></span>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
