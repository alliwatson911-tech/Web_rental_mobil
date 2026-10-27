<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LookupBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20'],
            'customer_phone' => ['required', 'string', 'max:30'],
        ];
    }

    public function attributes(): array
    {
        return ['code' => 'kode pesanan', 'customer_phone' => 'nomor HP'];
    }
}
