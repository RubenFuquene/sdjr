<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShowCommerceCommentRequest
 *
 * @OA\Schema(
 *     schema="ShowCommerceCommentRequest",
 *
 *     @OA\Property(property="commerce_id", type="integer", example=10),
 *     @OA\Property(property="id", type="integer", example=1)
 * )
 */
class ShowCommerceCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.comments.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
