<?php

namespace App\Http\Requests\QualityObjective;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePeriodRequest extends FormRequest
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
        $periodId = $this->route('period');

        return [
            'year' => [
                'required',
                'integer',
                'digits:4',
                Rule::unique('quality_objective_periods', 'year')->ignore($periodId),
            ],
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,active,closed,archived'],
        ];
    }
}
