<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateReviewTest extends TestCase
{
    public function test_review_page_renders_only_one_recipient_pagination(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('certificate-templates/template.png', 'template');

        $this->withSession([
            'template' => [
                'source' => 'upload',
                'path' => 'certificate-templates/template.png',
                'mime' => 'image/png',
            ],
            'recipients' => [
                ['name' => 'Ayu Lestari', 'note' => 'Peserta'],
                ['name' => 'Bima Putra', 'note' => 'Panitia'],
            ],
            'certificate_number_settings' => [
                'year' => 2025,
                'month' => 7,
                'start_number' => 1,
            ],
        ]);

        $response = $this->get(route('certificate-review.index'));

        $response->assertOk();
        $response->assertSee(route('certificate-export.pdf'), false);
        $response->assertSee(route('certificate-export.zip'), false);
        $this->assertSame(1, substr_count($response->getContent(), 'id="previousPage"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="nextPage"'));
    }
}
