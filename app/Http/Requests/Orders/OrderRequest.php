<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ValidationMessages;

class OrderRequest extends FormRequest
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
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Get the custom validation messages for the defined rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'product_id.required' => ValidationMessages::PRODUCT_ID_REQUIRED->value,
            'product_id.integer' => ValidationMessages::PRODUCT_ID_INVALID->value,
            'product_id.exists' => ValidationMessages::PRODUCT_ID_NOT_FOUND->value,
            'quantity.required' => ValidationMessages::QUANTITY_REQUIRED->value,
            'quantity.integer' => ValidationMessages::QUANTITY_INVALID->value,
            'quantity.min' => ValidationMessages::QUANTITY_MIN->value
        ];
    }
}
