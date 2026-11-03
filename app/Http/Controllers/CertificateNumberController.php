<?php

namespace App\Http\Controllers;

use App\Models\CertificateNumberSequence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $numberSettings = $request->session()->get('certificate_number_settings', []);
        $numberSettings = is_array($numberSettings) ? $numberSettings : [];
        $selectedYear = (int) ($numberSettings['year'] ?? now()->year);

        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = (int) now()->year;
        }

        $selectedMonth = (int) ($numberSettings['month'] ?? now()->month);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) now()->month;
        }

        $sequence = CertificateNumberSequence::query()
            ->where('year', $selectedYear)
            ->first();

        $lastNumber = $sequence?->last_number ?? 0;
        $hasCurrentReservation = ($numberSettings['year'] ?? null) === $selectedYear
            && ($numberSettings['reservation_count'] ?? null) === count($recipients)
            && is_int($numberSettings['start_number'] ?? null);
        $nextNumber = $hasCurrentReservation
            ? $numberSettings['start_number']
            : $lastNumber + 1;

        if ($hasCurrentReservation) {
            $lastNumber = $nextNumber - 1;
        }

        $lastNumbersByYear = CertificateNumberSequence::query()
            ->pluck('last_number', 'year')
            ->all();

        if ($hasCurrentReservation) {
            $lastNumbersByYear[$selectedYear] = $nextNumber - 1;
        }

        return view('certificate.step3', [
            'selectedYear' => $selectedYear,
            'lastNumber' => $lastNumber,
            'nextNumber' => $nextNumber,
            'selectedMonth' => $selectedMonth,
            'lastNumbersByYear' => $lastNumbersByYear,
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
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);

        $selectedYear = (int) $validated['year'];

        $recipientCount = count($recipients);
        $previousSettings = $request->session()->get('certificate_number_settings', []);
        $isSameReservation = is_array($previousSettings)
            && ($previousSettings['year'] ?? null) === $selectedYear
            && ($previousSettings['month'] ?? null) === (int) $validated['month']
            && ($previousSettings['reservation_count'] ?? null) === $recipientCount
            && is_int($previousSettings['start_number'] ?? null);

        if ($isSameReservation) {
            $nextNumber = $previousSettings['start_number'];
        } else {
            $nextNumber = DB::transaction(function () use ($selectedYear, $recipientCount): int {
                CertificateNumberSequence::query()->insertOrIgnore([
                    'year' => $selectedYear,
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = CertificateNumberSequence::query()
                    ->where('year', $selectedYear)
                    ->lockForUpdate()
                    ->firstOrFail();

                $nextNumber = $sequence->last_number + 1;
                $lastReservedNumber = $nextNumber + $recipientCount - 1;

                if ($lastReservedNumber > 4294967295) {
                    abort(422, 'Nomor sertifikat tahunan telah mencapai batas maksimum.');
                }

                $sequence->last_number = $lastReservedNumber;
                $sequence->save();

                return $nextNumber;
            });
        }

        $request->session()->put('certificate_number_settings', [
            'year' => $selectedYear,
            'month' => (int) $validated['month'],
            'start_number' => $nextNumber,
            'reservation_count' => $recipientCount,
        ]);

        return redirect()
            ->route('certificate-edit.index')
            ->with('status', 'Pengaturan nomor sertifikat berhasil disimpan.');
    }
}
