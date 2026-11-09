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
        if ($request->boolean('back')) {
            $request->session()->flash('previous_wizard_step', 4);
        }

        $template = $request->session()->get('template');

        if (! is_array($template) || empty($template['path'])) {
            return redirect()
                ->route('home')
                ->with('status', __('Please upload the certificate template first.'));
        }

        $recipients = $request->session()->get('recipients', []);

        if (empty($recipients)) {
            return redirect()
                ->route('recipients.index')
                ->withErrors([
                    'recipients' => __('Add at least one recipient first.'),
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

        $lastNumber = CertificateNumberSequence::query()
            ->where('year', $selectedYear)
            ->value('last_number') ?? 0;
        $highestUsedNumber = DB::table('certificate_number_usages')
            ->where('year', $selectedYear)
            ->max('number') ?? 0;
        $lastNumber = max((int) $lastNumber, (int) $highestUsedNumber);
        $savedStartNumber = $numberSettings['start_number'] ?? null;
        $nextNumber = (int) ($numberSettings['year'] ?? 0) === $selectedYear
            && is_numeric($savedStartNumber)
            && (int) $savedStartNumber >= 1
            ? (int) $savedStartNumber
            : min($lastNumber + 1, 4294967295);

        $suggestedNumbersByYear = CertificateNumberSequence::query()
            ->pluck('last_number', 'year')
            ->mapWithKeys(fn (int $number, int $year): array => [$year => $number])
            ->all();

        $usedNumbersByYear = DB::table('certificate_number_usages')
            ->get(['year', 'number'])
            ->groupBy('year')
            ->map(fn ($usages): array => $usages
                ->pluck('number')
                ->map(fn ($number): int => (int) $number)
                ->all())
            ->all();

        foreach (DB::table('certificate_number_usages')
            ->selectRaw('year, MAX(number) as last_number')
            ->groupBy('year')
            ->get() as $usage) {
            $suggestedNumbersByYear[$usage->year] = max(
                (int) ($suggestedNumbersByYear[$usage->year] ?? 0),
                (int) $usage->last_number,
            );
        }

        $initialStartNumber = (int) $request->session()->getOldInput('start_number', $nextNumber);
        $selectedNumberWasUsed = in_array(
            $initialStartNumber,
            $usedNumbersByYear[$selectedYear] ?? [],
            true,
        );

        return view('certificate.step3', [
            'selectedYear' => $selectedYear,
            'lastNumber' => $lastNumber,
            'nextNumber' => $nextNumber,
            'maximumStartNumber' => 4294967295,
            'selectedMonth' => $selectedMonth,
            'suggestedNumbersByYear' => $suggestedNumbersByYear,
            'usedNumbersByYear' => $usedNumbersByYear,
            'selectedNumberWasUsed' => $selectedNumberWasUsed,
            'initialStartNumber' => $initialStartNumber,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $template = $request->session()->get('template');
        $recipients = $request->session()->get('recipients', []);

        if (! is_array($template) || empty($template['path'])) {
            return redirect()
                ->route('home')
                ->with('status', __('Please upload the certificate template first.'));
        }

        if (empty($recipients)) {
            return redirect()
                ->route('recipients.index')
                ->withErrors([
                    'recipients' => __('Add at least one recipient first.'),
                ]);
        }

        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'start_number' => [
                'required',
                'integer',
                'min:1',
                'max:4294967295',
            ],
        ]);

        $selectedYear = (int) $validated['year'];
        $startNumber = (int) $validated['start_number'];

        DB::transaction(function () use ($selectedYear, $startNumber): void {
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

            $sequence->last_number = max($sequence->last_number, $startNumber);
            $sequence->save();
        });

        $request->session()->put('certificate_number_settings', [
            'year' => $selectedYear,
            'month' => (int) $validated['month'],
            'start_number' => $startNumber,
        ]);

        $request->session()->flash('previous_wizard_step', 3);

        return redirect()
            ->route('certificate-edit.index')
            ->with('status', __('Certificate number settings saved successfully.'));
    }
}
