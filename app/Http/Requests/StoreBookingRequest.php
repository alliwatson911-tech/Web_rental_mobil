<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi pesanan. Perhatikan: tidak ada field harga/total di sini.
 * Angka total dari browser diabaikan; harga dihitung ulang di server.
 */
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pickup_location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after:pickup_date'],
            'delivery_address' => [
                Rule::requiredIf(fn () => (bool) Location::find($this->input('pickup_location_id'))?->requires_address),
                'nullable', 'string', 'max:255',
            ],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['integer', Rule::exists('addons', 'id')->where('is_active', true)],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,20}$/'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
            'agree_terms' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pickup_location_id' => 'lokasi ambil',
            'pickup_date' => 'tanggal ambil',
            'return_date' => 'tanggal kembali',
            'delivery_address' => 'alamat antar',
            'addons' => 'tambahan',
            'addons.*' => 'tambahan',
            'customer_name' => 'nama lengkap',
            'customer_phone' => 'nomor HP/WhatsApp',
            'customer_email' => 'email',
            'customer_address' => 'alamat',
            'notes' => 'catatan',
            'agree_terms' => 'persetujuan syarat sewa',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Nomor HP/WhatsApp tidak valid (8-20 digit).',
            'agree_terms.accepted' => 'Anda harus menyetujui syarat sewa.',
            'delivery_address.required' => 'Alamat antar wajib diisi untuk lokasi ini.',
        ];
    }
}
