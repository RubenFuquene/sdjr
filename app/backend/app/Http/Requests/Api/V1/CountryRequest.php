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
        $action = $this->route()->getActionMethod();
        $permission = 'countries.' . ($action === 'store' ? 'create' : 'update');
        return $this->user()?->can($permission) ?? false;
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
     * @OA\Property(
     *      property="code",
     *      title="code",
     *      description="Unique code for the country (6 alphanumeric characters)",
     *      example="CO1234"
     * )
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {        
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:6|regex:/^[A-Za-z0-9]+$/|unique:countries,code',
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
