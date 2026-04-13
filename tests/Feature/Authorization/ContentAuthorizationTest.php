<?php

namespace Tests\Feature\Authorization;

use App\Models\Collection;
use App\Models\Photo;
use App\Models\Set;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_create_redirects_to_pricing_when_user_has_no_plan(): void
    {
        $user = User::factory()->create(['subscription_plan_id' => null]);

        $response = $this->actingAs($user)->get(route('collections.create'));

        $response->assertRedirect(route('pricing'));
    }

    public function test_collection_create_is_accessible_when_user_is_below_plan_limit(): void
    {
        $plan = $this->makePlan(['collection_limit' => 2]);
        $user = User::factory()->create(['subscription_plan_id' => $plan->id]);

        $response = $this->actingAs($user)->get(route('collections.create'));

        $response->assertOk();
    }

    public function test_collection_store_is_blocked_when_limit_is_reached(): void
    {
        $plan = $this->makePlan(['collection_limit' => 1]);
        $user = User::factory()->create(['subscription_plan_id' => $plan->id]);

        Collection::create([
            'name' => 'Existing',
            'user_id' => $user->id,
            'status' => 'Draft',
            'private' => false,
        ]);

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('collections.store'), [
                'name' => 'Blocked Collection',
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('collections', ['name' => 'Blocked Collection']);
    }

    public function test_non_owner_cannot_update_photo(): void
    {
        [$owner, $photo] = $this->makeOwnedPhoto();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->patchJson(route('photos.update', $photo), [
            'caption' => 'Hacked',
        ]);

        $response->assertForbidden();
    }

    public function test_owner_can_update_photo(): void
    {
        [$owner, $photo] = $this->makeOwnedPhoto();

        $response = $this->actingAs($owner)->patchJson(route('photos.update', $photo), [
            'caption' => 'Updated caption',
        ]);

        $response->assertOk();
        $this->assertSame('Updated caption', $photo->fresh()->caption);
    }

    private function makeOwnedPhoto(): array
    {
        $plan = $this->makePlan();
        $user = User::factory()->create(['subscription_plan_id' => $plan->id]);

        $collection = Collection::create([
            'name' => 'Portfolio',
            'user_id' => $user->id,
            'status' => 'Draft',
            'private' => false,
        ]);

        $set = Set::create([
            'name' => 'Set A',
            'collection_id' => $collection->id,
        ]);

        $photo = Photo::create([
            'user_id' => $user->id,
            'set_id' => $set->id,
            'url' => 'photos/'.$user->id.'/image.jpg',
            'private' => false,
            'size' => 12345,
        ]);

        return [$user, $photo];
    }

    private function makePlan(array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Starter',
            'slug' => 'starter-'.uniqid(),
            'price' => 0,
            'annual_price' => 0,
            'annual_discount_percent' => 0,
            'photo_limit' => 100,
            'collection_limit' => 5,
            'private_collections' => false,
            'watermarking' => false,
            'selling' => false,
            'video_upload' => false,
            'custom_templates' => false,
            'upload_logo' => false,
            'features' => ['Basic'],
            'active' => true,
            'sort_order' => 1,
            'recommended' => false,
        ], $overrides));
    }
}
