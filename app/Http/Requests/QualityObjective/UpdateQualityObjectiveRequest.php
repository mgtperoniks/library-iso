<?php

namespace App\Http\Requests\QualityObjective;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQualityObjectiveRequest extends FormRequest
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
            'period_id' => ['required', 'integer', 'exists:quality_objective_periods,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'process_name' => ['required', 'string', 'max:150'],
            'objective_statement' => ['required', 'string'],
            'kpi_indicator' => ['required', 'string', 'max:200'],
            'unit' => ['nullable', 'string', 'max:30'],
            'target_value' => ['required', 'numeric', 'min:0'],
            'target_polarity' => ['required', 'string', 'in:gte,lte'],
            'monitoring_frequency' => ['required', 'string', 'in:monthly,quarterly,biannual,annual'],
            'measurement_method' => ['nullable', 'string'],
            'pic_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_mandatory' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
