<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      title="Country Request",
 *      description="Country request body data",
 *      type="object",
 *      required={"name"}
 * )
 */
class CountryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @OA\Property(
     *      property="name",
     *      title="name",
     *      description="Name of the country",
     *      example="Colombia"
     * )
     *
     * @OA\Property(
     *      property="status",
     *      title="status",
     *      description="Status of the country (A: Active, I: Inactive)",
     *      example="A"
     * )
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|max:1|in:A,I',
        ];
    }
}
