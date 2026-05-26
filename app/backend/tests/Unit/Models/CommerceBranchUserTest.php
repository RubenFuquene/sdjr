<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\CommerceBranchUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceBranchUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_commerce(): void
    {
        $commerceBranchUser = CommerceBranchUser::factory()->create();

        $this->assertInstanceOf(Commerce::class, $commerceBranchUser->commerce);
        $this->assertEquals($commerceBranchUser->commerce_id, $commerceBranchUser->commerce->id);
    }

    public function test_belongs_to_commerce_branch(): void
    {
        $commerceBranchUser = CommerceBranchUser::factory()->create();

        $this->assertInstanceOf(CommerceBranch::class, $commerceBranchUser->commerceBranch);
        $this->assertEquals($commerceBranchUser->commerce_branch_id, $commerceBranchUser->commerceBranch->id);
    }

    public function test_belongs_to_user(): void
    {
        $commerceBranchUser = CommerceBranchUser::factory()->create();

        $this->assertInstanceOf(User::class, $commerceBranchUser->user);
        $this->assertEquals($commerceBranchUser->user_id, $commerceBranchUser->user->id);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $data = [
            'commerce_id' => 1,
            'commerce_branch_id' => 1,
            'user_id' => 1,
        ];

        $commerceBranchUser = new CommerceBranchUser($data);

        $this->assertEquals(1, $commerceBranchUser->commerce_id);
        $this->assertEquals(1, $commerceBranchUser->commerce_branch_id);
        $this->assertEquals(1, $commerceBranchUser->user_id);
    }

    public function test_uses_soft_deletes(): void
    {
        $commerceBranchUser = CommerceBranchUser::factory()->create();
        $id = $commerceBranchUser->id;

        $commerceBranchUser->delete();

        $this->assertSoftDeleted('commerce_branch_users', ['id' => $id]);
        $this->assertNotNull($commerceBranchUser->fresh()->deleted_at);
    }

    public function test_casts_dates_correctly(): void
    {
        $commerceBranchUser = CommerceBranchUser::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $commerceBranchUser->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $commerceBranchUser->updated_at);
    }
}
