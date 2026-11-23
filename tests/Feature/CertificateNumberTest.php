<?php

namespace Tests\Feature;

use App\Models\CertificateNumberSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        $response->assertSee('name="start_number"', false);
        $response->assertDontSee('name="start_number" readonly', false);
        $response->assertSee('name="year"', false);
        $response->assertSee('value="2025"', false);
    }

    public function test_returning_from_the_editor_animates_the_stepper_backwards(): void
    {
        $this->withSession([
            'template' => ['path' => 'certificate-templates/template.png'],
            'recipients' => [['name' => 'Ayu Lestari', 'note' => '']],
        ]);

        $response = $this->get(route('certificate-number.index', ['back' => 1]));

        $response->assertOk();
        $response->assertSee('animate-regression', false);
        $response->assertSee('just-regressed', false);
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
            'start_number' => 8,
        ]);

        $response->assertRedirect(route('certificate-edit.index'));
        $response->assertSessionHas('certificate_number_settings', [
            'year' => 2025,
            'month' => 7,
            'start_number' => 8,
        ]);
        $this->assertDatabaseHas('certificate_number_sequences', [
            'year' => 2025,
            'last_number' => 8,
        ]);
    }

    public function test_revisiting_the_same_number_settings_does_not_advance_the_sequence(): void
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

        $this->post(route('certificate-number.store'), ['month' => 7, 'year' => 2025, 'start_number' => 8])
            ->assertRedirect(route('certificate-edit.index'));

        $this->post(route('certificate-number.store'), ['month' => 7, 'year' => 2025, 'start_number' => 8])
            ->assertRedirect(route('certificate-edit.index'))
            ->assertSessionHas('certificate_number_settings', [
                'year' => 2025,
                'month' => 7,
                'start_number' => 8,
            ]);

        $this->assertDatabaseHas('certificate_number_sequences', [
            'year' => 2025,
            'last_number' => 8,
        ]);
    }

    public function test_a_new_number_suggestion_follows_the_highest_assigned_number(): void
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
            'start_number' => 10,
        ]);

        $response->assertRedirect(route('certificate-edit.index'));
        $response->assertSessionHas('certificate_number_settings', [
            'year' => 2025,
            'month' => 7,
            'start_number' => 10,
        ]);
        $this->assertDatabaseHas('certificate_number_sequences', [
            'year' => 2025,
            'last_number' => 10,
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
                'start_number' => 1,
            ]);

        $response->assertRedirect(route('certificate-number.index'));
        $response->assertSessionHasErrors('year');
        $response->assertSessionMissing('certificate_number_settings');
    }

    public function test_user_selected_start_number_is_assigned_without_reserving_a_range(): void
    {
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
            'start_number' => 25,
        ]);

        $response->assertRedirect(route('certificate-edit.index'));
        $response->assertSessionHas('certificate_number_settings', [
            'year' => 2025,
            'month' => 7,
            'start_number' => 25,
        ]);
        $this->assertDatabaseHas('certificate_number_sequences', [
            'year' => 2025,
            'last_number' => 25,
        ]);
    }

    public function test_user_can_reuse_a_number_lower_than_the_highest_assigned_number(): void
    {
        CertificateNumberSequence::query()->create([
            'year' => 2025,
            'last_number' => 40,
        ]);
        DB::table('certificate_number_usages')->insert([
            'year' => 2025,
            'number' => 25,
            'used_at' => now(),
        ]);
        $this->withSession([
            'template' => ['path' => 'certificate-templates/template.png'],
            'recipients' => [['name' => 'Ayu Lestari', 'note' => '']],
        ]);

        $response = $this->post(route('certificate-number.store'), [
            'month' => 7,
            'year' => 2025,
            'start_number' => 25,
        ]);

        $response->assertRedirect(route('certificate-edit.index'));
        $response->assertSessionHas('certificate_number_settings', [
            'year' => 2025,
            'month' => 7,
            'start_number' => 25,
        ]);
        $this->assertDatabaseHas('certificate_number_sequences', [
            'year' => 2025,
            'last_number' => 40,
        ]);
    }

    public function test_certificate_number_page_warns_when_the_selected_number_was_used(): void
    {
        DB::table('certificate_number_usages')->insert([
            'year' => 2025,
            'number' => 25,
            'used_at' => now(),
        ]);
        $this->withSession([
            'template' => ['path' => 'certificate-templates/template.png'],
            'recipients' => [['name' => 'Ayu Lestari', 'note' => '']],
            'certificate_number_settings' => [
                'year' => 2025,
                'month' => 7,
                'start_number' => 25,
            ],
            'locale' => 'id',
        ]);

        $response = $this->get(route('certificate-number.index'));

        $response->assertOk();
        $response->assertSee(
            'Nomor 025 sudah pernah digunakan pada tahun 2025. Anda tetap dapat menggunakannya kembali.',
            false,
        );
        $response->assertSee('min="1"', false);
    }
}
