<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Dashboard;
use Tests\TestCase;

class ConsentTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'personal_data_consent_at' => null,
        ], $attributes));
    }

    public function test_profile_redirects_to_consent_when_not_signed(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('profile.index'));

        $response->assertRedirect(route('legal.consent'));
        $this->assertNull($user->fresh()->personal_data_consent_at);
    }

    public function test_consent_accept_sets_timestamp_and_returns_to_intended(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get(route('profile.index'))
            ->assertRedirect(route('legal.consent'));

        $response = $this->actingAs($user)->post(route('legal.consent.accept'), [
            'personal_data_consent_confirm' => 'on',
        ]);

        $response->assertRedirect(route('profile.index'));
        $this->assertNotNull($user->fresh()->personal_data_consent_at);
        $this->assertNotNull($user->fresh()->personal_data_consent_ip);
        $this->assertNotNull($user->fresh()->personal_data_consent_user_agent);
        $this->assertNotNull($user->fresh()->personal_data_consent_doc_version);

        $this->actingAs($user)
            ->get(route('profile.index'))
            ->assertOk();
    }

    public function test_accept_requires_confirmation_checkbox(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->from(route('legal.consent'))
            ->post(route('legal.consent.accept'), []);

        $response->assertSessionHasErrors('personal_data_consent_confirm');
        $this->assertNull($user->fresh()->personal_data_consent_at);
    }

    public function test_guest_profile_redirects_to_login_not_consent(): void
    {
        $response = $this->get(route('profile.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_public_pages_accessible_without_consent(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->get(route('home'))->assertOk();
        $this->actingAs($user)->get(route('tournaments.index'))->assertOk();
    }

    public function test_user_with_consent_can_access_profile(): void
    {
        $user = $this->createUser(['personal_data_consent_at' => now()]);

        $this->actingAs($user)->get(route('profile.index'))->assertOk();
    }

    public function test_admin_redirects_to_consent_without_signature(): void
    {
        $user = $this->createUser(['permissions' => app(Dashboard::class)->getAllowAllPermission()]);

        $response = $this->actingAs($user)->get('/admin/main');

        $response->assertRedirect(route('legal.consent'));
    }

    public function test_admin_accessible_with_consent(): void
    {
        $user = $this->createUser([
            'permissions' => app(Dashboard::class)->getAllowAllPermission(),
            'personal_data_consent_at' => now(),
        ]);

        $this->actingAs($user)->get('/admin/main')->assertOk();
    }

    public function test_no_redirect_when_document_not_published(): void
    {
        LegalDocument::where('slug', LegalDocument::CONSENT)->delete();
        $user = $this->createUser();

        $this->actingAs($user)->get(route('profile.index'))->assertOk();
    }

    public function test_jetstream_profile_show_redirects_to_consent(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertRedirect(route('legal.consent'));

        $this->actingAs($this->createUser(['personal_data_consent_at' => now()]))
            ->get(route('profile.show'))
            ->assertOk();
    }

    public function test_fortify_profile_information_update_redirects_to_consent(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('user-profile-information.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('legal.consent'));
        $this->assertNotEquals('New Name', $user->fresh()->name);
    }

    public function test_fortify_password_update_redirects_to_consent(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('legal.consent'));
    }

    public function test_login_redirects_to_consent_when_not_signed(): void
    {
        $user = $this->createUser();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('profile.show'));

        $this->get(route('profile.show'))
            ->assertRedirect(route('legal.consent'));
    }

    public function test_logout_works_without_consent(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
