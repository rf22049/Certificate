
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

                    $animateConnectorForward = $previousStep !== null
                        && $stepNumber === (int) $previousStep
                        && $stepNumber < $currentStep;

                    $animateConnectorBackward = $previousStep !== null
                        && $stepNumber === (int) $currentStep
                        && $currentStep < (int) $previousStep;

                    $animateRegression = $previousStep !== null
                        && $stepNumber === (int) $previousStep
                        && $currentStep < (int) $previousStep;
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
                            {{ $animateConnectorForward ? 'just-completed' : '' }}
                            {{ $animateConnectorBackward ? 'just-reversed' : '' }}"
                        aria-hidden="true"
                    >
                        <span></span>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
