<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      title="Department Request",
 *      description="Department request body data",
 *      type="object",
 *      required={"country_id", "name"}
 * )
 */
class DepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $action = $this->route()->getActionMethod();
        $permission = 'admin.departments.'.($action === 'store' ? 'create' : 'update');

        return $this->user()?->can($permission) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @OA\Property(
     *      property="country_id",
     *      title="country_id",
     *      description="ID of the country",
     *      example="9d21b3a0-5e1a-4b3a-9b3a-1b3a05e1a4b3"
     * )
     * @OA\Property(
     *      property="name",
     *      title="name",
     *      description="Name of the department",
     *      example="Cundinamarca"
     * )
     * @OA\Property(
     *      property="status",
     *      title="status",
     *      description="Status of the department (A: Active, I: Inactive)",
     *      example="A"
     * )
     * @OA\Property(
     *      property="code",
     *      title="code",
     *      description="Unique code for the department (6 alphanumeric characters)",
     *      example="DEP001"
     * )
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:6|regex:/^[A-Za-z0-9]+$/|unique:departments,code',
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
