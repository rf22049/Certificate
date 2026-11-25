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
    @include('partials.wizard-header', ['currentStep' => 3])
    <main class="number-page number-builder-page">
        <header class="number-heading">
            <h1>{{ __('Certificate Number Settings') }}</h1>
            <p>{{ __('Design your certificate number format according to your needs. Each component can be configured separately and reused.') }}</p>
        </header>

        <form id="certificateNumberForm" method="POST" action="{{ route('certificate-number.store') }}">
            @csrf
            <input type="hidden" name="fields" id="fieldsPayload">
            <input type="hidden" name="separator" id="separatorPayload" value="/">
            <input
                type="hidden"
                name="format_name"
                id="formatNamePayload"
                value="{{ __('Untitled Format') }}"
            >

            <section class="number-builder-layout">
                <div class="number-builder-preview-column">
                    <div class="number-builder-section-heading">
                        <span class="number-builder-kicker">{{ __('Live Preview') }}</span>
                        <span class="number-builder-status" id="previewStatus">
                            {{ __('Not applied') }}
                        </span>
                    </div>

                    <div class="number-builder-preview-canvas">
                        <span class="number-builder-preview-dash" id="previewCode">—</span>
                    </div>

                    <p class="number-builder-preview-hint" id="previewHint">
                        {{ __('Your preview will appear after you click "Use Format".') }}
                    </p>

                    <section class="number-saved-formats">
                        <div class="number-builder-section-heading">
                            <div>
                                <span class="number-builder-kicker">{{ __('Format Library') }}</span>
                                <h2>{{ __('Saved Formats') }}</h2>
                            </div>
                            <span class="number-saved-count" id="savedFormatCount">
                                {{ __(':count formats', ['count' => count($savedFormats)]) }}
                            </span>
                        </div>

                        <label class="number-format-search">
                            <span aria-hidden="true">⌕</span>
                            <input
                                id="savedFormatSearch"
                                type="search"
                                placeholder="{{ __('Search saved formats...') }}"
                                aria-label="{{ __('Search saved formats') }}"
                            >
                        </label>
                        
                        <div class="number-saved-list" id="savedFormatList">
                            @forelse ($savedFormats as $format)
                                <button type="button" class="number-saved-item"
                                    data-format-id="{{ $format->id }}"
                                    data-format-name="{{ $format->name }}"
                                    data-fields='@json($format->fields)'
                                    data-separator="{{ $format->separator }}">
                                    <span class="number-saved-item-icon">▤</span>
                                    <span class="number-saved-item-copy">
                                        <strong>{{ $format->name }}</strong>
                                        <small>
                                            {{ __(':count components · :labels', [
                                                'count' => count($format->fields),
                                                'labels' => implode(' / ', array_column($format->fields, 'label')),
                                            ]) }}
                                        </small>
                                    </span>
                                    <span class="number-saved-item-arrow">→</span>
                                </button>
                            @empty
                                <div class="number-saved-empty" id="savedFormatEmpty">
                                    {{ __('No saved formats yet. Save your first format to reuse it later.') }}
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="number-builder-controls">
                    <div id="formatBuilderStage">
                    <section class="number-builder-card">
                        <div class="number-builder-section-heading">
                            <div>
                                <span class="number-builder-kicker">{{ __('Format Builder') }}</span>
                                <h2>{{ __('Build Number Components') }}</h2>
                            </div>
                            <span class="number-builder-count" id="fieldCount">0/12</span>
                        </div>

                        <p class="number-builder-description">
                            {{ __('Arrange your certificate number components. Each component you add will become an input field.') }}
                        </p>

                        <div class="number-builder-fields" id="builderFields"></div>

                        <button type="button" class="number-add-field" id="addFieldButton">
                            <span>＋</span> {{ __('Add Format Component') }}
                        </button>

                        <div class="number-builder-options">
                            <label for="formatName">{{ __('Format Name') }}</label>
                            <input
                                id="formatName"
                                type="text"
                                maxlength="120"
                                placeholder="{{ __('Example: Department Certificate Format') }}"
                            >

                            <label for="fieldSeparator">{{ __('Component Separator') }}</label>
                            <input
                                id="fieldSeparator"
                                type="text"
                                maxlength="20"
                                value="/"
                                placeholder="/"
                            >

                            <small>
                                {{ __('Separator examples: /, -, |, or a space.') }}
                            </small>
                        </div>
                    </section>

                    <section class="number-builder-card number-builder-action-card">
                        <div class="number-builder-section-heading">
                            <div>
                                <span class="number-builder-kicker">{{ __('Format Actions') }}</span>
                                <h2>{{ __('Save or Apply') }}</h2>
                            </div>
                        </div>

                        <p class="number-builder-description">
                            {{ __('Save your format to reuse it later, or apply it directly to this certificate.') }}
                        </p>

                        <div class="number-builder-actions">
                            <button type="button" class="number-save-format" id="saveFormatButton">
                                <span>♡</span> {{ __('Save Format') }}
                            </button>

                            <button type="button" class="number-use-format" id="useFormatButton">
                                {{ __('Use Format') }} <span>→</span>
                            </button>
                        </div>

                        <p class="number-builder-feedback" id="builderFeedback" role="status" aria-live="polite"></p>
                    </section>
                    </div>

                    <section class="number-builder-card number-builder-fill-card" id="fillFieldsCard" hidden>
                        <div class="number-builder-section-heading">
                            <div>
                                <span class="number-builder-kicker">{{ __('Active Format') }}</span>
                                <h2 id="activeFormatTitle">{{ __('Fill Format Fields') }}</h2>
                            </div>
                            <span class="number-active-indicator">{{ __('Active') }}</span>
                        </div>

                        <p class="number-builder-description">
                            {{ __('Only the components you added to the format are displayed here. Fill in each field according to your certificate requirements.') }}
                        </p>

                        <div class="number-active-fields" id="activeFields"></div>

                        <button type="button" class="number-edit-format" id="editFormatButton">
                            ← {{ __('Edit Format Structure') }}
                        </button>

                        {{-- System values remain hidden. Fields only appear when added through Format Builder. --}}
                        <input type="hidden" id="startNumber" name="start_number" value="{{ old('start_number', $initialStartNumber) }}">
                        <input type="hidden" id="month" name="month" value="{{ old('month', $selectedMonth) }}">
                        <input type="hidden" id="year" name="year" value="{{ old('year', $selectedYear) }}">
                    </section>
                </aside>
            </section>

            <section class="number-reminder">
                <div class="number-reminder-icon" aria-hidden="true">↻</div>

                <div>
                    <h2>{{ __('Certificate Number Reminder') }}</h2>
                    <p>
                        {{ __('The last recorded number for :year is :last. The suggested starting number is :next.', [
                            'year' => $selectedYear,
                            'last' => str_pad((string) $lastNumber, 3, '0', STR_PAD_LEFT),
                            'next' => str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT),
                        ]) }}
                    </p>
                </div>
            </section>

            <div id="numberNotice" class="number-notice" role="status" aria-live="polite"></div>
            <footer class="number-actions">
                <a class="number-button" href="{{ route('recipients.index', ['back' => 1]) }}">
                    ← {{ __('Back') }}
                </a>

                <button class="number-button number-button-primary" id="continueButton" type="submit" disabled>
                    {{ __('Continue') }} →
                </button>
            </footer>
        </form>
    </main>
