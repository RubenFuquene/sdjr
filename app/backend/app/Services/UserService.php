<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service class for handling user-related business logic for users.
 */
class UserService
{
    /**
     * Get all users with roles and permissions loaded.
     *
     * @return Collection<int, User>
     */
    public function getAll(): Collection
    {
        return User::with('roles', 'permissions')->get();
    }

    /**
     * Get paginated users with roles and permissions loaded.
     *
     * @param int $perPage Number of users per page (default: 15)
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('roles', 'permissions')->paginate($perPage);
    }

    /**
     * Create a new user with hashed password.
     *
     * @param array<string, mixed> $data User data including name, email, password
     * @return User The created user instance
     */
    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['remember_token'] = Str::random(10);
        return User::create($data);
    }

    /**
     * Find a user by ID with roles and permissions loaded.
     *
     * @param int $id User ID
     * @return User|null The user instance or null if not found
     */
    public function find(int $id): ?User
    {
        return User::with('roles', 'permissions')->find($id);
    }

    /**
     * Update an existing user, hashing password if provided.
     *
     * @param int $user The user to update
     * @param array<string, mixed> $data Updated user data
     * @return User The updated user instance
     */
    public function update(int $user_id, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user = User::findOrFail($user_id);
        $user->update($data);
        return $user;
    }

    /**
     * Delete a user.
     *
     * @param int $user_id The user to delete
     * @return bool True if deletion was successful
     */
    public function delete(int $user_id): bool
    {
        $user = User::findOrFail($user_id); 
        return $user->delete();
    }

    /**
     * Update the status of a user (activate/inactivate).
     *
     * @param int $user_id The user to update
     * @param string $status
     * @return User
     */
    public function updateStatus(int $user_id, string $status): User
    {
        $user = User::findOrFail($user_id);
        $user->status = $status;
        $user->save();
        return $user;
    }
}