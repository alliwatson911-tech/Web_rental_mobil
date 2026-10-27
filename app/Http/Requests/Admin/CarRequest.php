<?php

namespace App\Http\Requests\Admin;

use App\Models\Car;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $car = $this->route('car');

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'between:1990,'.(date('Y') + 1)],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('cars', 'plate_number')->ignore($car?->id)],
            'transmission' => ['required', Rule::in(array_keys(Car::TRANSMISSIONS))],
            'seats' => ['required', 'integer', 'between:2,20'],
            'fuel' => ['required', 'string', 'max:30'],
            'daily_price' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_keys(Car::STATUSES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('padaidi.max_upload_kb')],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'kategori', 'name' => 'nama mobil', 'brand' => 'merek', 'year' => 'tahun',
            'plate_number' => 'nomor polisi', 'transmission' => 'transmisi', 'seats' => 'jumlah kursi',
            'fuel' => 'bahan bakar', 'daily_price' => 'harga per hari', 'status' => 'status',
            'description' => 'deskripsi', 'photos' => 'foto', 'photos.*' => 'foto',
        ];
    }
}
