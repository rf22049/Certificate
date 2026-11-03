<?php

namespace Tests\Feature;

use App\Models\CertificateNumberSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_number_page_uses_saved_year_and_its_sequence(): void
    {
        CertificateNumberSequence::query()->create([
            'year' => 2025,
            'last_number' => 7,
        ]);
        CertificateNumberSequence::query()->create([
            'year' => 2026,
            'last_number' => 40,
        ]);

        $this->withSession([
            'template' => ['path' => 'certificate-templates/template.png'],
            'recipients' => [['name' => 'Ayu Lestari', 'note' => '']],
            'certificate_number_settings' => ['year' => 2025, 'month' => 7],
        ]);

        $response = $this->get(route('certificate-number.index'));

        $response->assertOk();
        $response->assertSee('value="8"', false);
        $response->assertSee('name="year"', false);
        $response->assertSee('value="2025"', false);
    }

    public function test_selected_year_and_month_are_saved_with_that_years_next_number(): void
    {
        CertificateNumberSequence::query()->create([
            'year' => 2025,
            'last_number' => 7,
        ]);

        $this->withSession([
            'template' => ['path' => 'certificate-templates/template.png'],
            'recipients' => [['name' => 'Ayu Lestari', 'note' => '']],
        ]);

        $response = $this->post(route('certificate-number.store'), [
            'month' => 7,
            'year' => 2025,
        ]);

        $response->assertRedirect(route('certificate-edit.index'));
        $response->assertSessionHas('certificate_number_settings', [
            'year' => 2025,
            'month' => 7,
            'start_number' => 8,
            'reservation_count' => 1,
        ]);
        $this->assertDatabaseHas('certificate_number_sequences', [
            'year' => 2025,
            'last_number' => 8,
        ]);
    }

    public function test_revisiting_the_same_number_settings_does_not_reserve_the_batch_twice(): void
    {
        CertificateNumberSequence::query()->create([
            'year' => 2025,
            'last_number' => 7,
        ]);

        $this->withSession([
            'template' => ['path' => 'certificate-templates/template.png'],
            'recipients' => [
                ['name' => 'Ayu Lestari', 'note' => ''],
                ['name' => 'Bima Putra', 'note' => ''],
            ],
        ]);

        $this->post(route('certificate-number.store'), ['month' => 7, 'year' => 2025])
            ->assertRedirect(route('certificate-edit.index'));

        $this->post(route('certificate-number.store'), ['month' => 7, 'year' => 2025])
            ->assertRedirect(route('certificate-edit.index'))
            ->assertSessionHas('certificate_number_settings', [
                'year' => 2025,
                'month' => 7,
                'start_number' => 8,
                'reservation_count' => 2,
            ]);

        $this->assertDatabaseHas('certificate_number_sequences', [
            'year' => 2025,
            'last_number' => 9,
        ]);
    }

    public function test_a_new_batch_starts_after_the_previously_reserved_numbers(): void
    {
        CertificateNumberSequence::query()->create([
            'year' => 2025,
            'last_number' => 9,
        ]);

        $this->withSession([
            'template' => ['path' => 'certificate-templates/template.png'],
            'recipients' => [
                ['name' => 'Ayu Lestari', 'note' => ''],
                ['name' => 'Bima Putra', 'note' => ''],
            ],
        ]);

        $response = $this->post(route('certificate-number.store'), [
            'month' => 7,
            'year' => 2025,
        ]);

        $response->assertRedirect(route('certificate-edit.index'));
        $response->assertSessionHas('certificate_number_settings', [
            'year' => 2025,
            'month' => 7,
            'start_number' => 10,
            'reservation_count' => 2,
        ]);
        $this->assertDatabaseHas('certificate_number_sequences', [
            'year' => 2025,
            'last_number' => 11,
        ]);
    }

    public function test_certificate_number_year_must_be_within_the_supported_range(): void
    {
        $this->withSession([
            'template' => ['path' => 'certificate-templates/template.png'],
            'recipients' => [['name' => 'Ayu Lestari', 'note' => '']],
        ]);

        $response = $this->from(route('certificate-number.index'))
            ->post(route('certificate-number.store'), [
                'month' => 7,
                'year' => 1900,
            ]);

        $response->assertRedirect(route('certificate-number.index'));
        $response->assertSessionHasErrors('year');
        $response->assertSessionMissing('certificate_number_settings');
    }
}
