<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchCarsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pickup_location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'pickup_date' => ['nullable', 'required_with:return_date', 'date', 'after_or_equal:today'],
            'return_date' => ['nullable', 'required_with:pickup_date', 'date', 'after:pickup_date'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pickup_location_id' => 'lokasi ambil',
            'pickup_date' => 'tanggal ambil',
            'return_date' => 'tanggal kembali',
            'category' => 'kategori',
        ];
    }

    public function hasDates(): bool
    {
        return $this->filled('pickup_date') && $this->filled('return_date');
    }
}
