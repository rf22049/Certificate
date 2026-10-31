
<header class="topbar">
    <a class="brand" href="{{ route('home') }}" aria-label="ProCert, beranda">
        <img
            class="brand-logo"
            src="{{ asset('images/procert logo (1).jpeg') }}"
            alt="ProCert"
        >
    </a>
</header>

@php
    $wizardSteps = [
        1 => 'Upload Template',
        2 => 'Upload Data',
        3 => 'Nomor Sertifikat',
        4 => 'Edit Sertifikat',
        5 => 'Review',
    ];

    $previousStep = session('previous_wizard_step');
@endphp

<nav class="steps" aria-label="Tahapan pembuatan sertifikat">
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
            @endphp

            <li
                class="step {{ $stepClass }} {{ $animateCheck ? 'animate-check' : '' }}"
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
                        {{ $animateConnector ? 'just-completed' : '' }}"
                    aria-hidden="true"
                >
                    <span></span>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
