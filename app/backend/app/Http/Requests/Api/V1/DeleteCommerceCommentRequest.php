<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * DeleteCommerceCommentRequest
 *
 * @OA\Schema(
 *     schema="DeleteCommerceCommentRequest",
 * )
 */
class DeleteCommerceCommentRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        return ($this->user()?->can('provider.comments.delete') ?? false)
            && $this->userCanAccessCommerce();
    }

    public function rules(): array
    {
        return [];
    }
}
