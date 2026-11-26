
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

    @if (($template ?? false) && session('template_toast'))
        <div
            class="procert-notification"
            data-procert-notification
            role="status"
            aria-live="polite"
        >
            <div class="procert-notification-bubble">
                <p>{{ session('template_toast') }}</p>

                <span class="procert-notification-time">Just now</span>
            </div>

            <button
                type="button"
                class="procert-notification-close"
                data-notification-close
                aria-label="{{ __('Close notification') }}"
            >
                &times;
            </button>
        </div>
    @endif

    <div class="topbar-actions">
        @if ($showExcel)
            <a
                class="excel-button"
                href="{{ asset('images/templates/template_sertif.xlsx') }}"
                download
                aria-label="{{ __('Download Excel Template') }}"
                title="{{ __('Download Excel Template') }}"
            >
                <svg
                    class="excel-button-icon"
                    viewBox="0 0 32 32"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M7 3.5H19L26 10.5V27
                        C26 28.1 25.1 29 24 29H7
                        C5.9 29 5 28.1 5 27V5.5
                        C5 4.4 5.9 3.5 7 3.5Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M19 3.5V10.5H26"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M11 15L19 24M19 15L11 24"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    />
                </svg>

                <span>{{ __('Excel Template') }}</span>
            </a>
        @endif

        {{-- Language Selector --}}
        <form
            class="language-picker"
            method="POST"
            action="{{ route('language.update') }}"
        >
            @csrf

            <details class="language-dropdown">
                <summary
                    class="language-dropdown-trigger"
                    aria-label="{{ __('Select language') }}"
                >
                    <span class="language-current">
                        {{ app()->getLocale() === 'id' ? 'Bahasa Indonesia' : 'English' }}
                    </span>

                    <svg
                        class="language-chevron"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="m6 9 6 6 6-6"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </summary>

                <div class="language-dropdown-menu">
                    <button
                        type="submit"
                        name="locale"
                        value="id"
                        class="language-option {{ app()->getLocale() === 'id' ? 'is-active' : '' }}"
                        @if (app()->getLocale() === 'id') aria-current="true" @endif
                    >
                        <span>Bahasa Indonesia</span>

                        @if (app()->getLocale() === 'id')
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path
                                    d="m5 12 4 4L19 6"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        @endif
                    </button>

                    <button
                        type="submit"
                        name="locale"
                        value="en"
                        class="language-option {{ app()->getLocale() === 'en' ? 'is-active' : '' }}"
                        @if (app()->getLocale() === 'en') aria-current="true" @endif
                    >
                        <span>English</span>

                        @if (app()->getLocale() === 'en')
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path
                                    d="m5 12 4 4L19 6"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        @endif
                    </button>
                </div>
            </details>
        </form>


        {{-- Dark Mode Toggle --}}
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
            {{-- Moon icon: switch to Dark Mode --}}
            <svg
                data-theme-icon="moon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M20.2 15.3A8.5 8.5 0 0 1 8.7 3.8 8.6 8.6 0 1 0 20.2 15.3Z"/>
            </svg>

            {{-- Sun icon: switch to Light Mode --}}
            <svg
                data-theme-icon="sun"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
                hidden
            >
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>
            </svg>
        </button>
    </div>
</header>

@php
    $procertTranslations = [
        'uploading' => __('Uploading...'),
        'uploadingFile' => __('Uploading :name...'),

        'recipientName' => __('Recipient name, row :number'),
        'recipientEmail' => __('Recipient email, row :number'),
        'note' => __('Note, row :number'),

        'deleteRow' => __('Delete row'),
        'deleteRowNumber' => __('Delete row :number'),

        'showAll' => __('Show All'),
        'showLess' => __('Show Less'),

        'previewPrefix' => __('Preview'),

        'switchTemplateTip' => __('You can switch to another template from the template library.'),
        'checkTemplateTip' => __('Make sure your certificate design is clear and readable.'),
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
