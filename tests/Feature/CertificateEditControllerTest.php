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
        $response->assertSee('customTextInput');
        $response->assertDontSee('Template Excel');
        $response->assertSee('logo ProCert (1).jpeg');
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
            fn (array $settings): bool => $settings['global']['customText']['content']
                === 'Teks tambahan untuk sertifikat'
                && $settings['global']['customText']['x'] === 25,
        );
    }

    public function test_custom_text_longer_than_one_thousand_characters_is_rejected(): void
    {
        $response = $this->postJson(
            route('certificate-edit.save'),
            $this->validPayload(str_repeat('a', 1001)),
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('settings.global.customText.content');
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
                'global' => [
                    'name' => $style,
                    'number' => array_merge($style, ['font_size' => 14, 'y' => 70]),
                    'customText' => array_merge($style, ['content' => $customText]),
                ],
                'individual' => [],
            ],
        ];
    }
}
