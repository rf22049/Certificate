<?php

namespace App\Http\Controllers;

use App\Services\CertificatePdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

class CertificateExportController extends Controller
{
    public function pdf(Request $request, CertificatePdfGenerator $generator): StreamedResponse
    {
        $export = $this->exportData($request);
        $content = $generator->generateAll(
            $export['template'],
            $export['recipients'],
            $export['numberSettings'],
            $export['editSettings'],
        );
        $this->recordNumberUsage($export['numberSettings'], count($export['recipients']),);
        $startNumber = (int) $numberSettings['start_number'];
        $recipientCount = count($recipients);

        $maximumNumber = 4294967295;

        abort_unless(
            $startNumber + $recipientCount - 1 <= $maximumNumber,
            422,
            __('The certificate number range exceeds the allowed limit.'),
        );

        return response()->streamDownload(
            static function () use ($content): void {
                echo $content;
            },
            'certificates.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    public function zip(Request $request, CertificatePdfGenerator $generator): BinaryFileResponse
    {
        $export = $this->exportData($request);
        $archivePath = tempnam(sys_get_temp_dir(), 'procert-export-');

        if ($archivePath === false) {
            throw new RuntimeException(__('Unable to create the certificate archive.'));
        }

        try {
            $archive = new ZipArchive;
            $result = $archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($result !== true) {
                throw new RuntimeException(
                    __('Unable to open the certificate archive.')
                );
            }

            $usedDirectoryNames = [];

            foreach ($export['recipients'] as $index => $recipient) {
                $content = $generator->generateOne(
                    $export['template'],
                    $recipient,
                    $index,
                    $export['numberSettings'],
                    $export['editSettings'],
                );
                $recipientName = preg_replace(
                    '/[\/\\\\:*?"<>|\x00-\x1F]/u',
                    '-',
                    $recipient['name'],
                );
                $directoryName = trim(mb_substr($recipientName ?? '', 0, 80), ' .-_');
                $directoryName = $directoryName !== '' ? $directoryName : 'Recipient';
                $directoryKey = $directoryName;
                $suffix = 2;

                while (isset($usedDirectoryNames[mb_strtolower($directoryKey)])) {
                    $directoryKey = $directoryName.' ('.$suffix.')';
                    $suffix++;
                }

                $usedDirectoryNames[mb_strtolower($directoryKey)] = true;

                if (! $archive->addEmptyDir($directoryKey) || ! $archive->addFromString($directoryKey.'/certificate.pdf', $content)) {
                    throw new RuntimeException(
                        __('Unable to add a certificate to the archive.')
                    );
                }
            }

            if (! $archive->close()) {
                throw new RuntimeException(
                    __('Unable to save the certificate archive.')
                );
            }

            $this->recordNumberUsage($export['numberSettings']);
        } catch (Throwable $exception) {
            if (is_file($archivePath)) {
                unlink($archivePath);
            }

            throw $exception;
        }

        return response()
            ->download($archivePath, 'certificates.zip', ['Cache-Control' => 'private, no-store'])
            ->deleteFileAfterSend(true);
    }

    /**
     * @return array{
     *     template: array{path: string, mime: string},
     *     recipients: list<array{name: string, note?: string}>,
     *     numberSettings: array{year: int, month: int, start_number: int},
     *     editSettings: array{global?: array, individual?: array}
     * }
     */
    private function exportData(Request $request): array
    {
        $template = $request->session()->get('template');
        $recipients = $request->session()->get('recipients', []);
        $numberSettings = $request->session()->get('certificate_number_settings');

        abort_unless(
            is_file($templatePath),
            404,
            __('Template file not found.'),
        );

        $templatePath = ($template['source'] ?? 'upload') === 'builtin'
            ? public_path($template['path'])
            : Storage::disk('local')->path($template['path']);

        $mime = mime_content_type($templatePath);
        abort_unless(
            in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true),
            422,
            __('The template format is not supported for export.'),
        );

        abort_unless(
            is_array($recipients)
                && array_is_list($recipients)
                && count($recipients) > 0
                && count($recipients) <= 1000
                && collect($recipients)->every(
                    fn (mixed $recipient): bool => is_array($recipient)
                        && is_string($recipient['name'] ?? null)
                        && trim($recipient['name']) !== '',
                ),
            422,
            __('Recipient data is invalid.'),
        );

        abort_unless(
            is_array($numberSettings)
                && is_numeric($numberSettings['year'] ?? null)
                && (int) $numberSettings['year'] >= 2000
                && (int) $numberSettings['year'] <= 2100
                && is_numeric($numberSettings['month'] ?? null)
                && (int) $numberSettings['month'] >= 1
                && (int) $numberSettings['month'] <= 12
                && is_numeric($numberSettings['start_number'] ?? null)
                && (int) $numberSettings['start_number'] >= 1
                && (int) $numberSettings['start_number'] <= 4294967295,
            422,
            __('Certificate number settings are invalid.'),
        );

        $editSettings = $request->session()->get('certificate_edit_settings', []);

        return [
            'template' => [
                'path' => $templatePath,
                'mime' => $mime,
            ],
            'recipients' => $recipients,
            'numberSettings' => [
                'year' => (int) $numberSettings['year'],
                'month' => (int) $numberSettings['month'],
                'start_number' => (int) $numberSettings['start_number'],
            ],
            'editSettings' => is_array($editSettings) ? $editSettings : [],
        ];
    }

    /**
     * Record every certificate number used in the exported batch.
     *
     * @param array{year: int, start_number: int} $numberSettings
     */
    private function recordNumberUsage(
        array $numberSettings,
        int $recipientCount,
    ): void {
        $year = (int) $numberSettings['year'];
        $startNumber = (int) $numberSettings['start_number'];
        $endNumber = $startNumber + $recipientCount - 1;

        DB::transaction(function () use (
            $year,
            $startNumber,
            $endNumber,
        ): void {
            $usedAt = now();

            $usages = [];

            for ($number = $startNumber; $number <= $endNumber; $number++) {
                $usages[] = [
                    'year' => $year,
                    'number' => $number,
                    'used_at' => $usedAt,
                ];
            }

            DB::table('certificate_number_usages')->insertOrIgnore(
                $usages,
            );

            DB::table('certificate_number_sequences')
                ->where('year', $year)
                ->update([
                    'last_number' => DB::raw(
                        'MAX(last_number, '.$endNumber.')'
                    ),
                    'updated_at' => $usedAt,
                ]);
        });
    }
}