</div>

@php
    $messages = [
        'freeText' => __('Free Text'),
        'sequentialNumber' => __('Sequential Number'),
        'numericMonth' => __('Numeric Month'),
        'romanMonth' => __('Roman Month'),
        'monthName' => __('Month Name'),
        'year' => __('Year'),

        'removeComponent' => __('Remove component'),
        'columnTitle' => __('Column Title'),
        'columnPlaceholder' => __('Example: Department Code or Document Type'),
        'textContentType' => __('Text Content / Type'),
        'textValuePlaceholder' => __('Example: PP05'),
        'textWillBeContent' => __('This text will be used as the component value when the format is applied.'),
        'automaticNumberValue' => __('This value is generated automatically from certificate number settings.'),

        'column' => __('Column :number'),
        'enterValue' => __('Enter :label'),
        'fillFormatFields' => __('Fill Format Fields'),

        'notApplied' => __('Not applied'),
        'applied' => __('Format Applied'),
        'previewAfterApply' => __('Your preview will appear after you click "Use Format".'),
        'previewAutoUpdate' => __('The preview updates automatically when a field or number setting changes.'),

        'addComponentFirst' => __('Add at least one component and complete all column titles first.'),
        'formatAppliedFeedback' => __('Format applied successfully. Fill in the fields below to update the preview.'),
        'editFormatFeedback' => __('Edit the format structure, then apply it again.'),
        'completeColumnTitles' => __('Complete every column title before saving the format.'),
        'enterFormatName' => __('Enter a format name first.'),
        'saveFormatFailed' => __('Failed to save the format.'),
        'formatSaved' => __('Format saved successfully.'),
        'applyFormatFirst' => __('Apply a format before continuing.'),
        'noSavedFormats' => __('No saved formats yet.'),

        'formatCount' => __(':count formats'),
        'savedFormatDetails' => __(':count components · :labels'),

        'saved' => __('Saved'),
        'saving' => __('Saving...'),
        'untitledFormat' => __('Untitled Format'),
    ];

    $monthNames = [
        1 => __('January'),
        2 => __('February'),
        3 => __('March'),
        4 => __('April'),
        5 => __('May'),
        6 => __('June'),
        7 => __('July'),
        8 => __('August'),
        9 => __('September'),
        10 => __('October'),
        11 => __('November'),
        12 => __('December'),
    ];


