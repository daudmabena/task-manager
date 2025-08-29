<?php

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit systems');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $system = $this->route('system');
        
        return [
            'name' => ['required', 'string', 'max:255', 'unique:systems,name,' . $system->id],
            'description' => ['required', 'string'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The system name is required.',
            'name.string' => 'The system name must be a string.',
            'name.max' => 'The system name may not be greater than 255 characters.',
            'description.required' => 'The system description is required.',
            'description.string' => 'The system description must be a string.',
        ];
    }
}
