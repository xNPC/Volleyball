<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Dashboard;
use Tests\TestCase;

class TermsTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_document_is_seeded(): void
    {
        $document = LegalDocument::findBySlug(LegalDocument::TERMS);

        $this->assertNotNull($document);
        $this->assertNotEmpty($document->content);
    }

    public function test_terms_page_renders(): void
    {
        $response = $this->get(route('legal.terms'));

        $response->assertOk();
        $response->assertSee('Пользовательское соглашение');
    }

    public function test_terms_link_present_in_footer(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('legal.terms'), false);
    }

    public function test_terms_screen_opens_in_admin(): void
    {
        $admin = User::factory()->create([
            'personal_data_consent_at' => now(),
            'permissions' => app(Dashboard::class)->getAllowAllPermission(),
        ]);

        $this->actingAs($admin)
            ->get(route('platform.legal.edit', LegalDocument::TERMS))
            ->assertOk();
    }

    public function test_consent_screen_opens_with_slug(): void
    {
        $admin = User::factory()->create([
            'personal_data_consent_at' => now(),
            'permissions' => app(Dashboard::class)->getAllowAllPermission(),
        ]);

        $this->actingAs($admin)
            ->get(route('platform.legal.edit', LegalDocument::CONSENT))
            ->assertOk();
    }

    public function test_unknown_legal_slug_returns_404(): void
    {
        $admin = User::factory()->create([
            'personal_data_consent_at' => now(),
            'permissions' => app(Dashboard::class)->getAllowAllPermission(),
        ]);

        $this->actingAs($admin)
            ->get(route('platform.legal.edit', 'unknown'))
            ->assertNotFound();
    }
}