@endphp

<script>

(() => {
    const messages = {{ Illuminate\Support\Js::from($messages) }};
    const monthNames = {{ Illuminate\Support\Js::from($monthNames) }};

    const romanMonths = {
        1: 'I', 2: 'II', 3: 'III', 4: 'IV',
        5: 'V', 6: 'VI', 7: 'VII', 8: 'VIII',
        9: 'IX', 10: 'X', 11: 'XI', 12: 'XII'
    };

    const types = [
        ['text', messages.freeText],
        ['number', messages.sequentialNumber],
        ['month', messages.numericMonth],
        ['roman_month', messages.romanMonth],
        ['month_name', messages.monthName],
        ['year', messages.year],
    ];

    const builder = document.getElementById('builderFields');
    const activeFields = document.getElementById('activeFields');
    const fieldsPayload = document.getElementById('fieldsPayload');
    const separatorInput = document.getElementById('fieldSeparator');
    const preview = document.getElementById('previewCode');
    const continueButton = document.getElementById('continueButton');
    const fillCard = document.getElementById('fillFieldsCard');
    const builderStage = document.getElementById('formatBuilderStage');
    const feedback = document.getElementById('builderFeedback');
    const savedList = document.getElementById('savedFormatList');
    let fields = [];
    let applied = false;

    const oldSettings = @json($numberSettings ?? []);
    const savedFormats = @json($savedFormats->values());

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    }

    function makeField(field = {}) {
        return {
            id: field.id || `field-${Math.random().toString(36).slice(2,9)}`,
            label: field.label || '',
            type: field.type || 'text',
            value: field.value || ''
        };
    }

    function fieldTypeOptions(selected) {
        return types.map(([value,label]) => `<option value="${value}" ${value===selected?'selected':''}>${label}</option>`).join('');
    }

    function renderBuilder() {
        builder.innerHTML = '';

        fields.forEach((field, index) => {
            const card = document.createElement('div');
            card.className = 'number-builder-field';

            card.innerHTML = `
                <div class="number-builder-field-top">
                    <span class="number-builder-index">
                        ${String(index + 1).padStart(2, '0')}
                    </span>

                    <button
                        type="button"
                        class="number-remove-field"
                        aria-label="${escapeHtml(messages.removeComponent)}"
                        title="${escapeHtml(messages.removeComponent)}"
                    >×</button>
                </div>

                <label>${escapeHtml(messages.columnTitle)}</label>

                <input
                    class="builder-label"
                    type="text"
                    maxlength="80"
                    placeholder="${escapeHtml(messages.columnPlaceholder)}"
                    value="${escapeHtml(field.label)}"
                >

                <label>${escapeHtml(messages.textContentType)}</label>

                <select class="builder-type">
                    ${fieldTypeOptions(field.type)}
                </select>

                <input
                    class="builder-value"
                    type="text"
                    maxlength="120"
                    placeholder="${escapeHtml(messages.textValuePlaceholder)}"
                    value="${escapeHtml(field.value)}"
                    ${field.type !== 'text' ? 'hidden' : ''}
                >

                <small>
                    ${field.type === 'text'
                        ? escapeHtml(messages.textWillBeContent)
                        : escapeHtml(messages.automaticNumberValue)}
                </small>
            `;

            card.querySelector('.builder-label').addEventListener('input', event => {
                field.label = event.target.value;
            });

            card.querySelector('.builder-type').addEventListener('change', event => {
                field.type = event.target.value;
                renderBuilder();
            });

            card.querySelector('.builder-value').addEventListener('input', event => {
                field.value = event.target.value;
            });

            card.querySelector('.number-remove-field').addEventListener('click', () => {
                fields.splice(index, 1);
                renderBuilder();
            });

            builder.appendChild(card);
        });

        document.getElementById('fieldCount').textContent = `${fields.length}/12`;
        document.getElementById('addFieldButton').disabled = fields.length >= 12;
    }

    function currentValue(field) {
        if (field.type !== 'text') {
            return getAutomaticFieldValue(field);
        }

        const input = activeFields.querySelector(
            `[data-field-id="${field.id}"]`
        );

        return input ? input.value : field.value;
    }

    function renderPreview() {
        if (!applied) { preview.textContent='—'; return; }
        const values = fields.map(currentValue);
        preview.textContent = values.join(separatorInput.value);
    }

    function renderActiveFields() {
        activeFields.innerHTML = '';

        fields.forEach((field, index) => {
            const wrap = document.createElement('div');
            wrap.className = 'number-active-field';

            const auto = field.type !== 'text';

            const automaticValue = field.type === 'number'
                ? String(document.getElementById('startNumber').value || 1).padStart(3, '0')
                : field.type === 'month'
                    ? document.getElementById('month').value || ''
                    : field.type === 'roman_month'
                        ? romanMonths[Number(document.getElementById('month').value)] || ''
                        : field.type === 'month_name'
                            ? monthNames[Number(document.getElementById('month').value)] || ''
                            : field.type === 'year'
                                ? document.getElementById('year').value || ''
                                : field.value;

            const label = field.label || messages.column.replace(':number', index + 1);
            const placeholder = messages.enterValue.replace(':label', label);

            wrap.innerHTML = `
                <label>${escapeHtml(label)}</label>

                <input
                    type="text"
                    data-field-id="${escapeHtml(field.id)}"
                    value="${escapeHtml(automaticValue)}"
                    ${auto ? 'readonly' : ''}
                    placeholder="${escapeHtml(placeholder)}"
                >

                ${auto
                    ? `<small>${escapeHtml(messages.automaticNumberValue)}</small>`
                    : ''}
            `;

            const input = wrap.querySelector('input');
            input.addEventListener('input', renderPreview);

            activeFields.appendChild(wrap);
        });
    }

    function applyFormat() {
        if (!fields.length || fields.some(field => !field.label.trim())) {
            feedback.textContent = messages.addComponentFirst;
            feedback.classList.add('is-error');
            return;
        }

        applied = true;
        builderStage.hidden = true;
        fillCard.hidden = false;

        document.getElementById('activeFormatTitle').textContent =
            document.getElementById('formatName').value.trim()
            || messages.fillFormatFields;

        renderActiveFields();
        continueButton.disabled = false;

        document.getElementById('previewStatus').textContent = messages.applied;

        document.getElementById('previewHint').textContent =
            messages.previewAutoUpdate;

        feedback.textContent = messages.formatAppliedFeedback;
        feedback.classList.remove('is-error');

        syncPayload();
        renderPreview();
    }

    function syncPayload() {
        fields.forEach(field => {
            if (field.type === 'text') field.value = currentValue(field);
        });

        const payload=fields.map(f=>({...f, value: currentValue(f)}));
        fieldsPayload.value=JSON.stringify(payload);
        document.getElementById('separatorPayload').value=separatorInput.value;
        document.getElementById('formatNamePayload').value = document.getElementById('formatName').value.trim() || messages.untitledFormat;
    }

    function renderSavedFormats(items) {
        savedList.innerHTML = '';

        if (!items.length) {
            savedList.innerHTML = `
                <div class="number-saved-empty">
                    ${escapeHtml(messages.noSavedFormats)}
                </div>
            `;

            return;
        }

        items.forEach(format => {
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'number-saved-item';
            button.dataset.formatId = String(format.id);

            button.innerHTML = `
                <span class="number-saved-item-icon">▤</span>

                <span class="number-saved-item-copy">
                    <strong>${escapeHtml(format.name)}</strong>

                    <small>
                        ${escapeHtml(
                            messages.savedFormatDetails
                                .replace(':count', format.fields.length)
                                .replace(
                                    ':labels',
                                    format.fields.map(field => field.label).join(' / ')
                                )
                        )}
                    </small>
                </span>

                <span class="number-saved-item-arrow">→</span>
            `;

            savedList.appendChild(button);
        });
    }

    document.getElementById('addFieldButton').addEventListener('click',()=>{
        if(fields.length>=12)return;
        fields.push(makeField()); renderBuilder();
    });

    document.getElementById('useFormatButton').addEventListener('click',applyFormat);
    document.getElementById('editFormatButton').addEventListener('click', () => {
        applied = false;
        fillCard.hidden = true;
        builderStage.hidden = false;
        continueButton.disabled = true;
        document.getElementById('previewStatus').textContent = messages.notApplied;
        document.getElementById('previewHint').textContent =
            messages.previewAfterApply;
        preview.textContent = '—';
        feedback.textContent = messages.editFormatFeedback;
        feedback.classList.remove('is-error');
    });

    document.getElementById('saveFormatButton').addEventListener('click', async () => {
        if (!fields.length || fields.some(field => !field.label.trim())) {
            feedback.textContent = messages.completeColumnTitles;
            feedback.classList.add('is-error');
            return;
        }

        const name = document.getElementById('formatName').value.trim();

        if (!name) {
            feedback.textContent = messages.enterFormatName;
            feedback.classList.add('is-error');
            return;
        }

        const payload = {
            name,
            separator: separatorInput.value,
            fields: fields.map(field => ({ ...field })),
        };

        const saveButton = document.getElementById('saveFormatButton');
        saveButton.disabled = true;
        feedback.textContent = messages.saving;
        feedback.classList.remove('is-error');

        try {
            const response = await fetch(
                @json(route('certificate-number.formats.store')),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify(payload),
                }
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || messages.saveFormatFailed);
            }

            const item = result.format;
            savedFormats.unshift(item);
            renderSavedFormats(savedFormats);

            document.getElementById('savedFormatCount').textContent =
                messages.formatCount.replace(':count', savedFormats.length);

            feedback.textContent = result.message || messages.formatSaved;
            feedback.classList.remove('is-error');

        } catch (error) {
            feedback.textContent = error.message || messages.saveFormatFailed;
            feedback.classList.add('is-error');

        } finally {
            saveButton.disabled = false;
        }
    });

    document.getElementById('savedFormatSearch').addEventListener('input',e=>{
        const query=e.target.value.toLowerCase();
        [...savedList.children].forEach(item=>item.hidden=!item.textContent.toLowerCase().includes(query));
    });

    savedList.addEventListener('click', event => {
        const button = event.target.closest('.number-saved-item');

        if (!button) {
            return;
        }

        const item = savedFormats.find(
            format => String(format.id) === button.dataset.formatId
        );

        if (!item) {
            return;
        }

        fields = item.fields.map(makeField);

        separatorInput.value = item.separator || '/';

        document.getElementById('formatName').value = item.name;

        renderBuilder();
        applyFormat();
    });

    const numberInputs = [
        separatorInput,
        document.getElementById('startNumber'),
        document.getElementById('month'),
        document.getElementById('year'),
    ];

    function handleNumberSettingChange() {
        if (!applied) {
            return;
        }

        syncPayload();
        renderActiveFields();
        renderPreview();
    }

    numberInputs.forEach(input => {
        input.addEventListener('input', handleNumberSettingChange);
        input.addEventListener('change', handleNumberSettingChange);
    });
    activeFields.addEventListener('input',()=>{if(applied){syncPayload();renderPreview();}});
    
    document.getElementById('certificateNumberForm').addEventListener('submit', event => {
        if (!applied) {
            event.preventDefault();
            feedback.textContent = messages.applyFormatFirst;
            feedback.classList.add('is-error');
            return;
        }

        syncPayload();
    });

    // Start intentionally empty: user must build or load a format first.
    fields=[];
    renderBuilder();
    renderSavedFormats(savedFormats);
    continueButton.disabled=true;
    if (Array.isArray(oldSettings.fields) && oldSettings.fields.length) {
        fields=oldSettings.fields.map(makeField);
        separatorInput.value=oldSettings.separator || '/';
        document.getElementById('formatName').value=oldSettings.format_name || '';
        renderBuilder();
        applyFormat();
    }

    function getAutomaticFieldValue(field) {
        const month = Number(document.getElementById('month').value);

        switch (field.type) {
            case 'number':
                return String(
                    document.getElementById('startNumber').value || 1
                ).padStart(3, '0');

            case 'month':
                return String(month);

            case 'roman_month':
                return romanMonths[month] || '';

            case 'month_name':
                return monthNames[month] || '';

            case 'year':
                return document.getElementById('year').value || '';

            default:
                return field.value || '';
        }
    }
})();
</script>
</body>
</html>
