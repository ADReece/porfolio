<?php

namespace Tests\Unit\Policies;

use App\Models\Collection;
use App\Models\Set;
use App\Models\User;
use App\Policies\SetPolicy;
use Tests\TestCase;

class SetPolicyTest extends TestCase
{
    public function test_owner_can_view_update_and_delete_set(): void
    {
        $user = new User();
        $user->id = 'user-1';
        $collection = new Collection(['user_id' => 'user-1']);
        $set = new Set();
        $set->setRelation('collection', $collection);
        $policy = new SetPolicy();

        $this->assertTrue($policy->view($user, $set));
        $this->assertTrue($policy->update($user, $set));
        $this->assertTrue($policy->delete($user, $set));
    }

    public function test_non_owner_cannot_view_update_or_delete_set(): void
    {
        $user = new User();
        $user->id = 'user-1';
        $collection = new Collection(['user_id' => 'user-2']);
        $set = new Set();
        $set->setRelation('collection', $collection);
        $policy = new SetPolicy();

        $this->assertFalse($policy->view($user, $set));
        $this->assertFalse($policy->update($user, $set));
        $this->assertFalse($policy->delete($user, $set));
    }
}
