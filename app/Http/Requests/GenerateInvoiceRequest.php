<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInvoiceRequest extends FormRequest
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
            'customer_id'           => 'required|exists:customers,id',
            'issue_date'            => 'required|date',
            'due_date'              => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.name'          => 'required|string',
            'items.*.unit_price'         => 'required|numeric|min:0',
            'items.*.quantity'           => 'required|integer|min:1',
            'tax'                   => 'nullable|numeric|min:0',
            'discount'              => 'nullable|numeric|min:0',
            'shipping_fee'          => 'nullable|numeric|min:0',
        ];
    }
}
