<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class RecipientDataTest extends TestCase
{
    public function test_recipient_step_requires_an_uploaded_template(): void
    {
        $response = $this->get(route('recipients.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_recipient_step_renders_manual_and_import_modes(): void
    {
        $this->withTemplate();

        $this->get(route('recipients.index', ['mode' => 'manual']))
            ->assertOk()
            ->assertSee('Masukkan Data Penerima')
            ->assertSee('Tambah Baris')
            ->assertSee('Kembali');

        $this->get(route('recipients.index', ['mode' => 'import']))
            ->assertOk()
            ->assertSee('Drag &amp; Drop File Here', false)
            ->assertSee('Pratinjau data');
    }

    public function test_xlsx_import_reads_recipients_into_the_session(): void
    {
        $this->withTemplate();
        $path = tempnam(sys_get_temp_dir(), 'procert-xlsx-');
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['Nama Penerima', 'Ket'],
            ['Ayu Lestari', 'Peserta'],
            ['Bima Putra', 'Panitia'],
        ]);
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $upload = new UploadedFile(
                $path,
                'penerima.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true,
            );
            $response = $this->post(route('recipients.import'), ['recipients_file' => $upload]);
        } finally {
            @unlink($path);
        }

        $response->assertRedirect(route('recipients.index', ['mode' => 'import']));
        $response->assertSessionHas('recipients', [
            ['name' => 'Ayu Lestari', 'note' => 'Peserta'],
            ['name' => 'Bima Putra', 'note' => 'Panitia'],
        ]);
    }

    public function test_manual_recipients_are_trimmed_and_saved(): void
    {
        $this->withTemplate();

        $response = $this->post(route('recipients.store'), [
            'recipients' => [
                ['name' => '  Ayu Lestari  ', 'note' => '  Peserta  '],
                ['name' => '', 'note' => ''],
                ['name' => 'Bima Putra', 'note' => 'Panitia'],
            ],
        ]);

        $response->assertRedirect(route('recipients.index', ['mode' => 'manual']));
        $response->assertSessionHas('recipients', [
            ['name' => 'Ayu Lestari', 'note' => 'Peserta'],
            ['name' => 'Bima Putra', 'note' => 'Panitia'],
        ]);
    }

    public function test_manual_submission_requires_at_least_one_recipient(): void
    {
        $this->withTemplate();

        $response = $this->from(route('recipients.index', ['mode' => 'manual']))
            ->post(route('recipients.store'), [
                'recipients' => [['name' => '', 'note' => '']],
            ]);

        $response->assertRedirect(route('recipients.index', ['mode' => 'manual']));
        $response->assertSessionHasErrors('recipients');
    }

    private function withTemplate(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('certificate-templates/template.png', 'template');

        $this->withSession([
            'template' => [
                'path' => 'certificate-templates/template.png',
                'name' => 'template.png',
            ],
        ]);
    }
}
