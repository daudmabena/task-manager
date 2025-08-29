<?php

namespace App\Http\Requests\FunctionsRequirement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFunctionsRequirementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit functions requirements');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'process_id' => 'required|exists:processes,id',
            'name' => 'required|string|max:255',
            'requirement' => 'required|string',
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after:planned_start_date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'process_id.required' => 'Please select a process.',
            'process_id.exists' => 'The selected process is invalid.',
            'name.required' => 'Function name is required.',
            'name.string' => 'Function name must be a string.',
            'name.max' => 'Function name cannot exceed 255 characters.',
            'requirement.required' => 'Function requirement is required.',
            'requirement.string' => 'Function requirement must be a string.',
            'planned_start_date.required' => 'Planned start date is required.',
            'planned_start_date.date' => 'Planned start date must be a valid date.',
            'planned_end_date.required' => 'Planned end date is required.',
            'planned_end_date.date' => 'Planned end date must be a valid date.',
            'planned_end_date.after' => 'Planned end date must be after planned start date.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'process_id' => 'process',
            'name' => 'function name',
            'requirement' => 'function requirement',
            'planned_start_date' => 'planned start date',
            'planned_end_date' => 'planned end date',
        ];
    }
}
