<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ValidationMessages;
class LoginRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => ValidationMessages::EMAIL_REQUIRED->value,
            'email.email' => ValidationMessages::EMAIL_INVALID->value,
            'email.exists' => ValidationMessages::EMAIL_NOT_FOUND->value,
            'password.required' => ValidationMessages::PASSWORD_REQUIRED->value,
        ];
    }
}

