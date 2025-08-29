<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class GivePermissionToRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage permissions');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'permissions.required' => 'At least one permission must be selected.',
            'permissions.array' => 'The permissions must be an array.',
            'permissions.*.exists' => 'The selected permission is invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'permissions' => 'permissions',
        ];
    }
}
