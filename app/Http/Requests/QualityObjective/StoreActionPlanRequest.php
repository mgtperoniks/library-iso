<?php

namespace App\Http\Requests\QualityObjective;

use Illuminate\Foundation\Http\FormRequest;

class StoreActionPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorized via policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'objective_id' => ['required', 'integer', 'exists:quality_objectives,id'],
            'sequence' => ['nullable', 'integer', 'min:1'],
            'program_name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'pic_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'target_date' => ['required', 'date'],
            'actual_date' => ['nullable', 'date', 'after_or_equal:target_date'],
            'budget_estimated' => ['nullable', 'numeric', 'min:0'],
            'progress_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'in:open,in_progress,completed,overdue,cancelled'],
            'completion_notes' => ['nullable', 'string'],
        ];
    }
}
