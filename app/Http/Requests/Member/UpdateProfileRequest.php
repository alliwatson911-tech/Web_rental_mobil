<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'address' => ['nullable', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'digits:16'],
            'sim_number' => ['nullable', 'string', 'max:30'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'phone' => 'nomor WhatsApp/HP',
            'address' => 'alamat',
            'nik' => 'nomor NIK KTP',
            'sim_number' => 'nomor SIM A',
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ];
    }
}

