<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    #[Test]
    public function registering_creates_a_user_and_redirects_to_their_unique_link(): void
    {
        $response = $this->post(route('register'), [
            'username' => 'test-user',
            'phone_number' => '+15551234567',
        ]);

        $user = User::where('username', 'test-user')->firstOrFail();
        $link = $user->links()->valid()->sole();

        $this->assertSame('+15551234567', $user->phone_number);
        $response->assertRedirect(route('luck', $link));
    }

    #[Test]
    public function the_link_handed_out_at_registration_opens_page_a(): void
    {
        $response = $this->post(route('register'), [
            'username' => 'test-user',
            'phone_number' => '+15551234567',
        ]);

        $this->followRedirects($response)->assertOk();
    }

    #[Test]
    public function registration_requires_a_username_and_a_phone_number(): void
    {
        $response = $this->post(route('register'), []);

        $response->assertInvalid(['username', 'phone_number']);
    }

    #[Test]
    public function registration_fails_when_the_username_is_already_taken(): void
    {
        User::factory()->create(['username' => 'taken']);

        $response = $this->post(route('register'), [
            'username' => 'taken',
            'phone_number' => '+15551234567',
        ]);

        $response->assertInvalid(['username']);
    }

    #[Test]
    public function registration_fails_when_the_phone_number_is_already_taken(): void
    {
        User::factory()->create(['phone_number' => '+15551234567']);

        $response = $this->post(route('register'), [
            'username' => 'someone-else',
            'phone_number' => '+15551234567',
        ]);

        $response->assertInvalid(['phone_number']);
    }
}
