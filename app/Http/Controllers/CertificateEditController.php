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
        // TEMPLATE
        $template = $request->session()->get('template');

        if (
            ! is_array($template)
            || empty($template['path'])
            || ! Storage::disk('local')->exists($template['path'])
        ) {
            return redirect()->route('home')
                ->with('status', 'Silakan unggah template sertifikat terlebih dahulu.');
        }

        // DATA PENERIMA
        $recipients = $request->session()->get('recipients', []);

        if (empty($recipients)) {
            return redirect()->route('recipients.index')
                ->withErrors([
                    'recipients' => 'Tambahkan minimal satu penerima terlebih dahulu.'
                ]);
        }

        // PENGATURAN NOMOR
        $numberSettings = $request->session()->get('certificate_number_settings');

        if (! is_array($numberSettings)) {
            return redirect()->route('certificate-number.index')
                ->withErrors([
                    'certificate_number' => 'Silakan atur nomor sertifikat terlebih dahulu.'
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

        // PERIKSA PENGATURAN INDIVIDUAL
        foreach ($settings['individual'] as $index => $elements) {
            if (! is_array($elements)) {
                unset($settings['individual'][$index]);
                continue;
            }

            foreach ($elements as $element => $style) {
                if (! in_array($element, ['name', 'number'], true) || ! is_array($style)) {
                    unset($settings['individual'][$index][$element]);
                    continue;
                }

                $default = $element === 'name'
                    ? $defaultNameStyle
                    : $defaultNumberStyle;

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

        return view('certificate.step4', [
            'template' => $template,
            'recipients' => $recipients,
            'numberSettings' => $numberSettings,
            'editSettings' => $settings,
        ]);
    }

    public function save(Request $request): JsonResponse|RedirectResponse
    {
        // VALIDASI DATA PENERIMA DAN STRUKTUR PENGATURAN
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
            if (
                ! is_array($elements)
                || ! isset($validated['recipients'][$index])
            ) {
                abort(422, 'Pengaturan sertifikat tidak valid.');
            }

            foreach ($elements as $element => $style) {
                if (
                    ! in_array($element, ['name', 'number'], true)
                    || ! is_array($style)
                ) {
                    abort(422, 'Elemen sertifikat tidak valid.');
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
                'message' => 'Nama penerima tidak boleh kosong.',
            ], 422);
        }

        // SIMPAN KE SESSION, BUKAN DATABASE
        $request->session()->put('recipients', $recipients);

        $request->session()->put('certificate_edit_settings', [
            'global' => $validated['settings']['global'],
            'individual' => $individual,
        ]);

        // RESPONS UNTUK JAVASCRIPT FETCH
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Perubahan sertifikat berhasil disimpan.',
            ]);
        }

        return redirect()->route('certificate-edit.index')
            ->with('status', 'Perubahan sertifikat berhasil disimpan.');
    }
}