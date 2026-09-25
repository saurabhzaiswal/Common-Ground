<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'min:3', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'max:255',
                Password::min(8)->mixedCase()->numbers()->symbols(),
                'regex:/[\p{P}\p{S}]/u',
            ],
            'cf-turnstile-response' => ['required', 'string', 'max:2048'],
        ];
    }
}
