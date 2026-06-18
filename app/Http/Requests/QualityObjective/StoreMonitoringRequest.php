<?php

namespace App\Http\Requests\QualityObjective;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMonitoringRequest extends FormRequest
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
            'period_label' => [
                'required',
                'string',
                'max:20',
                Rule::unique('quality_objective_monitorings', 'period_label')
                    ->where('objective_id', $this->input('objective_id')),
            ],
            'period_year' => ['required', 'integer', 'digits:4'],
            'period_month' => ['nullable', 'integer', 'between:1,12'],
            'period_quarter' => ['nullable', 'integer', 'between:1,4'],
            'target_snapshot' => ['required', 'numeric', 'min:0'],
            'realization_value' => ['nullable', 'numeric', 'min:0'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
