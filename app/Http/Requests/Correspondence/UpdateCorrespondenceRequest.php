<?php

namespace App\Http\Requests\Correspondence;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCorrespondenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit correspondences');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'task_id' => 'required|exists:tasks_tracking,id',
            'type' => 'required|string|max:50|in:email,letter,phone,meeting,document,other',
            'reference' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'task_id.required' => 'Please select a task.',
            'task_id.exists' => 'The selected task is invalid.',
            'type.required' => 'Correspondence type is required.',
            'type.string' => 'Correspondence type must be a string.',
            'type.max' => 'Correspondence type cannot exceed 50 characters.',
            'type.in' => 'Correspondence type must be one of: email, letter, phone, meeting, document, other.',
            'reference.required' => 'Reference is required.',
            'reference.string' => 'Reference must be a string.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'task_id' => 'task',
            'type' => 'correspondence type',
            'reference' => 'reference',
        ];
    }
}
