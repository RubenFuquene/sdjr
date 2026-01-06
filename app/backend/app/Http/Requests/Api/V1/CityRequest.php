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
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $action = $this->route()->getActionMethod();
        $permission = 'admin.cities.'.($action === 'store' ? 'create' : 'update');

        return $this->user()?->can($permission) ?? false;
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
     * @OA\Property(
     *      property="name",
     *      title="name",
     *      description="Name of the city",
     *      example="Bogota"
     * )
     * @OA\Property(
     *      property="status",
     *      title="status",
     *      description="Status of the city (A: Active, I: Inactive)",
     *      example="A"
     * )
     * @OA\Property(
     *      property="code",
     *      title="code",
     *      description="Unique code for the city (6 alphanumeric characters)",
     *      example="CITY01"
     * )
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:6|regex:/^[A-Za-z0-9]+$/|unique:cities,code',
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
