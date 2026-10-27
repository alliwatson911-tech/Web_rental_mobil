<?php

namespace App\Http\Requests\Admin;

use App\Models\Addon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'alpha_dash', 'max:50', Rule::unique('addons', 'code')->ignore($this->route('addon')?->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
            'charge_type' => ['required', Rule::in([Addon::PER_DAY, Addon::ONCE])],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['code' => 'kode', 'name' => 'nama', 'description' => 'deskripsi', 'price' => 'harga', 'charge_type' => 'jenis biaya'];
    }
}
