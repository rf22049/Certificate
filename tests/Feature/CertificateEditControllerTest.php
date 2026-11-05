<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateEditControllerTest extends TestCase
{
    public function test_editor_shows_the_add_text_box_control_without_the_excel_button(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('certificate-templates/template.png', 'template');

        $this->withSession([
            'template' => [
                'source' => 'upload',
                'path' => 'certificate-templates/template.png',
                'mime' => 'image/png',
            ],
            'recipients' => [['name' => 'Ayu Lestari', 'note' => '']],
            'certificate_number_settings' => [
                'year' => 2025,
                'month' => 7,
                'start_number' => 1,
            ],
        ]);

        $response = $this->get(route('certificate-edit.index'));

        $response->assertOk();
        $response->assertSee('Tambah Kotak Teks');
        $response->assertSee('textBoxContent');
        $response->assertSee('textBoxTabs');
        $response->assertSee('hingga 50 kotak teks');
        $response->assertSee('<input type="checkbox" id="syncTextBoxes">', false);
        $response->assertDontSee('Template Excel');
        $response->assertSee('logo ProCert.png');
        $response->assertSee('procert_logo_transparent.png');
    }

    public function test_custom_text_and_style_are_saved_to_the_session(): void
    {
        $response = $this->postJson(
            route('certificate-edit.save'),
            $this->validPayload('Teks tambahan untuk sertifikat'),
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertSessionHas(
            'certificate_edit_settings',
            fn (array $settings): bool => $settings['syncTextBoxes'] === false
                && count($settings['individual'][0]['textBoxes']) === 2
                && $settings['individual'][0]['textBoxes'][0]['content']
                    === 'Teks tambahan untuk sertifikat'
                && $settings['individual'][0]['textBoxes'][0]['x'] === 25
                && $settings['individual'][0]['textBoxes'][1]['content'] === 'Teks kedua'
                && $settings['global']['textBoxes'] === [],
        );
    }

    public function test_custom_text_can_be_saved_independently_for_each_recipient(): void
    {
        $payload = $this->validPayload('Teks Ayu');
        $secondRecipientTextBox = $payload['settings']['individual'][0]['textBoxes'][0];
        $secondRecipientTextBox['content'] = 'Teks Bima';
        $secondRecipientTextBox['x'] = 75;
        $payload['recipients'][] = ['name' => 'Bima Putra', 'note' => ''];
        $payload['settings']['individual'][1] = [
            'textBoxes' => [$secondRecipientTextBox],
        ];

        $response = $this->postJson(route('certificate-edit.save'), $payload);

        $response->assertOk();
        $response->assertSessionHas(
            'certificate_edit_settings',
            fn (array $settings): bool => $settings['syncTextBoxes'] === false
                && $settings['individual'][0]['textBoxes'][0]['content'] === 'Teks Ayu'
                && $settings['individual'][1]['textBoxes'][0]['content'] === 'Teks Bima'
                && $settings['individual'][1]['textBoxes'][0]['x'] === 75,
        );
    }

    public function test_custom_text_can_be_synchronized_for_all_recipients_when_selected(): void
    {
        $payload = $this->validPayload('Teks tersinkron');
        $payload['settings']['syncTextBoxes'] = true;
        $payload['settings']['global']['textBoxes'] =
            $payload['settings']['individual'][0]['textBoxes'];

        $response = $this->postJson(route('certificate-edit.save'), $payload);

        $response->assertOk();
        $response->assertSessionHas(
            'certificate_edit_settings',
            fn (array $settings): bool => $settings['syncTextBoxes'] === true
                && $settings['global']['textBoxes'][0]['content'] === 'Teks tersinkron',
        );
    }

    public function test_custom_text_longer_than_one_thousand_characters_is_rejected(): void
    {
        $response = $this->postJson(
            route('certificate-edit.save'),
            $this->validPayload(str_repeat('a', 1001)),
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('settings.individual.0.textBoxes.0.content');
        $response->assertSessionMissing('certificate_edit_settings');
    }

    public function test_mismatched_individual_settings_return_a_specific_explanation(): void
    {
        $payload = $this->validPayload('Teks tambahan');
        $payload['settings']['individual'] = [
            3 => ['name' => $payload['settings']['global']['name']],
        ];

        $response = $this->postJson(route('certificate-edit.save'), $payload);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'Pengaturan untuk penerima nomor 4 tidak cocok dengan daftar penerima. Muat ulang halaman lalu coba lagi.',
        ]);
        $response->assertSessionMissing('certificate_edit_settings');
    }

    private function validPayload(string $customText): array
    {
        $style = [
            'font_family' => 'Manrope',
            'font_size' => 24,
            'color' => '#4042A4',
            'alignment' => 'center',
            'bold' => false,
            'italic' => false,
            'underline' => false,
            'x' => 25,
            'y' => 80,
        ];

        return [
            'recipients' => [['name' => 'Ayu Lestari', 'note' => '']],
            'settings' => [
                'syncTextBoxes' => false,
                'global' => [
                    'name' => $style,
                    'number' => array_merge($style, ['font_size' => 14, 'y' => 70]),
                    'textBoxes' => [],
                ],
                'individual' => [
                    0 => [
                        'textBoxes' => [
                        array_merge($style, [
                            'id' => 'text-first',
                            'content' => $customText,
                        ]),
                        array_merge($style, [
                            'id' => 'text-second',
                            'content' => 'Teks kedua',
                            'x' => 75,
                        ]),
                    ],
                    ],
                ],
            ],
        ];
    }
}
