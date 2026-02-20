<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * @param  array<string, mixed>  $filters  Filters to apply (name, last_name, email, phone, status)
     * @param  int  $perPage  Number of users per page (default: 15)
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('roles', 'permissions');

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (! empty($filters['last_name'])) {
            $query->where('last_name', 'like', "%{$filters['last_name']}%");
        }
        if (! empty($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }
        if (! empty($filters['phone'])) {
            $query->where('phone', 'like', "%{$filters['phone']}%");
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new user with hashed password.
     *
     * @param  array<string, mixed>  $data  User data including name, email, password
     * @return User The created user instance
     */
    public function create(array $data): User
    {        
        $data['remember_token'] = Str::random(10);

        $user = User::create($data);

        $user->syncRoles($data['roles'] ?? []);

        return $user;
    }

    /**
     * Find a user by ID with roles and permissions loaded.
     *
     * @param  int  $id  User ID
     * @return User|null The user instance or null if not found
     */
    public function find(int $id): ?User
    {
        return User::with('roles', 'permissions')->find($id);
    }

    /**
     * Update an existing user, hashing password if provided.
     *
     * @param  int  $user  The user to update
     * @param  array<string, mixed>  $data  Updated user data
     * @return User The updated user instance
     */
    public function update(int $user_id, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user = User::findOrFail($user_id);
        $user->update($data);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }

    /**
     * Delete a user.
     *
     * @param  int  $user_id  The user to delete
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
     * @param  int  $user_id  The user to update
     */
    public function updateStatus(int $user_id, string $status): User
    {
        $user = User::findOrFail($user_id);
        $user->status = $status;
        $user->save();

        return $user;
    }

    /**
     * Get all administrator users (those with 'superadmin' or 'admin' roles).
     *
     * @param  array<string, mixed>  $filters  Optional filters (not currently used)
     * @param  int  $perPage  Number of users per page (not currently used)
     * @return LengthAwarePaginator Paginated list of administrator users
     */
    public function getAdministrators(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('roles')->whereHas('roles', function ($roles) {
            $roles->whereIn('name', ['superadmin', 'admin']);
        });

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (! empty($filters['last_name'])) {
            $query->where('last_name', 'like', "%{$filters['last_name']}%");
        }
        if (! empty($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }
}
