<?php

namespace App\Http\Controllers;

use App\Models\CertificateNumberSequence;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CertificateNumberController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $template = $request->session()->get('template');

        if (! is_array($template) || empty($template['path'])) {
            return redirect()
                ->route('home')
                ->with('status', 'Silakan unggah template sertifikat terlebih dahulu.');
        }

        $recipients = $request->session()->get('recipients', []);

        if (empty($recipients)) {
            return redirect()
                ->route('recipients.index')
                ->withErrors([
                    'recipients' => 'Tambahkan minimal satu nama penerima terlebih dahulu.',
                ]);
        }

        // Ambil tahun berjalan dan nomor terakhir yang tercatat.
        $selectedYear = (int) now()->year;

        $sequence = CertificateNumberSequence::query()
            ->where('year', $selectedYear)
            ->first();

        $lastNumber = $sequence?->last_number ?? 0;
        $nextNumber = $lastNumber + 1;
        $numberSettings = $request->session()->get('certificate_number_settings', []);

        return view('certificate.step3', [
            'selectedYear' => $selectedYear,
            'lastNumber' => $lastNumber,
            'nextNumber' => $nextNumber,
            'selectedMonth' => $numberSettings['month'] ?? (int) now()->month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $template = $request->session()->get('template');
        $recipients = $request->session()->get('recipients', []);

        if (! is_array($template) || empty($template['path'])) {
            return redirect()
                ->route('home')
                ->with('status', 'Silakan unggah template sertifikat terlebih dahulu.');
        }

        if (empty($recipients)) {
            return redirect()
                ->route('recipients.index')
                ->withErrors([
                    'recipients' => 'Tambahkan minimal satu nama penerima terlebih dahulu.',
                ]);
        }

        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        $selectedYear = (int) now()->year;

        $sequence = CertificateNumberSequence::query()
            ->where('year', $selectedYear)
            ->first();

        $nextNumber = ($sequence?->last_number ?? 0) + 1;

        $request->session()->put('certificate_number_settings', [
            'year' => $selectedYear,
            'month' => (int) $validated['month'],
            'start_number' => $nextNumber,
        ]);

        return redirect()
            ->route('certificate-edit.index')
            ->with('status', 'Pengaturan nomor sertifikat berhasil disimpan.');
    }
}
