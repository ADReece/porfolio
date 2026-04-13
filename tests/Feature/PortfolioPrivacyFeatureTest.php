<?php

namespace Tests\Feature;

use App\Http\Controllers\ProfileController;
use App\Models\Collection;
use App\Models\Photo;
use App\Models\Set;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PortfolioPrivacyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_collections_display_mode_only_shows_published_visible_public_collections(): void
    {
        $user = User::factory()->create([
            'portfolio_display_mode' => 'collections',
        ]);

        $visible = Collection::factory()->create([
            'user_id' => $user->id,
            'status' => 'Published',
            'hide_from_portfolio' => false,
            'private' => false,
        ]);

        Collection::factory()->create([
            'user_id' => $user->id,
            'status' => 'Draft',
            'hide_from_portfolio' => false,
            'private' => false,
        ]);

        Collection::factory()->create([
            'user_id' => $user->id,
            'status' => 'Published',
            'hide_from_portfolio' => true,
            'private' => false,
        ]);

        Collection::factory()->create([
            'user_id' => $user->id,
            'status' => 'Published',
            'hide_from_portfolio' => false,
            'private' => true,
            'password' => bcrypt('secret1234'),
        ]);

        $response = $this->get(route('profile.view', ['username' => $user->username]));

        $response->assertOk();
        $response->assertViewIs('profile.view-collections');
        $response->assertViewHas('collections', function ($collections) use ($visible) {
            return $collections->count() === 1 && $collections->first()->id === $visible->id;
        });
    }

    public function test_private_collection_route_requires_password_before_showing_gallery(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'user_id' => $user->id,
            'private' => true,
            'password' => bcrypt('secret1234'),
            'status' => 'Published',
        ]);

        $response = $this->get(route('profile.collection', [
            'username' => $user->username,
            'collection_id' => $collection->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('collections.frontend.password');
    }

    public function test_private_collection_rejects_incorrect_password(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'user_id' => $user->id,
            'private' => true,
            'password' => bcrypt('secret1234'),
            'status' => 'Published',
        ]);

        $response = $this->get(route('profile.collection', [
            'username' => $user->username,
            'collection_id' => $collection->id,
            'password' => 'wrong-password',
        ]));

        $response->assertOk();
        $response->assertViewIs('collections.frontend.password');
        $response->assertSee('Invalid Password');
    }

    public function test_private_collection_allows_access_with_valid_password(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'user_id' => $user->id,
            'private' => true,
            'password' => bcrypt('secret1234'),
            'status' => 'Published',
        ]);

        $response = $this->get(route('profile.collection', [
            'username' => $user->username,
            'collection_id' => $collection->id,
            'password' => 'secret1234',
        ]));

        $response->assertOk();
        $response->assertViewIs('collections.frontend.show');
    }

    public function test_public_collection_filters_out_private_photos(): void
    {
        $user = User::factory()->create();

        $collection = Collection::factory()->create([
            'user_id' => $user->id,
            'private' => false,
            'status' => 'Published',
        ]);

        $set = Set::create([
            'collection_id' => $collection->id,
            'name' => 'Main Set',
            'hide_from_portfolio' => false,
        ]);

        $publicPhoto = Photo::create([
            'user_id' => $user->id,
            'set_id' => $set->id,
            'url' => 'photos/public-photo.jpg',
            'caption' => 'Public',
            'description' => null,
            'private' => false,
            'size' => 1024,
            'tags' => null,
        ]);

        Photo::create([
            'user_id' => $user->id,
            'set_id' => $set->id,
            'url' => 'photos/private-photo.jpg',
            'caption' => 'Private',
            'description' => null,
            'private' => true,
            'size' => 1024,
            'tags' => null,
        ]);

        $request = Request::create('/@'.$user->username.'/collections/'.$collection->id, 'GET');
        $view = app(ProfileController::class)->collection($request, $user->username, $collection->id);
        $resolvedCollection = $view->getData()['collection'];
        $photos = $resolvedCollection->sets->first()->photos;

        $this->assertSame('collections.frontend.show', $view->name());
        $this->assertCount(1, $photos);
        $this->assertSame($publicPhoto->id, $photos->first()->id);
    }
}
