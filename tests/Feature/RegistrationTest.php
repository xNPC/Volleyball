<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_new_users_can_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'personal_data_consent' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profile.show', absolute: false));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->personal_data_consent_at);
        $this->assertNotNull($user->personal_data_consent_ip);
        $this->assertNotNull($user->personal_data_consent_doc_version);
    }

    public function test_registration_requires_personal_data_consent(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('personal_data_consent');
        $this->assertGuest();
    }

    public function test_consent_page_can_be_rendered(): void
    {
        $response = $this->get('/personal-data-consent');

        $response->assertStatus(200);
    }
}
