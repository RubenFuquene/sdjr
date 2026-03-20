<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="PatchProductStatusRequest",
 *   required={"status"},
 *   @OA\Property(property="status", type="string", example="0", enum={"1","0"}, description="Product status (1=active, 0=inactive)")
 * )
 */
class PatchProductStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.products.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:'.Constant::STATUS_ACTIVE.','.Constant::STATUS_INACTIVE,
        ];
    }
}