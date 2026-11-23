<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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

        if ($request->boolean('back')) {
            $request->session()->flash('previous_wizard_step', 3);
        }

        $mode = $request->query('mode', $request->session()->get('recipient_mode', 'manual'));
        $mode = in_array($mode, ['manual', 'import'], true) ? $mode : 'manual';
        $request->session()->put('recipient_mode', $mode);

        return view('recipients.index', [
            'mode' => $mode,
            'recipients' => $request->session()->get('recipients', []),
            'canAdvanceImport' => $request->session()->get('recipient_data_source') === 'import'
                && $request->session()->get('recipients', []) !== [],
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
                ->withErrors(['recipients_file' => __('The file could not be read. Make sure its format is XLS, XLSX, or CSV.')]);
        }

        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestDataRow();
        $headerCells = $worksheet->rangeToArray(
            'A1:'.$worksheet->getHighestDataColumn().'1',
            null,
            true,
            false
        )[0];
        $nameColumn = null;
        $noteColumn = null;

        foreach ($headerCells as $index => $headerCell) {
            $header = mb_strtolower(trim((string) $headerCell));
            $column = Coordinate::stringFromColumnIndex($index + 1);

            if (in_array($header, ['nama', 'nama penerima', 'name', 'recipient', 'recipient name'], true)) {
                $nameColumn = $column;
            }

            if (in_array($header, ['ket', 'keterangan', 'catatan', 'note', 'notes', 'description'], true)) {
                $noteColumn = $column;
            }
        }

        if ($lastRow > 10000) {
            $spreadsheet->disconnectWorksheets();

            return back()->withErrors(['recipients_file' => __('The file has too many rows to process.')]);
        }

        $hasHeader = $nameColumn !== null;
        $firstDataRow = $hasHeader ? 2 : 1;
        $nameColumn ??= 'A';
        $noteColumn ??= Coordinate::stringFromColumnIndex(
            Coordinate::columnIndexFromString($nameColumn) + 1
        );
        $recipients = [];

        for ($row = $firstDataRow; $row <= $lastRow; $row++) {
            $name = $this->cellText($worksheet, $nameColumn.$row);
            $note = $this->cellText($worksheet, $noteColumn.$row);

            if ($name === '' && $note === '') {
                continue;
            }

            if ($note === '' && in_array($name, ['…', '...'], true)) {
                continue;
            }

            if ($name === '' || mb_strlen($name) > 255 || mb_strlen($note) > 255) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'recipients_file' => __('Check row :row. A name is required and each column may contain at most 255 characters.', [
                        'row' => $row,
                    ]),
                ]);
            }

            if (count($recipients) >= self::MAX_RECIPIENTS) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'recipients_file' => __('The file may contain at most 1,000 recipients.'),
                ]);
            }

            $recipients[] = ['name' => $name, 'note' => $note];
        }

        $spreadsheet->disconnectWorksheets();

        if ($recipients === []) {
            return back()->withErrors(['recipients_file' => __('No recipient data was found in the file.')]);
        }

        $request->session()->put('recipients', $recipients);
        $request->session()->put('recipient_mode', 'import');
        $request->session()->put('recipient_data_source', 'import');

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
            return back()->withErrors(['recipients' => __('Add at least one recipient name.')]);
        }

        $request->session()->put('recipients', $recipients);
        $request->session()->put('recipient_mode', 'manual');
        $request->session()->put('recipient_data_source', 'manual');

        $request->session()->flash('previous_wizard_step', 2);

        return redirect()->route('certificate-number.index')->with('status', __('Recipient data saved successfully.'));
    }

    public function next(Request $request): RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
        }

        if (
            $request->session()->get('recipient_mode') !== 'import'
            || $request->session()->get('recipient_data_source') !== 'import'
        ) {
            return back()->withErrors([
                'recipients_file' => __('Upload an Excel or CSV file first.'),
            ]);
        }

        if ($request->session()->get('recipients', []) === []) {
            return back()->withErrors([
                'recipients' => __('Add or upload at least one recipient.'),
            ]);
        }

        $request->session()->flash('previous_wizard_step', 2);

        return redirect()->route('certificate-number.index');
    }

    private function hasTemplate(Request $request): bool
    {
        $template = $request->session()->get('template');

        if (! is_array($template) || ! isset($template['path'])) {
            return false;
        }

        if (($template['source'] ?? 'upload') === 'builtin') {
            return is_file(public_path($template['path']));
        }

        return Storage::disk('local')->exists($template['path']);
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
