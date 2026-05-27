<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\CommerceBranchUser;
use App\Models\User;
use App\Notifications\BranchAssignmentNotification;
use App\Notifications\BranchLeaderWelcomeNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Service for handling CommerceBranch User assignments
 */
class CommerceBranchUserService
{
    private UserService $userService;

    private PasswordResetService $passwordResetService;

    private AuditLogService $auditLogService;

    public function __construct(
        UserService $userService,
        PasswordResetService $passwordResetService,
        AuditLogService $auditLogService
    ) {
        $this->userService = $userService;
        $this->passwordResetService = $passwordResetService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get all branch leaders for a commerce (with optional pagination)
     *
     * @param  int  $commerceId  The commerce ID
     * @param  bool  $paginate  Whether to paginate results
     * @param  int  $perPage  Items per page
     * @return LengthAwarePaginator|Collection
     *
     * @throws ModelNotFoundException
     */
    public function getCommerceUsers(int $commerceId, bool $paginate = true, int $perPage = 15)
    {
        $commerce = Commerce::findOrFail($commerceId);

        $query = $commerce->branchLeaders()
            ->with(['assignedBranches' => function ($q) use ($commerceId) {
                $q->where('commerce_id', $commerceId);
            }])
            ->withPivot(['created_at', 'updated_at']);

        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Create a new user without password and assign to branch
     *
     * @param  array  $userData  User data [name, last_name, email, phone]
     * @param  int  $commerceBranchId  Branch ID to assign
     * @param  int  $creatorUserId  ID of user creating the branch leader
     * @return array [user, token, branch] Returns created user, password reset token, and branch
     *
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function createAndAssign(array $userData, int $commerceBranchId, int $creatorUserId): array
    {
        DB::beginTransaction();

        try {
            $commerceBranch = CommerceBranch::findOrFail($commerceBranchId);

            // Validate creator is owner of the commerce
            $commerce = Commerce::where('id', $commerceBranch->commerce_id)
                ->where('owner_user_id', $creatorUserId)
                ->firstOrFail();

            // Create user without password
            $user = $this->userService->createUserWithoutPassword($userData);

            // Assign branch_leader role
            $user->assignRole('branch_leader');

            // Create password reset token
            $token = $this->passwordResetService->createTokenForUser($user->email);

            // Assign user to branch
            CommerceBranchUser::create([
                'commerce_id' => $commerceBranch->commerce_id,
                'commerce_branch_id' => $commerceBranch->id,
                'user_id' => $user->id,
            ]);

            DB::commit();

            Log::info('Branch leader created and assigned', [
                'user_id' => $user->id,
                'commerce_branch_id' => $commerceBranch->id,
                'commerce_id' => $commerce->id,
            ]);

            // Send welcome notification with password setup token
            try {
                Notification::send($user, new BranchLeaderWelcomeNotification($user, $commerceBranch, $token));
            } catch (\Throwable $e) {
                Log::warning('Branch leader welcome notification dispatch failed', [
                    'user_id' => $user->id,
                    'commerce_branch_id' => $commerceBranch->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return [
                'user' => $user->fresh(['roles', 'assignedBranches']),
                'token' => $token,
                'branch' => $commerceBranch,
            ];

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Failed to create and assign branch leader - related model not found', [
                'error' => $e->getMessage(),
                'commerce_branch_id' => $commerceBranchId,
            ]);

            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create and assign branch leader', [
                'error' => $e->getMessage(),
                'commerce_branch_id' => $commerceBranchId,
            ]);

            throw $e;
        }
    }

    /**
     * Assign an existing user to a branch
     *
     * @param  int  $userId  User ID to assign
     * @param  int  $commerceBranchId  Branch ID to assign to
     * @param  int  $assignerUserId  ID of user performing assignment
     * @return CommerceBranchUser The created pivot record
     *
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function assignUserToBranch(int $userId, int $commerceBranchId, int $assignerUserId): CommerceBranchUser
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($userId);
            $commerceBranch = CommerceBranch::findOrFail($commerceBranchId);

            // Validate assigner is owner of the commerce
            $commerce = Commerce::where('id', $commerceBranch->commerce_id)
                ->where('owner_user_id', $assignerUserId)
                ->firstOrFail();

            // Ensure user has branch_leader role
            if (! $user->hasRole('branch_leader')) {
                $user->assignRole('branch_leader');
            }

            // Check if already assigned
            $existing = CommerceBranchUser::where('user_id', $userId)
                ->where('commerce_branch_id', $commerceBranchId)
                ->first();

            if ($existing) {
                throw new \Exception('User is already assigned to this branch');
            }

            // Create assignment
            $assignment = CommerceBranchUser::create([
                'commerce_id' => $commerceBranch->commerce_id,
                'commerce_branch_id' => $commerceBranch->id,
                'user_id' => $user->id,
            ]);

            DB::commit();

            Log::info('User assigned to branch', [
                'user_id' => $user->id,
                'commerce_branch_id' => $commerceBranch->id,
                'commerce_id' => $commerce->id,
            ]);

            // Send assignment notification to existing user
            try {
                Notification::send($user, new BranchAssignmentNotification($user, $commerceBranch));
            } catch (\Throwable $e) {
                Log::warning('Branch assignment notification dispatch failed', [
                    'user_id' => $user->id,
                    'commerce_branch_id' => $commerceBranch->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $assignment->fresh(['user', 'commerceBranch']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign user to branch', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'commerce_branch_id' => $commerceBranchId,
            ]);

            throw $e;
        }
    }

    /**
     * Remove a user from a branch
     *
     * @param  int  $userId  User ID to remove
     * @param  int  $commerceBranchId  Branch ID to remove from
     * @param  int  $removerUserId  ID of user performing removal
     * @return bool True if removed successfully
     *
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function removeUserFromBranch(int $userId, int $commerceBranchId, int $removerUserId): bool
    {
        DB::beginTransaction();

        try {
            $commerceBranch = CommerceBranch::findOrFail($commerceBranchId);

            // Validate remover is owner of the commerce
            Commerce::where('id', $commerceBranch->commerce_id)
                ->where('owner_user_id', $removerUserId)
                ->firstOrFail();

            $assignment = CommerceBranchUser::where('user_id', $userId)
                ->where('commerce_branch_id', $commerceBranchId)
                ->firstOrFail();

            $assignment->delete();

            DB::commit();

            Log::info('User removed from branch', [
                'user_id' => $userId,
                'commerce_branch_id' => $commerceBranchId,
                'commerce_id' => $commerceBranch->commerce_id,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove user from branch', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'commerce_branch_id' => $commerceBranchId,
            ]);

            throw $e;
        }
    }

    /**
     * Get all users assigned to a specific branch
     *
     * @param  int  $commerceBranchId  The branch ID
     * @param  bool  $paginate  Whether to paginate results
     * @param  int  $perPage  Items per page
     * @return LengthAwarePaginator|Collection
     *
     * @throws ModelNotFoundException
     */
    public function getCommerceBranchUsers(int $commerceBranchId, bool $paginate = true, int $perPage = 15)
    {
        $commerceBranch = CommerceBranch::findOrFail($commerceBranchId);

        $query = $commerceBranch->branchLeaders()
            ->withPivot(['created_at', 'updated_at']);

        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Check if a user is assigned to a specific branch
     *
     * @param  int  $userId  The user ID
     * @param  int  $commerceBranchId  The branch ID
     * @return bool True if user is assigned to branch
     */
    public function isUserAssignedToBranch(int $userId, int $commerceBranchId): bool
    {
        return CommerceBranchUser::where('user_id', $userId)
            ->where('commerce_branch_id', $commerceBranchId)
            ->exists();
    }

    /**
     * Get all branches assigned to a user for a specific commerce
     *
     * @param  int  $userId  The user ID
     * @param  int  $commerceId  The commerce ID
     * @return Collection Collection of branches
     */
    public function getUserBranchesByCommerce(int $userId, int $commerceId): Collection
    {
        return CommerceBranch::whereHas('commerceBranchUsers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('commerce_id', $commerceId)
            ->with(['department', 'city', 'neighborhood'])
            ->get();
    }
}
