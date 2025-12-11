<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      title="City Request",
 *      description="City request body data",
 *      type="object",
 *      required={"department_id", "name"}
 * )
 */
class CityRequest extends FormRequest
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
     *      property="department_id",
     *      title="department_id",
     *      description="ID of the department",
     *      example="9d21b3a0-5e1a-4b3a-9b3a-1b3a05e1a4b3"
     * )
     *
     * @OA\Property(
     *      property="name",
     *      title="name",
     *      description="Name of the city",
     *      example="Bogota"
     * )
     *
     * @OA\Property(
     *      property="status",
     *      title="status",
     *      description="Status of the city (A: Active, I: Inactive)",
     *      example="A"
     * )
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
