<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CalculateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => [
                'required',
                'date_format:Y-m-d',
            ],

            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],

            'formula' => [
                'required',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! str_contains($value, '[OMIE_MD]')) {
                        $fail('La fórmula debe contener el segmento [OMIE_MD].');
                    }
                },
            ],
        ];
    }

     public function messages(): array
    {
        return [
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date_format' => 'La fecha de inicio debe tener el formato YYYY-MM-DD.',

            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.date_format' => 'La fecha de fin debe tener el formato YYYY-MM-DD.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',

            'formula.required' => 'La fórmula es obligatoria.',
            'formula.string' => 'La fórmula debe ser una cadena de texto.',
            'formula.max' => 'La fórmula no puede superar los 500 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $validator->errors(),
            ], 400)
        );
    }
}
