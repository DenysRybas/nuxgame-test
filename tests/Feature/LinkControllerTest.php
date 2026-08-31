<?php

namespace Tests\Feature;

use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function regenerating_deactivates_the_link_and_redirects_to_the_new_one(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $response = $this->post(route('link.regenerate', $link));

        $this->assertSame(LinkStatus::Inactive, $link->fresh()->status);

        $new = $link->user->links()->valid()->sole();
        $this->assertNotSame($link->token, $new->token);
        $response->assertRedirect(route('luck', $new));
    }

    #[Test]
    public function the_old_link_stops_working_once_regenerated(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $this->post(route('link.regenerate', $link));

        $this->get(route('luck', $link))->assertNotFound();
    }

    #[Test]
    public function deactivating_the_link_redirects_home_and_makes_the_link_unusable(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $response = $this->post(route('link.deactivate', $link));

        $this->assertSame(LinkStatus::Inactive, $link->fresh()->status);
        $response->assertRedirect(route('home'));

        $this->get(route('luck', $link))->assertNotFound();
    }

    #[Test]
    public function an_already_deactivated_link_cannot_be_regenerated(): void
    {
        $link = Link::factory()->inactive()->for(User::factory())->create();

        $this->post(route('link.regenerate', $link))->assertNotFound();
        $this->assertDatabaseCount('links', 1);
    }

    #[Test]
    public function an_expired_link_cannot_be_regenerated(): void
    {
        $link = Link::factory()->for(User::factory())->create();

        $this->travelTo(now()->addDays(Link::ACTIVE_DAYS)->addSecond());

        $this->post(route('link.regenerate', $link))->assertNotFound();
        $this->assertDatabaseCount('links', 1);
    }
}
