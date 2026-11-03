<?php

namespace App\Http\Controllers;

use App\Services\CertificatePdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            throw new RuntimeException('Arsip sertifikat tidak dapat dibuat.');
        }

        try {
            $archive = new ZipArchive;
            $result = $archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($result !== true) {
                throw new RuntimeException('Arsip sertifikat tidak dapat dibuka.');
            }

            foreach ($export['recipients'] as $index => $recipient) {
                $content = $generator->generateOne(
                    $export['template'],
                    $recipient,
                    $index,
                    $export['numberSettings'],
                    $export['editSettings'],
                );
                $recipientName = Str::limit(Str::slug($recipient['name']), 80, '');
                $filename = str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)
                    .'-'.($recipientName !== '' ? $recipientName : 'recipient')
                    .'.pdf';

                if (! $archive->addFromString($filename, $content)) {
                    throw new RuntimeException('Sertifikat tidak dapat ditambahkan ke arsip.');
                }
            }

            if (! $archive->close()) {
                throw new RuntimeException('Arsip sertifikat tidak dapat disimpan.');
            }
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
            is_array($template) && isset($template['path']),
            404,
            'Template sertifikat tidak ditemukan.',
        );

        $templatePath = ($template['source'] ?? 'upload') === 'builtin'
            ? public_path($template['path'])
            : Storage::disk('local')->path($template['path']);

        abort_unless(is_file($templatePath), 404, 'File template tidak ditemukan.');

        $mime = mime_content_type($templatePath);
        abort_unless(
            in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true),
            422,
            'Format template tidak didukung untuk ekspor.',
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
            'Data penerima tidak valid.',
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
                && (int) $numberSettings['start_number'] >= 1,
            422,
            'Pengaturan nomor sertifikat tidak valid.',
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
}
