<header class="topbar">
    <a class="brand" href="{{ route('home') }}" aria-label="ProCert, beranda">
        <img class="brand-logo" src="{{ asset('images/procert logo (1).jpeg') }}" alt="ProCert">
    </a>
</header>

<nav class="steps" aria-label="Tahapan pembuatan sertifikat">
    <ol class="steps-list">
        @foreach ([1 => 'Upload Template', 2 => 'Upload Data', 3 => 'Nomor Sertifikat', 4 => 'Edit Sertifikat', 5 => 'Review'] as $stepNumber => $stepLabel)
            @php($stepClass = $stepNumber === $currentStep ? 'is-active' : ($stepNumber < $currentStep ? 'is-complete' : ''))
            <li class="step {{ $stepClass }}" @if ($stepNumber === $currentStep) aria-current="step" @endif>
                <span class="step-number">
                    @if ($stepNumber < $currentStep)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m5 12 4 4L19 6" />
                        </svg>
                    @else
                        {{ $stepNumber }}
                    @endif
                </span>
                <span class="step-label">{{ $stepLabel }}</span>
            </li>
        @endforeach
    </ol>
</nav>