<?php

namespace Tests\Unit\Policies;

use App\Models\Photo;
use App\Models\User;
use App\Policies\PhotoPolicy;
use Tests\TestCase;

class PhotoPolicyTest extends TestCase
{
    public function test_owner_can_view_update_and_delete_photo(): void
    {
        $user = new User();
        $user->id = 'user-1';
        $photo = new Photo(['user_id' => 'user-1']);
        $policy = new PhotoPolicy();

        $this->assertTrue($policy->view($user, $photo));
        $this->assertTrue($policy->update($user, $photo));
        $this->assertTrue($policy->delete($user, $photo));
    }

    public function test_non_owner_cannot_view_update_or_delete_photo(): void
    {
        $user = new User();
        $user->id = 'user-1';
        $photo = new Photo(['user_id' => 'user-2']);
        $policy = new PhotoPolicy();

        $this->assertFalse($policy->view($user, $photo));
        $this->assertFalse($policy->update($user, $photo));
        $this->assertFalse($policy->delete($user, $photo));
    }
}
