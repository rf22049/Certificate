<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CertificateEditController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $template = $request->session()->get('template');

        if (
            ! is_array($template)
            || empty($template['path'])
            || ! Storage::disk('local')->exists($template['path'])
        ) {
            return redirect()->route('home')
                ->with('status', 'Silakan unggah template sertifikat terlebih dahulu.');
        }

        $recipients = $request->session()->get('recipients', []);

        if (empty($recipients)) {
            return redirect()->route('recipients.index')
                ->withErrors(['recipients' => 'Tambahkan minimal satu penerima terlebih dahulu.']);
        }

        $numberSettings = $request->session()->get('certificate_number_settings');

        if (! is_array($numberSettings)) {
            return redirect()->route('certificate-number.index')
                ->withErrors(['certificate_number' => 'Silakan atur nomor sertifikat terlebih dahulu.']);
        }

        $defaultStyle = [
            'font_family' => 'Manrope',
            'font_size' => 24,
            'color' => '#4042A4',
            'alignment' => ['required', 'in:left,center,right,justify'],
            'bold' => true,
            'italic' => false,
            'underline' => false,
            'x' => 50,
            'y' => 50,
        ];

        $settings = $request->session()->get('certificate_edit_settings', [
            'global' => [
                'name' => $defaultStyle,
                'number' => array_merge($defaultStyle, [
                    'font_size' => 14,
                    'bold' => false,
                    'y' => 70,
                ]),
            ],
            'individual' => [],
        ]);

        return view('certificate.step4', [
            'template' => $template,
            'recipients' => $recipients,
            'numberSettings' => $numberSettings,
            'editSettings' => $settings,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipients' => ['required', 'array', 'min:1', 'max:1000'],
            'recipients.*.name' => ['required', 'string', 'max:255'],
            'recipients.*.note' => ['nullable', 'string', 'max:255'],

            'settings' => ['required', 'array'],
            'settings.global' => ['required', 'array'],
            'settings.global.name' => ['required', 'array'],
            'settings.global.number' => ['required', 'array'],
            'settings.individual' => ['nullable', 'array'],
        ]);

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

        foreach (['name', 'number'] as $element) {
            foreach ($styleRules as $field => $rules) {
                $request->validate([
                    "settings.global.{$element}.{$field}" => $rules,
                ]);
            }
        }

        $individual = $validated['settings']['individual'] ?? [];

        foreach ($individual as $index => $elements) {
            if (! is_array($elements) || ! isset($validated['recipients'][$index])) {
                abort(422, 'Pengaturan sertifikat tidak valid.');
            }

            foreach ($elements as $element => $style) {
                if (! in_array($element, ['name', 'number'], true) || ! is_array($style)) {
                    abort(422, 'Elemen sertifikat tidak valid.');
                }

                foreach ($styleRules as $field => $rules) {
                    $request->validate([
                        "settings.individual.{$index}.{$element}.{$field}" => $rules,
                    ]);
                }
            }
        }

        $recipients = collect($validated['recipients'])
            ->map(fn (array $recipient): array => [
                'name' => trim($recipient['name']),
                'note' => trim($recipient['note'] ?? ''),
            ])
            ->values()
            ->all();

        if (collect($recipients)->contains(fn ($recipient) => $recipient['name'] === '')) {
            return back()->withErrors([
                'recipients' => 'Nama penerima tidak boleh kosong.',
            ]);
        }

        $request->session()->put('recipients', $recipients);
        $request->session()->put('certificate_edit_settings', [
            'global' => $validated['settings']['global'],
            'individual' => $individual,
        ]);

        return redirect()->route('certificate-edit.index')
            ->with('status', 'Perubahan sertifikat berhasil disimpan.');
    }
}