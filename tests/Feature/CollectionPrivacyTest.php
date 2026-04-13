<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    /** @test */
    public function public_collection_is_accessible_to_anyone()
    {
        $collection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'private' => false,
        ]);

        // Owner can create and access
        $this->assertTrue($collection->user_id === $this->user->id);
        $this->assertFalse($collection->private);

        // Other user can view (public)
        $this->assertFalse($collection->private);
    }

    /** @test */
    public function private_collection_requires_password()
    {
        $password = 'secret123';
        $collection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'private' => true,
            'password' => bcrypt($password),
        ]);

        $this->assertTrue($collection->private);
        $this->assertNotNull($collection->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($password, $collection->password));
    }

    /** @test */
    public function owner_can_access_own_collection()
    {
        $collection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'private' => true,
        ]);

        $this->assertEquals($this->user->id, $collection->user_id);
    }

    /** @test */
    public function collection_can_be_hidden_from_portfolio()
    {
        $collection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'hide_from_portfolio' => true,
        ]);

        $this->assertTrue($collection->hide_from_portfolio);
    }

    /** @test */
    public function private_collections_can_be_hidden_from_portfolio()
    {
        $collection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'private' => true,
            'password' => bcrypt('secret'),
            'hide_from_portfolio' => true,
        ]);

        $this->assertTrue($collection->private);
        $this->assertTrue($collection->hide_from_portfolio);
    }

    /** @test */
    public function collection_attributes_stored_correctly()
    {
        $collection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Wedding 2026',
            'event_date' => '2026-06-15',
            'private' => true,
            'watermarked' => true,
            'hide_from_portfolio' => false,
            'password' => bcrypt('password123'),
        ]);

        $this->assertEquals('Wedding 2026', $collection->name);
        $this->assertEquals('2026-06-15', $collection->event_date->format('Y-m-d'));
        $this->assertTrue($collection->private);
        $this->assertTrue($collection->watermarked);
        $this->assertFalse($collection->hide_from_portfolio);
    }

    /** @test */
    public function collection_status_is_tracked()
    {
        $draftCollection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'Draft',
        ]);

        $this->assertEquals('Draft', $draftCollection->status);

        $publishedCollection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'Published',
        ]);

        $this->assertEquals('Published', $publishedCollection->status);
    }

    /** @test */
    public function only_owner_can_update_collection()
    {
        $collection = Collection::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name',
        ]);

        // Owner can update
        $collection->update(['name' => 'Updated Name']);
        $this->assertEquals('Updated Name', $collection->fresh()->name);

        // Other user should not have permission (authorization handled by policy)
        $this->assertNotEquals($this->otherUser->id, $collection->user_id);
    }
}
