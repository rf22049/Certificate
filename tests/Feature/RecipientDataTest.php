<?php

namespace tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
            ->assertSee('Email', false)
            ->assertDontSee('Template Excel')
            ->assertSee('Kembali');

        $this->get(route('recipients.index', ['mode' => 'import']))
            ->assertOk()
            ->assertSee('Drag &amp; Drop File Here', false)
            ->assertSee('Email', false)
            ->assertSee('disabled', false)
            ->assertSee('Pratinjau data');
    }

    public function test_downloadable_excel_template_has_email_column(): void
    {
        $spreadsheet = IOFactory::load(
            public_path('images/templates/template_sertif.xlsx')
        );

        $worksheet = $spreadsheet->getActiveSheet();

        $this->assertSame(['No', 'Nama', 'Email', 'Ket'], [
            $worksheet->getCell('A1')->getValue(),
            $worksheet->getCell('B1')->getValue(),
            $worksheet->getCell('C1')->getValue(),
            $worksheet->getCell('D1')->getValue(),
        ]);

        foreach (range(2, 5) as $row) {
            $this->assertSame(
                null,
                $worksheet->getCell('B'.$row)->getValue()
            );

            $this->assertSame(
                null,
                $worksheet->getCell('C'.$row)->getValue()
            );

            $this->assertSame(
                null,
                $worksheet->getCell('D'.$row)->getValue()
            );
        }

        $spreadsheet->disconnectWorksheets();
    }

    public function test_returning_to_the_recipient_step_animates_the_stepper_backwards(): void
    {
        $this->withTemplate();

        $response = $this->get(
            route('recipients.index', ['back' => 1])
        );

        $response->assertOk();
        $response->assertSee('animate-regression', false);
        $response->assertSee('just-reversed', false);
    }

    public function test_xlsx_import_reads_recipients_and_optional_emails_into_the_session(): void
    {
        $this->withTemplate();

        $path = tempnam(sys_get_temp_dir(), 'procert-xlsx-');

        $spreadsheet = new Spreadsheet;

        $spreadsheet->getActiveSheet()->fromArray([
            ['Nama Penerima', 'Email', 'Ket'],
            ['Ayu Lestari', 'ayu@gmail.com', 'Peserta'],
            ['Bima Putra', null, 'Panitia'],
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

            $response = $this->post(
                route('recipients.import'),
                ['recipients_file' => $upload]
            );
        } finally {
            @unlink($path);
        }

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'import'])
        );

        $response->assertSessionHas('recipients', [
            [
                'name' => 'Ayu Lestari',
                'email' => 'ayu@gmail.com',
                'note' => 'Peserta',
            ],
            [
                'name' => 'Bima Putra',
                'email' => '',
                'note' => 'Panitia',
            ],
        ]);

        $response->assertSessionHas('recipient_data_source', 'import');
    }

    public function test_xlsx_import_uses_the_recipient_column_after_a_row_number_column(): void
    {
        $this->withTemplate();

        $path = tempnam(sys_get_temp_dir(), 'procert-xlsx-');

        $spreadsheet = new Spreadsheet;

        $spreadsheet->getActiveSheet()->fromArray([
            ['No', 'Nama', 'Email', 'Ket'],
            [1, 'Ayu Lestari', 'ayu@gmail.com', 'Peserta'],
            [2, 'Bima Putra', 'bima@outlook.com', 'Panitia'],
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

            $response = $this->post(
                route('recipients.import'),
                ['recipients_file' => $upload]
            );
        } finally {
            @unlink($path);
        }

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'import'])
        );

        $response->assertSessionHas('recipients', [
            [
                'name' => 'Ayu Lestari',
                'email' => 'ayu@gmail.com',
                'note' => 'Peserta',
            ],
            [
                'name' => 'Bima Putra',
                'email' => 'bima@outlook.com',
                'note' => 'Panitia',
            ],
        ]);
    }

    public function test_xlsx_import_detects_custom_name_and_email_headers(): void
    {
        $this->withTemplate();

        $path = tempnam(sys_get_temp_dir(), 'procert-xlsx-');

        $spreadsheet = new Spreadsheet;

        $spreadsheet->getActiveSheet()->fromArray([
            ['No', 'Nama Lengkap Peserta', 'Email Address', 'Keterangan'],
            [1, 'Sofia', 'sofia@gmail.com', 'Peserta'],
            [2, 'Ayu Lestari', 'ayu@outlook.com', 'Panitia'],
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

            $response = $this->post(
                route('recipients.import'),
                ['recipients_file' => $upload]
            );
        } finally {
            @unlink($path);
        }

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'import'])
        );

        $response->assertSessionHas('recipients', [
            [
                'name' => 'Sofia',
                'email' => 'sofia@gmail.com',
                'note' => 'Peserta',
            ],
            [
                'name' => 'Ayu Lestari',
                'email' => 'ayu@outlook.com',
                'note' => 'Panitia',
            ],
        ]);
    }

    public function test_xlsx_import_can_read_email_from_a_different_column_position(): void
    {
        $this->withTemplate();

        $path = tempnam(sys_get_temp_dir(), 'procert-xlsx-');

        $spreadsheet = new Spreadsheet;

        $spreadsheet->getActiveSheet()->fromArray([
            ['Email Address', 'Keterangan', 'Nama Lengkap'],
            ['sofia@gmail.com', 'Peserta', 'Sofia'],
            ['ayu@outlook.com', 'Panitia', 'Ayu Lestari'],
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

            $response = $this->post(
                route('recipients.import'),
                ['recipients_file' => $upload]
            );
        } finally {
            @unlink($path);
        }

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'import'])
        );

        $response->assertSessionHas('recipients', [
            [
                'name' => 'Sofia',
                'email' => 'sofia@gmail.com',
                'note' => 'Peserta',
            ],
            [
                'name' => 'Ayu Lestari',
                'email' => 'ayu@outlook.com',
                'note' => 'Panitia',
            ],
        ]);
    }

    public function test_xlsx_import_skips_ellipsis_placeholders_without_counting_them_as_recipients(): void
    {
        $this->withTemplate();

        $path = tempnam(sys_get_temp_dir(), 'procert-xlsx-');

        $spreadsheet = new Spreadsheet;

        $spreadsheet->getActiveSheet()->fromArray([
            ['No', 'Nama', 'Email', 'Ket'],
            [1, 'Ayu Lestari', 'ayu@gmail.com', 'Peserta'],
            [2, 'Bima Putra', 'bima@gmail.com', 'Panitia'],
            [3, 'Citra Dewi', null, 'Peserta'],
            [4, 'Dimas Pratama', null, 'Panitia'],
            [5, '…', null, null],
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

            $response = $this->post(
                route('recipients.import'),
                ['recipients_file' => $upload]
            );
        } finally {
            @unlink($path);
        }

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'import'])
        );

        $response->assertSessionHas('recipients', [
            [
                'name' => 'Ayu Lestari',
                'email' => 'ayu@gmail.com',
                'note' => 'Peserta',
            ],
            [
                'name' => 'Bima Putra',
                'email' => 'bima@gmail.com',
                'note' => 'Panitia',
            ],
            [
                'name' => 'Citra Dewi',
                'email' => '',
                'note' => 'Peserta',
            ],
            [
                'name' => 'Dimas Pratama',
                'email' => '',
                'note' => 'Panitia',
            ],
        ]);

        $response = $this->get(
            route('recipients.index', ['mode' => 'import'])
        );

        $response->assertOk();
        $response->assertSee('Pratinjau data <span>(4)</span>', false);
        $response->assertDontSee('disabled', false);
    }

    public function test_manual_recipients_are_trimmed_and_saved_with_optional_emails(): void
    {
        $this->withTemplate();

        $response = $this->post(route('recipients.store'), [
            'recipients' => [
                [
                    'name' => '  Ayu Lestari  ',
                    'email' => '  ayu@gmail.com  ',
                    'note' => '  Peserta  ',
                ],
                [
                    'name' => '',
                    'email' => '',
                    'note' => '',
                ],
                [
                    'name' => 'Bima Putra',
                    'email' => '',
                    'note' => 'Panitia',
                ],
            ],
        ]);

        $response->assertRedirect(
            route('certificate-number.index')
        );

        $response->assertSessionHas('recipients', [
            [
                'name' => 'Ayu Lestari',
                'email' => 'ayu@gmail.com',
                'note' => 'Peserta',
            ],
            [
                'name' => 'Bima Putra',
                'email' => '',
                'note' => 'Panitia',
            ],
        ]);
    }

    public function test_manual_submission_rejects_invalid_email_addresses(): void
    {
        $this->withTemplate();

        $response = $this->from(
            route('recipients.index', ['mode' => 'manual'])
        )->post(route('recipients.store'), [
            'recipients' => [
                [
                    'name' => 'Ayu Lestari',
                    'email' => 'not-an-email',
                    'note' => 'Peserta',
                ],
            ],
        ]);

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'manual'])
        );

        $response->assertSessionHasErrors('recipients.0.email');
    }

    public function test_xlsx_import_rejects_invalid_email_addresses(): void
    {
        $this->withTemplate();

        $path = tempnam(sys_get_temp_dir(), 'procert-xlsx-');

        $spreadsheet = new Spreadsheet;

        $spreadsheet->getActiveSheet()->fromArray([
            ['Nama', 'Email'],
            ['Ayu Lestari', 'invalid-email'],
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

            $response = $this->from(
                route('recipients.index', ['mode' => 'import'])
            )->post(
                route('recipients.import'),
                ['recipients_file' => $upload]
            );
        } finally {
            @unlink($path);
        }

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'import'])
        );

        $response->assertSessionHasErrors('recipients_file');
        $response->assertSessionMissing('recipients');
    }

    public function test_xlsx_import_rejects_more_than_one_thousand_recipients_without_a_header(): void
    {
        $this->withTemplate();

        $path = tempnam(sys_get_temp_dir(), 'procert-xlsx-');

        $spreadsheet = new Spreadsheet;
        $rows = [];

        for ($row = 1; $row <= 1001; $row++) {
            $rows[] = ['Recipient '.$row, ''];
        }

        $spreadsheet->getActiveSheet()->fromArray($rows);

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

            $response = $this->from(
                route('recipients.index', ['mode' => 'import'])
            )->post(
                route('recipients.import'),
                ['recipients_file' => $upload]
            );
        } finally {
            @unlink($path);
        }

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'import'])
        );

        $response->assertSessionHasErrors([
            'recipients_file' => 'File hanya boleh berisi maksimal 1.000 penerima.',
        ]);

        $response->assertSessionMissing('recipients');
    }

    public function test_manual_submission_requires_at_least_one_recipient(): void
    {
        $this->withTemplate();

        $response = $this->from(
            route('recipients.index', ['mode' => 'manual'])
        )->post(
            route('recipients.store'),
            [
                'recipients' => [
                    [
                        'name' => '',
                        'email' => '',
                        'note' => '',
                    ],
                ],
            ]
        );

        $response->assertRedirect(
            route('recipients.index', ['mode' => 'manual'])
        );

        $response->assertSessionHasErrors('recipients');
    }

    public function test_import_mode_can_advance_with_existing_manual_recipients(): void
    {
        $this->withTemplate();

        $this->withSession([
            'template' => [
                'path' => 'certificate-templates/template.png',
                'name' => 'template.png',
            ],
            'recipient_mode' => 'import',
            'recipient_data_source' => 'manual',
            'recipients' => [
                [
                    'name' => 'Ayu Lestari',
                    'email' => '',
                    'note' => '',
                ],
            ],
        ]);

        $response = $this->from(
            route('recipients.index', ['mode' => 'import'])
        )->post(route('recipients.next'));

        $response->assertRedirect(
            route('certificate-number.index')
        );

        $response->assertSessionHasNoErrors();

        $response->assertSessionHas('recipients', [
            [
                'name' => 'Ayu Lestari',
                'email' => '',
                'note' => '',
            ],
        ]);
    }

    private function withTemplate(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put(
            'certificate-templates/template.png',
            'template'
        );

        $this->withSession([
            'template' => [
                'path' => 'certificate-templates/template.png',
                'name' => 'template.png',
            ],
        ]);
    }
}
