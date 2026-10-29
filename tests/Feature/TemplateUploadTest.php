<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemplateUploadTest extends TestCase
{
    public function test_home_shows_the_template_upload_step(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Buat');
        $response->assertSee('Drag &amp; Drop File Here', false);
        $response->assertDontSee('Admin Prodi');
    }

    public function test_supported_template_is_stored_privately_and_previewed_inline(): void
    {
        Storage::fake('local');

        $upload = UploadedFile::fake()->create('certificate.pdf', 250, 'application/pdf');
        $response = $this->post(route('templates.store'), ['template' => $upload]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('template', fn (array $template): bool => $template['name'] === 'certificate.pdf');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Template berhasil diunggah!')
            ->assertSee(route('recipients.index'), false)
            ->assertDontSee('Admin Prodi');

        $path = session('template.path');
        Storage::disk('local')->assertExists($path);

        $preview = $this->get(route('templates.preview'));
        $preview->assertOk();
        $preview->assertHeader('content-disposition', 'inline');
    }

    public function test_unsupported_template_is_rejected(): void
    {
        Storage::fake('local');

        $response = $this->from(route('home'))->post(route('templates.store'), [
            'template' => UploadedFile::fake()->create('notes.txt', 20, 'text/plain'),
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('template');
        $this->assertSame([], Storage::disk('local')->allFiles('certificate-templates'));
    }

    public function test_template_can_be_removed_from_private_storage(): void
    {
        Storage::fake('local');
        $this->post(route('templates.store'), [
            'template' => UploadedFile::fake()->create('certificate.pdf', 250, 'application/pdf'),
        ]);
        $path = session('template.path');

        $response = $this->delete(route('templates.destroy'));

        $response->assertRedirect(route('home'));
        $response->assertSessionMissing('template');
        Storage::disk('local')->assertMissing($path);
    }

    public function test_invalid_replacement_keeps_the_current_template_and_shows_the_error(): void
    {
        Storage::fake('local');
        $this->post(route('templates.store'), [
            'template' => UploadedFile::fake()->create('certificate.pdf', 250, 'application/pdf'),
        ]);

        $response = $this->from(route('home'))->followingRedirects()->post(route('templates.store'), [
            'template' => UploadedFile::fake()->create('notes.txt', 20, 'text/plain'),
        ]);

        $response->assertOk();
        $response->assertSee('Template berhasil diunggah!');
        $response->assertSee('The template field must be a file of type');
    }
}
