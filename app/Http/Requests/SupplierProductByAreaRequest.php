<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierProductByAreaRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company' => 'nullable|string|max:10',
            'area' => 'required|string|max:50',
            'date' => 'required|string',
            'type' => 'nullable|string|in:OC,OS,OT,ALL',
        ];
    }

    public function messages(): array
    {
        return [
            'area.required' => 'El área es obligatoria',
            'date.required' => 'El rango de fechas es obligatorio',
            'type.in' => 'El tipo debe ser OC, OS, OT o ALL',
        ];
    }

    public function getCompany(): string
    {
        return $this->input('company', '003');
    }

    public function getType(): string
    {
        return $this->input('type', 'OC');

        return $type === 'ALL' ? null : $type;
    }
}
