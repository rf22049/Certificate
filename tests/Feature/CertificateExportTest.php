<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\Tfpdf\Fpdi;
use Tests\TestCase;
use ZipArchive;

class CertificateExportTest extends TestCase
{
    public function test_pdf_export_downloads_one_unicode_pdf_page_per_recipient(): void
    {
        $this->withImageTemplate();

        $response = $this->get(route('certificate-export.pdf'));

        $response->assertOk();
        $response->assertDownload('certificates.pdf');

        $content = $response->streamedContent();
        $this->assertStringStartsWith('%PDF-', $content);

        $pdf = new Fpdi('P', 'pt');
        $this->assertSame(2, $pdf->setSourceFile(StreamReader::createByString($content)));
    }

    public function test_zip_export_contains_a_pdf_for_each_recipient(): void
    {
        $this->withImageTemplate();

        $response = $this->get(route('certificate-export.zip'));

        $response->assertOk();
        $response->assertDownload('certificates.zip');

        $archive = new ZipArchive;
        $archivePath = $response->baseResponse->getFile()->getPathname();

        try {
            $this->assertSame(true, $archive->open($archivePath));
            $this->assertSame(2, $archive->numFiles);
            $this->assertStringStartsWith('%PDF-', $archive->getFromName('certificates/001-ayu-lestari.pdf'));
            $this->assertStringStartsWith('%PDF-', $archive->getFromName('certificates/002-bima-ozturk.pdf'));
            $this->assertFalse($archive->locateName('001-ayu-lestari.pdf'));
            $this->assertTrue($archive->close());
        } finally {
            if (is_file($archivePath)) {
                unlink($archivePath);
            }
        }
    }

    public function test_pdf_export_uses_an_uploaded_pdf_as_the_certificate_background(): void
    {
        Storage::fake('local');
        $template = new Fpdi('L', 'pt', [842, 595]);
        $template->AddPage('L', [842, 595]);
        $template->AddFont('DejaVuSans', '', 'DejaVuSans.ttf', true);
        $template->SetFont('DejaVuSans', '', 20);
        $template->Text(100, 100, 'Original PDF template');
        Storage::disk('local')->put(
            'certificate-templates/template.pdf',
            $template->Output('S'),
        );
        $this->withExportSession('certificate-templates/template.pdf', 'application/pdf');

        $response = $this->get(route('certificate-export.pdf'));

        $response->assertOk();
        $response->assertDownload('certificates.pdf');
        $pdf = new Fpdi('P', 'pt');
        $this->assertSame(2, $pdf->setSourceFile(
            StreamReader::createByString($response->streamedContent()),
        ));
    }

    public function test_export_requires_a_selected_template(): void
    {
        $response = $this->get(route('certificate-export.pdf'));

        $response->assertNotFound();
    }

    private function withImageTemplate(): void
    {
        Storage::fake('local');
        $path = UploadedFile::fake()->image('template.png', 1200, 800)
            ->store('certificate-templates', 'local');

        $this->withExportSession($path, 'image/png');
    }

    private function withExportSession(string $templatePath, string $mime): void
    {
        $this->withSession([
            'template' => [
                'source' => 'upload',
                'path' => $templatePath,
                'mime' => $mime,
            ],
            'recipients' => [
                ['name' => 'Ayu Lestari', 'note' => 'Peserta'],
                ['name' => 'Bima Öztürk', 'note' => 'Panitia'],
            ],
            'certificate_number_settings' => [
                'year' => 2025,
                'month' => 7,
                'start_number' => 21,
            ],
            'certificate_edit_settings' => [
                'global' => [
                    'name' => ['x' => 50, 'y' => 45, 'font_size' => 24, 'bold' => true],
                    'number' => ['x' => 50, 'y' => 75, 'font_size' => 14],
                    'customText' => [
                        'content' => 'Teks tambahan untuk sertifikat',
                        'x' => 35,
                        'y' => 82,
                        'font_size' => 20,
                    ],
                ],
                'individual' => [
                    1 => ['name' => ['x' => 60, 'y' => 45]],
                ],
            ],
        ]);
    }
}
