<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class RecipientController extends Controller
{
    private const MAX_RECIPIENTS = 1000;

    public function index(Request $request): View|RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
        }

        $mode = $request->query('mode', $request->session()->get('recipient_mode', 'manual'));
        $mode = in_array($mode, ['manual', 'import'], true) ? $mode : 'manual';
        $request->session()->put('recipient_mode', $mode);

        return view('recipients.index', [
            'mode' => $mode,
            'recipients' => $request->session()->get('recipients', []),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'recipients_file' => [
                'required',
                'file',
                'mimes:csv,txt,xls,xlsx',
                'extensions:csv,txt,xls,xlsx',
                'max:10240',
            ],
        ]);

        try {
            $reader = IOFactory::createReaderForFile($validated['recipients_file']->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($validated['recipients_file']->getRealPath());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['recipients_file' => 'File tidak dapat dibaca. Pastikan formatnya XLS, XLSX, atau CSV.']);
        }

        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestDataRow();

        if ($lastRow > self::MAX_RECIPIENTS + 1) {
            $spreadsheet->disconnectWorksheets();

            return back()->withErrors(['recipients_file' => 'File hanya boleh berisi maksimal 1.000 penerima.']);
        }

        $firstColumn = mb_strtolower($this->cellText($worksheet, 'A1'));
        $hasHeader = in_array($firstColumn, ['nama', 'nama penerima', 'name', 'recipient'], true);
        $firstDataRow = $hasHeader ? 2 : 1;
        $recipients = [];

        for ($row = $firstDataRow; $row <= $lastRow; $row++) {
            $name = $this->cellText($worksheet, 'A'.$row);
            $note = $this->cellText($worksheet, 'B'.$row);

            if ($name === '' && $note === '') {
                continue;
            }

            if ($name === '' || mb_strlen($name) > 255 || mb_strlen($note) > 255) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'recipients_file' => 'Periksa baris '.$row.'. Nama wajib diisi dan setiap kolom maksimal 255 karakter.',
                ]);
            }

            $recipients[] = ['name' => $name, 'note' => $note];
        }

        $spreadsheet->disconnectWorksheets();

        if ($recipients === []) {
            return back()->withErrors(['recipients_file' => 'Tidak ada data penerima yang ditemukan di file.']);
        }

        $request->session()->put('recipients', $recipients);
        $request->session()->put('recipient_mode', 'import');

        return redirect()->route('recipients.index', ['mode' => 'import']);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'recipients' => ['required', 'array', 'max:'.self::MAX_RECIPIENTS],
            'recipients.*.name' => ['nullable', 'string', 'max:255'],
            'recipients.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $recipients = collect($validated['recipients'])
            ->map(fn (array $recipient): array => [
                'name' => trim($recipient['name'] ?? ''),
                'note' => trim($recipient['note'] ?? ''),
            ])
            ->filter(fn (array $recipient): bool => $recipient['name'] !== '')
            ->values()
            ->all();

        if ($recipients === []) {
            return back()->withErrors(['recipients' => 'Tambahkan minimal satu nama penerima.']);
        }

        $request->session()->put('recipients', $recipients);
        $request->session()->put('recipient_mode', 'manual');

        return redirect()->route('recipients.index', ['mode' => 'manual'])
            ->with('status', 'Data penerima berhasil disimpan.');
    }

    public function next(Request $request): RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
        }

        if ($request->session()->get('recipients', []) === []) {
            return back()->withErrors(['recipients' => 'Tambahkan atau unggah minimal satu penerima.']);
        }

        return back()->with('status', 'Data penerima siap. Langkah berikutnya belum tersedia.');
    }

    private function hasTemplate(Request $request): bool
    {
        $template = $request->session()->get('template');

        return is_array($template)
            && isset($template['path'])
            && Storage::disk('local')->exists($template['path']);
    }

    private function cellText(Worksheet $worksheet, string $coordinate): string
    {
        $value = $worksheet->getCell($coordinate)->getValue();

        if (is_object($value) && method_exists($value, 'getPlainText')) {
            $value = $value->getPlainText();
        }

        return is_scalar($value) ? trim((string) $value) : '';
    }
}
