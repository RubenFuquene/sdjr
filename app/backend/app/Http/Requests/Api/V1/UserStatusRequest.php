<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UserStatusRequest
 *
 * @OA\Schema(
 *     schema="UserStatusRequest",
 *     type="object",
 *     required={"status"},
 *
 *     @OA\Property(property="status", type="integer", enum={0,1}, example=1, description="User status: 1=active, 0=inactive")
 * )
 */
class UserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.profiles.users.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:0,1'],
        ];
    }
}
