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
            'canAdvanceImport' => $request->session()->get('recipients', []) !== [],
            'pendingRecipients' => $request->session()->get('pending_recipients', []),
            'pendingRecipientFilename' => $request->session()->get('pending_recipient_filename'),
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
                'max:10240',
            ],
        ]);

        $file = $validated['recipients_file'];
        $extension = strtolower($file->getClientOriginalExtension());

        // Check the file extension separately.
        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return back()->withErrors([
                'recipients_file' => __('The file extension must be XLSX, XLS, or CSV.'),
            ]);
        }
          
        try {
            /*
            * Select the reader using the uploaded file extension.
            * This avoids relying entirely on automatic file detection.
            */
            $readerType = match ($extension) {
                'xlsx' => 'Xlsx',
                'xls' => 'Xls',
                'csv' => 'Csv',
                default => null,
            };

            if ($readerType === null) {
                return back()->withErrors([
                    'recipients_file' => __(
                        'The file extension must be XLSX, XLS, or CSV.'
                    ),
                ]);
            }

            $reader = IOFactory::createReader($readerType);

            $reader->setReadDataOnly(true);

            /*
            * Configure CSV encoding and delimiter.
            */
            if (
                $reader instanceof
                \PhpOffice\PhpSpreadsheet\Reader\Csv
            ) {
                $reader->setInputEncoding('UTF-8');

                $reader->setDelimiter(
                    $this->detectCsvDelimiter($file->getRealPath())
                );
            }

            $spreadsheet = $reader->load($file->getRealPath());

        } catch (Throwable $exception) {
            /*
            * Keep the actual technical error in Laravel's log.
            * Do not expose internal exception details to the user.
            */
            report($exception);

            return back()
                ->withErrors([
                    'recipients_file' => __(
                        'The uploaded file could not be processed. Please check the file and try again.'
                    ),
                ])
                ->withInput();
        }

        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestDataRow();
        $lastColumn = $worksheet->getHighestDataColumn();

        $headerCells = $worksheet->rangeToArray(
            'A1:'.$lastColumn.'1',
            null,
            true,
            false
        )[0];

        $nameColumn = null;
        $emailColumn = null;
        $noteColumn = null;

        // Detect columns by keywords instead of exact header names.
        foreach ($headerCells as $index => $headerCell) {
            $header = mb_strtolower(trim((string) $headerCell));
            $normalized = preg_replace('/[^a-z0-9]+/u', '', $header);

            $column = Coordinate::stringFromColumnIndex($index + 1);

            if (
                $nameColumn === null
                && (
                    str_contains($normalized, 'nama')
                    || str_contains($normalized, 'name')
                    || $normalized === 'recipient'
                    || str_contains($normalized, 'recipientname')
                    || str_contains($normalized, 'recipientfullname')
                )
            ) {
                $nameColumn = $column;
            }

            if (
                $emailColumn === null
                && (
                    str_contains($normalized, 'email')
                    || str_contains($normalized, 'mail')
                    || str_contains($normalized, 'outlook')
                )
            ) {
                $emailColumn = $column;
            }

            if (
                $noteColumn === null
                && (
                    str_contains($normalized, 'ket')
                    || str_contains($normalized, 'catatan')
                    || str_contains($normalized, 'note')
                    || str_contains($normalized, 'description')
                )
            ) {
                $noteColumn = $column;
            }
        }

        if ($lastRow > 10000) {
            $spreadsheet->disconnectWorksheets();

            return back()->withErrors([
                'recipients_file' => __('The file has too many rows to process.'),
            ]);
        }

        // A recognized name header means row 1 is the header.
        $hasHeader = $nameColumn !== null;

        $firstDataRow = $hasHeader ? 2 : 1;

        // Preserve compatibility with simple files without headers.
        $nameColumn ??= 'A';

        if (! $hasHeader) {
            $noteColumn ??= 'B';
        }

        $recipients = [];

        for ($row = $firstDataRow; $row <= $lastRow; $row++) {
            $name = $this->cellText($worksheet, $nameColumn.$row);

            $email = $emailColumn !== null
                ? $this->cellText($worksheet, $emailColumn.$row)
                : '';

            $note = $noteColumn !== null
                ? $this->cellText($worksheet, $noteColumn.$row)
                : '';

            // Ignore completely empty rows.
            if ($name === '' && $email === '' && $note === '') {
                continue;
            }

            if ($name === '…' || $name === '...') {
                continue;
            }

            // A name is always required.
            if ($name === '' || mb_strlen($name) > 255) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'recipients_file' => __('A valid recipient name is required in row :row.', [
                        'row' => $row,
                    ]),
                ]);
            }

            // Email is optional, but must be valid when provided.
            if (
                $email !== ''
                && (
                    mb_strlen($email) > 254
                    || filter_var($email, FILTER_VALIDATE_EMAIL) === false
                )
            ) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'recipients_file' => __('Enter a valid email address in row :row.', [
                        'row' => $row,
                    ]),
                ]);
            }

            if (mb_strlen($note) > 255) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'recipients_file' => __('The note in row :row exceeds 255 characters.', [
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

            $recipients[] = [
                'name' => $name,
                'email' => $email,
                'note' => $note,
            ];
        }

        $spreadsheet->disconnectWorksheets();

        if ($recipients === []) {
            return back()->withErrors([
                'recipients_file' => __('No recipient data was found in the file.'),
            ]);
        }

        // Check whether a recipient list already exists.
        $currentRecipients = $request->session()->get('recipients', []);

        if (is_array($currentRecipients) && count($currentRecipients) > 0) {
            /*
            * Do not overwrite the existing recipient list yet.
            * Keep the newly imported data temporarily until
            * the user confirms the replacement.
            */
            $request->session()->put('pending_recipients', $recipients);

            $request->session()->put(
                'pending_recipient_filename',
                $file->getClientOriginalName()
            );

            return redirect()->route('recipients.index', [
                'mode' => 'import',
            ]);
        }

        $request->session()->put('recipients', $recipients);
        $request->session()->put('recipient_mode', 'import');
        $request->session()->put('recipient_data_source', 'import');

        return redirect()
            ->route('recipients.index', ['mode' => 'import'])
            ->with('recipient_toast', __('Recipient data uploaded successfully.'));
    }

    /**
     * Confirm replacement of the current recipient list
     * with the newly imported Excel/CSV data.
     */
    public function confirmImportReplacement(Request $request): RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
        }

        $pendingRecipients = $request->session()->get('pending_recipients');

        if (! is_array($pendingRecipients) || $pendingRecipients === []) {
            return redirect()
                ->route('recipients.index', ['mode' => 'import'])
                ->withErrors([
                    'recipients_file' => __('No pending recipient data was found. Please upload the file again.'),
                ]);
        }

        // Replace the entire active recipient list.
        $request->session()->put('recipients', $pendingRecipients);
        $request->session()->put('recipient_mode', 'import');
        $request->session()->put('recipient_data_source', 'import');

        // Remove temporary replacement data.
        $request->session()->forget([
            'pending_recipients',
            'pending_recipient_filename',
        ]);
        
        return redirect()
            ->route('recipients.index', ['mode' => 'import'])
            ->with('recipient_toast', __('Recipient data replaced successfully.'));

    }

    /**
     * Cancel replacement and preserve the existing recipient list.
     */
    public function cancelImportReplacement(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'pending_recipients',
            'pending_recipient_filename',
        ]);

        return redirect()->route('recipients.index', [
            'mode' => 'import',
        ]);
    }

    /**
     * Save recipient changes without advancing to the next wizard step.
     * Used when switching between manual and Excel modes.
     */
    public function saveDraft(Request $request): RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'recipients' => ['nullable', 'array', 'max:'.self::MAX_RECIPIENTS],
            'recipients.*.name' => ['nullable', 'string', 'max:255'],
            'recipients.*.email' => ['nullable', 'string', 'email:rfc', 'max:254'],
            'recipients.*.note' => ['nullable', 'string', 'max:255'],
            'mode' => ['required', 'in:manual,import'],
        ]);

        $recipients = collect($validated['recipients'] ?? [])
            ->map(fn (array $recipient): array => [
                'name' => trim($recipient['name'] ?? ''),
                'email' => trim($recipient['email'] ?? ''),
                'note' => trim($recipient['note'] ?? ''),
            ])
            ->filter(fn (array $recipient): bool =>
                $recipient['name'] !== ''
                || $recipient['email'] !== ''
                || $recipient['note'] !== ''
            )
            ->values()
            ->all();

        $request->session()->put('recipients', $recipients);
        $request->session()->put('recipient_mode', $validated['mode']);
        $request->session()->put('recipient_data_source', 'shared');

        return redirect()->route('recipients.index', [
            'mode' => $validated['mode'],
        ]);
    }
    
    public function store(Request $request): RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'recipients' => ['required', 'array', 'max:'.self::MAX_RECIPIENTS],
            'recipients.*.name' => ['nullable', 'string', 'max:255'],
            'recipients.*.email' => ['nullable', 'string', 'email:rfc', 'max:254'],
            'recipients.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $recipients = collect($validated['recipients'])
            ->map(fn (array $recipient): array => [
                'name' => trim($recipient['name'] ?? ''),
                'email' => trim($recipient['email'] ?? ''),
                'note' => trim($recipient['note'] ?? ''),
            ])
            ->filter(fn (array $recipient): bool => $recipient['name'] !== '')
            ->values()
            ->all();

        if ($recipients === []) {
            return back()->withErrors([
                'recipients' => __('Add at least one recipient name.'),
            ]);
        }

        $request->session()->put('recipients', $recipients);
        $request->session()->put('recipient_mode', 'manual');
        $request->session()->put('recipient_data_source', 'manual');

        $request->session()->flash('previous_wizard_step', 2);

        return redirect()->route('certificate-number.index')
            ->with('status', __('Recipient data saved successfully.'));
    }

    public function next(Request $request): RedirectResponse
    {
        if (! $this->hasTemplate($request)) {
            return redirect()->route('home');
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

    private function detectCsvDelimiter(string $path): string
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return ',';
        }

        $firstLine = fgets($handle);

        fclose($handle);

        if ($firstLine === false) {
            return ',';
        }

        $delimiters = [',', ';', "\t"];

        $selectedDelimiter = ',';
        $highestCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = substr_count($firstLine, $delimiter);

            if ($count > $highestCount) {
                $highestCount = $count;
                $selectedDelimiter = $delimiter;
            }
        }

        return $selectedDelimiter;
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
