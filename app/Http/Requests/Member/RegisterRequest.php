<?php

namespace App\Http\Requests\Member;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/', Rule::unique('users', 'phone')],
            'password' => ['required', 'confirmed', Password::min(6)],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'phone' => 'nomor WhatsApp/HP',
            'password' => 'kata sandi',
            'address' => 'alamat domisili',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email ini sudah terdaftar sebagai akun.',
            'phone.unique' => 'Nomor WhatsApp/HP ini sudah terdaftar.',
            'phone.regex' => 'Nomor WhatsApp/HP tidak valid (8-20 digit).',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ];
    }
}

