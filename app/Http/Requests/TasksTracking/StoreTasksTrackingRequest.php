<?php

namespace App\Http\Requests\TasksTracking;

use Illuminate\Foundation\Http\FormRequest;

class StoreTasksTrackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create tasks tracking');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'function_id' => 'required|exists:functions_requirements,id',
            'correspondence' => 'required|string',
            'actual_start_date' => 'required|date',
            'actual_end_date' => 'required|date|after:actual_start_date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'function_id.required' => 'Please select a function requirement.',
            'function_id.exists' => 'The selected function requirement is invalid.',
            'correspondence.required' => 'Correspondence is required.',
            'correspondence.string' => 'Correspondence must be a string.',
            'actual_start_date.required' => 'Actual start date is required.',
            'actual_start_date.date' => 'Actual start date must be a valid date.',
            'actual_end_date.required' => 'Actual end date is required.',
            'actual_end_date.date' => 'Actual end date must be a valid date.',
            'actual_end_date.after' => 'Actual end date must be after actual start date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: pending, in_progress, completed, cancelled.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'function_id' => 'function requirement',
            'correspondence' => 'correspondence',
            'actual_start_date' => 'actual start date',
            'actual_end_date' => 'actual end date',
            'status' => 'status',
        ];
    }
}
