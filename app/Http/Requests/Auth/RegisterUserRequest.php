<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'A valid first name is required.',
            'first_name.string' => 'A valid first name is required.',
            'first_name.max' => 'Fnirst ame cannot exceed 255 characters.',

            'last_name.required' => 'A valid last name is required.',
            'last_name.string' => 'A valid last name is required.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',

            'email.required' => 'A valid email is required.',
            'email.string' => 'A valid email is required.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'email.email' => 'A valid email is required.',
            'email.unique' => 'Email address cannot be used.',

            'password.required' => 'A password is required.',
            'password.confirmed' => 'Password\'s must match.' ,
            'password.min' => 'Password must be at least 8 characters long.',
        ];
    }
}
