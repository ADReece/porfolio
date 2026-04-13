<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);

        $user = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
    }

    /** @test */
    public function user_can_verify_email()
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);

        // Simulate clicking verification link
        $user->markEmailAsVerified();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /** @test */
    public function user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
    }

    /** @test */
    public function user_can_request_password_reset()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Request password reset
        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirectToRoute('login');
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_with_free_plan_can_upload_photos()
    {
        $freePlan = SubscriptionPlan::factory()->create(['name' => 'Free', 'photo_limit' => 10]);
        $user = User::factory()->create();
        $user->subscriptionPlan()->associate($freePlan)->save();

        // User with free plan should be able to upload (if limit not exceeded)
        $this->assertTrue($user->canUploadPhotos());
    }

    /** @test */
    public function user_without_subscription_cannot_upload_photos()
    {
        $user = User::factory()->create();
        // User has no subscription plan
        $this->assertNull($user->subscriptionPlan);

        // User without subscription cannot upload photos
        $this->assertFalse($user->canUploadPhotos());
    }

    /** @test */
    public function user_with_feature_override_can_upload_photos()
    {
        $user = User::factory()->create([
            'feature_override' => true,
        ]);

        // User with feature override can upload regardless of plan
        $this->assertTrue($user->canUploadPhotos());
    }
}
