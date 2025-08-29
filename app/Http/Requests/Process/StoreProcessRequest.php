<?php

namespace App\Http\Requests\Process;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create processes');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'system_id' => 'required|exists:systems,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'system_id.required' => 'Please select a system.',
            'system_id.exists' => 'The selected system is invalid.',
            'name.required' => 'Process name is required.',
            'name.string' => 'Process name must be a string.',
            'name.max' => 'Process name cannot exceed 255 characters.',
            'description.required' => 'Process description is required.',
            'description.string' => 'Process description must be a string.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'system_id' => 'system',
            'name' => 'process name',
            'description' => 'process description',
        ];
    }
}
