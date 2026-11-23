<?php

namespace Tests\Feature;

use Tests\TestCase;

class LanguageTest extends TestCase
{
    public function test_language_choice_is_saved_and_translates_the_next_page_request(): void
    {
        $this->from(route('home'))
            ->post(route('language.update'), ['locale' => 'en'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'en');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<html lang="en">', false);
        $response->assertSee('Create <span class="title-accent">Certificate</span>', false);
        $response->assertSee('name="locale"', false);
        $response->assertSee('data-theme-toggle', false);
        $response->assertDontSee('data-theme-label', false);
    }

    public function test_language_choice_rejects_unsupported_locales(): void
    {
        $this->from(route('home'))
            ->post(route('language.update'), ['locale' => 'fr'])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('locale')
            ->assertSessionMissing('locale');
    }
}
