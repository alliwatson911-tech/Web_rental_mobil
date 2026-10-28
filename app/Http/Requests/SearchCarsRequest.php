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
            'transmission' => ['nullable', 'string', 'in:manual,automatic'],
            'seats' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pickup_location_id' => 'lokasi ambil',
            'pickup_date' => 'tanggal ambil',
            'return_date' => 'tanggal kembali',
            'category' => 'kategori',
            'transmission' => 'transmisi',
            'seats' => 'kapasitas kursi',
        ];
    }

    public function hasDates(): bool
    {
        return $this->filled('pickup_date') && $this->filled('return_date');
    }
}
