<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ValidationMessages; 

class UpdateProductStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:1'
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'quantity.required' => ValidationMessages::QUANTITY_REQUIRED->value,
            'quantity.integer' => ValidationMessages::QUANTITY_INVALID->value,
            'quantity.min' => ValidationMessages::QUANTITY_MIN->value,
        ];
    }
}
