<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CertificateEditController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->boolean('back')) {
            $request->session()->flash('previous_wizard_step', 5);
        }

        // TEMPLATE
        $template = $request->session()->get('template');

        if (! is_array($template) || empty($template['path'])) {
            return redirect()->route('home')
                ->with('status', __('Please select or upload a certificate template first.'));
        }

        // CEK TEMPLATE BAWAAN ATAU TEMPLATE UPLOAD
        if (($template['source'] ?? 'upload') === 'builtin') {
            // Template bawaan berada di folder public
            $templateExists = is_file(public_path($template['path']));
        } else {
            // Template yang diunggah berada di local storage
            $templateExists = Storage::disk('local')->exists($template['path']);
        }

        if (! $templateExists) {
            $request->session()->forget('template');

            return redirect()->route('home')
                ->with('status', __('The template file was not found. Please choose a template again.'));
        }

        // DATA PENERIMA
        $recipients = $request->session()->get('recipients', []);

        if (empty($recipients)) {
            return redirect()->route('recipients.index')
                ->withErrors([
                    'recipients' => __('Add at least one recipient first.'),
                ]);
        }

        // PENGATURAN NOMOR
        $numberSettings = $request->session()->get('certificate_number_settings');

        if (! is_array($numberSettings)) {
            return redirect()->route('certificate-number.index')
                ->withErrors([
                    'certificate_number' => __('Set the certificate number first.'),
                ]);
        }

        // DEFAULT STYLE
        $defaultNameStyle = [
            'font_family' => 'Manrope',
            'font_size' => 24,
            'color' => '#4042A4',
            'alignment' => 'center',
            'bold' => true,
            'italic' => false,
            'underline' => false,
            'x' => 50,
            'y' => 50,
        ];

        $defaultNumberStyle = array_merge($defaultNameStyle, [
            'font_size' => 14,
            'bold' => false,
            'y' => 70,
        ]);
        $defaultTextBoxStyle = array_merge($defaultNameStyle, [
            'bold' => false,
            'y' => 80,
        ]);

        // AMBIL PENGATURAN DARI SESSION
        $settings = $request->session()->get('certificate_edit_settings', []);

        if (! is_array($settings)) {
            $settings = [];
        }

        $settings['global'] = is_array($settings['global'] ?? null)
            ? $settings['global']
            : [];

        $settings['individual'] = is_array($settings['individual'] ?? null)
            ? $settings['individual']
            : [];

        // GABUNGKAN DEFAULT DENGAN PENGATURAN YANG TERSIMPAN
        $settings['global']['name'] = array_merge(
            $defaultNameStyle,
            is_array($settings['global']['name'] ?? null)
                ? $settings['global']['name']
                : []
        );

        $settings['global']['number'] = array_merge(
            $defaultNumberStyle,
            is_array($settings['global']['number'] ?? null)
                ? $settings['global']['number']
                : []
        );

        // PERBAIKI ALIGNMENT LAMA YANG TIDAK VALID
        $allowedAlignments = ['left', 'center', 'right', 'justify'];

        foreach (['name', 'number'] as $element) {
            if (! in_array(
                $settings['global'][$element]['alignment'],
                $allowedAlignments,
                true
            )) {
                $settings['global'][$element]['alignment'] = 'center';
            }
        }

        $textBoxes = $settings['global']['textBoxes'] ?? [];

        if (! is_array($textBoxes) || ! array_is_list($textBoxes)) {
            $textBoxes = [];
        }

        if (
            $textBoxes === []
            && is_array($settings['global']['customText'] ?? null)
            && is_string($settings['global']['customText']['content'] ?? null)
        ) {
            $textBoxes[] = [
                ...$settings['global']['customText'],
                'id' => 'text-legacy',
            ];
        }

        $settings['global']['textBoxes'] = $this->normalizeTextBoxes(
            $textBoxes,
            $defaultTextBoxStyle,
            $allowedAlignments,
        );
        $textBoxes = $settings['global']['textBoxes'];

        unset($settings['global']['customText']);

        // PERIKSA PENGATURAN INDIVIDUAL
        foreach ($settings['individual'] as $index => $elements) {
            if (! is_array($elements)) {
                unset($settings['individual'][$index]);

                continue;
            }

            foreach ($elements as $element => $style) {
                if ($element === 'textBoxes') {
                    if (! is_array($style) || ! array_is_list($style)) {
                        unset($settings['individual'][$index]['textBoxes']);

                        continue;
                    }

                    $settings['individual'][$index]['textBoxes'] = $this->normalizeTextBoxes(
                        $style,
                        $defaultTextBoxStyle,
                        $allowedAlignments,
                    );

                    continue;
                }

                if (! in_array($element, ['name', 'number'], true) || ! is_array($style)) {
                    unset($settings['individual'][$index][$element]);

                    continue;
                }

                $default = match ($element) {
                    'name' => $defaultNameStyle,
                    'number' => $defaultNumberStyle,
                };

                $settings['individual'][$index][$element] = array_merge(
                    $default,
                    $style
                );

                if (! in_array(
                    $settings['individual'][$index][$element]['alignment'],
                    $allowedAlignments,
                    true
                )) {
                    $settings['individual'][$index][$element]['alignment'] = 'center';
                }
            }
        }

        $settings['syncTextBoxes'] = ($settings['syncTextBoxes'] ?? false) === true;

        if (! $settings['syncTextBoxes']) {
            foreach (array_keys($recipients) as $index) {
                $settings['individual'][$index]['textBoxes'] ??= $this->normalizeTextBoxes(
                    $textBoxes,
                    $defaultTextBoxStyle,
                    $allowedAlignments,
                );
            }
        }

        return view('certificate.step4', [
            'template' => $template,
            'recipients' => $recipients,
            'numberSettings' => $numberSettings,
            'editSettings' => $settings,
        ]);
    }

    public function save(Request $request): JsonResponse|RedirectResponse
    {
        // ATURAN VALIDASI STYLE
        $styleRules = [
            'font_family' => ['required', 'string', 'max:100'],
            'font_size' => ['required', 'numeric', 'between:8,150'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'alignment' => ['required', 'in:left,center,right,justify'],
            'bold' => ['required', 'boolean'],
            'italic' => ['required', 'boolean'],
            'underline' => ['required', 'boolean'],
            'x' => ['required', 'numeric', 'between:0,100'],
            'y' => ['required', 'numeric', 'between:0,100'],
        ];

        // VALIDASI DATA PENERIMA DAN STRUKTUR PENGATURAN
        $validationRules = [
            'recipients' => ['required', 'array', 'min:1', 'max:1000'],
            'recipients.*.name' => ['required', 'string', 'max:255'],
            'recipients.*.note' => ['nullable', 'string', 'max:255'],

            'settings' => ['required', 'array:global,individual,syncTextBoxes'],
            'settings.syncTextBoxes' => ['required', 'boolean'],
            'settings.global' => ['required', 'array:name,number,textBoxes'],
            'settings.global.name' => ['required', 'array'],
            'settings.global.number' => ['required', 'array'],
            'settings.global.textBoxes' => ['present', 'array', 'list', 'max:50'],
            'settings.global.textBoxes.*' => [
                'required',
                'array:id,content,font_family,font_size,color,alignment,bold,italic,underline,x,y',
            ],
            'settings.global.textBoxes.*.id' => [
                'required',
                'string',
                'max:80',
                'regex:/^text-[a-zA-Z0-9-]+$/',
                'distinct',
            ],
            'settings.global.textBoxes.*.content' => ['present', 'string', 'max:1000'],
            'settings.individual' => ['nullable', 'array'],
            'settings.individual.*' => ['array:name,number,textBoxes'],
            'settings.individual.*.name' => [
                'sometimes',
                'array:font_family,font_size,color,alignment,bold,italic,underline,x,y',
            ],
            'settings.individual.*.number' => [
                'sometimes',
                'array:font_family,font_size,color,alignment,bold,italic,underline,x,y',
            ],
            'settings.individual.*.textBoxes' => ['sometimes', 'array', 'list', 'max:50'],
            'settings.individual.*.textBoxes.*' => [
                'required',
                'array:id,content,font_family,font_size,color,alignment,bold,italic,underline,x,y',
            ],
            'settings.individual.*.textBoxes.*.id' => [
                'required',
                'string',
                'max:80',
                'regex:/^text-[a-zA-Z0-9-]+$/',
            ],
            'settings.individual.*.textBoxes.*.content' => ['present', 'string', 'max:1000'],
        ];

        foreach ($styleRules as $field => $rules) {
            $validationRules["settings.global.textBoxes.*.{$field}"] = [
                'required',
                ...array_slice($rules, 1),
            ];
            $validationRules["settings.individual.*.name.{$field}"] = [
                'sometimes',
                ...array_slice($rules, 1),
            ];
            $validationRules["settings.individual.*.textBoxes.*.{$field}"] = [
                'required',
                ...array_slice($rules, 1),
            ];
            $validationRules["settings.individual.*.number.{$field}"] = [
                'sometimes',
                ...array_slice($rules, 1),
            ];
        }

        $validated = $request->validate($validationRules);

        // VALIDASI STYLE GLOBAL
        foreach (['name', 'number'] as $element) {
            foreach ($styleRules as $field => $rules) {
                $request->validate([
                    "settings.global.{$element}.{$field}" => $rules,
                ]);
            }
        }

        // VALIDASI STYLE INDIVIDUAL
        $individual = $validated['settings']['individual'] ?? [];

        foreach ($individual as $index => $elements) {
            if (! isset($validated['recipients'][$index])) {
                return response()->json([
                    'message' => __('Certificate settings are invalid.'),
                    'errors' => [
                        "settings.individual.{$index}" => [
                            __('Settings for recipient :number do not match the recipient list. Reload the page and try again.', [
                                'number' => (int) $index + 1,
                            ]),
                        ],
                    ],
                ], 422);
            }

            $textBoxIds = array_column($elements['textBoxes'] ?? [], 'id');

            if (count($textBoxIds) !== count(array_unique($textBoxIds))) {
                return response()->json([
                    'message' => __('Certificate settings are invalid.'),
                    'errors' => [
                        "settings.individual.{$index}.textBoxes" => [
                            __('Each text box on a certificate must have a unique ID.'),
                        ],
                    ],
                ], 422);
            }

            foreach ($elements as $element => $style) {
                if ($element === 'textBoxes') {
                    foreach ($style as $textBoxIndex => $textBox) {
                        foreach ($styleRules as $field => $rules) {
                            $request->validate([
                                "settings.individual.{$index}.textBoxes.{$textBoxIndex}.{$field}" => [
                                    'required',
                                    ...array_slice($rules, 1),
                                ],
                            ]);
                        }
                    }

                    continue;
                }

                foreach ($styleRules as $field => $rules) {
                    $request->validate([
                        "settings.individual.{$index}.{$element}.{$field}" => $rules,
                    ]);
                }
            }
        }

        // BERSIHKAN DATA PENERIMA
        $recipients = collect($validated['recipients'])
            ->map(fn (array $recipient): array => [
                'name' => trim($recipient['name']),
                'note' => trim($recipient['note'] ?? ''),
            ])
            ->values()
            ->all();

        if (collect($recipients)->contains(
            fn ($recipient) => $recipient['name'] === ''
        )) {
            return response()->json([
                'message' => __('Recipient names cannot be empty.'),
            ], 422);
        }

        // SIMPAN KE SESSION, BUKAN DATABASE
        $request->session()->put('recipients', $recipients);

        $syncTextBoxes = (bool) $validated['settings']['syncTextBoxes'];
        $individual = $validated['settings']['individual'] ?? [];

        if (! $syncTextBoxes) {
            foreach ($recipients as $index => $recipient) {
                $individual[$index]['textBoxes'] ??= [];
            }
        }

        $request->session()->put('certificate_edit_settings', [
            'global' => array_merge($validated['settings']['global'], [
                'textBoxes' => $syncTextBoxes
                    ? $validated['settings']['global']['textBoxes']
                    : [],
            ]),
            'syncTextBoxes' => $syncTextBoxes,
            'individual' => $individual,
        ]);

        $request->session()->flash('previous_wizard_step', 4);

        // RESPONS UNTUK JAVASCRIPT FETCH
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Certificate changes saved successfully.'),
            ]);
        }

        return redirect()->route('certificate-edit.index')
            ->with('status', __('Certificate changes saved successfully.'));
    }

    /**
     * @param  list<mixed>  $textBoxes
     * @param  array<string, mixed>  $defaultStyle
     * @param  list<string>  $allowedAlignments
     * @return list<array<string, mixed>>
     */
    private function normalizeTextBoxes(
        array $textBoxes,
        array $defaultStyle,
        array $allowedAlignments,
    ): array {
        $normalized = [];

        foreach (array_slice($textBoxes, 0, 50) as $index => $textBox) {
            if (
                ! is_array($textBox)
                || ! is_string($textBox['content'] ?? null)
                || ! is_string($textBox['id'] ?? null)
            ) {
                continue;
            }

            $textBox = array_merge($defaultStyle, $textBox);
            $textBox['alignment'] = in_array($textBox['alignment'], $allowedAlignments, true)
                ? $textBox['alignment']
                : 'center';
            $textBox['id'] = mb_substr($textBox['id'], 0, 80);

            if ($textBox['id'] === '') {
                $textBox['id'] = 'text-'.($index + 1);
            }

            $normalized[] = $textBox;
        }

        return $normalized;
    }
}
