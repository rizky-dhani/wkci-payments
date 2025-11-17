<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateQRISRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'trx_id' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'order_id' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1|max:999999999',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'trx_id.required' => 'Transaction ID is required',
            'customer_name.required' => 'Customer name is required',
            'customer_email.required' => 'Customer email is required',
            'customer_email.email' => 'Please provide a valid email address',
            'order_id.required' => 'Order ID is required',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a valid number',
            'amount.min' => 'Amount must be at least 1',
            'amount.max' => 'Amount cannot exceed 999,999,999',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => (float) $this->amount,
        ]);
    }
}
