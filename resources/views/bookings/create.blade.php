@extends('layouts.app')
@section('title', 'Pesan '.$car->name)

@section('content')
<ol class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
    <li class="badge bg-brand-600 text-white">1. Isi pesanan</li>
    <li class="badge bg-slate-200 text-slate-600">2. Ringkasan</li>
    <li class="badge bg-slate-200 text-slate-600">3. Selesai</li>
</ol>

<form method="POST" action="{{ route('bookings.review', $car) }}" class="grid gap-6 lg:grid-cols-3" id="booking-form">
    @csrf
    <div class="space-y-6 lg:col-span-2">
        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Jadwal &amp; lokasi</h2>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <div>
                    <label class="label" for="pickup_location_id">Lokasi ambil</label>
                    <select class="input" id="pickup_location_id" name="pickup_location_id" required>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" data-requires-address="{{ $location->requires_address ? 1 : 0 }}" @selected(old('pickup_location_id', $prefill['pickup_location_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('pickup_location_id')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="pickup_date">Tanggal ambil</label>
                    <input class="input" type="date" id="pickup_date" name="pickup_date" min="{{ now()->toDateString() }}" value="{{ old('pickup_date', $prefill['pickup_date'] ?? '') }}" required>
                    @error('pickup_date')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="return_date">Tanggal kembali</label>
                    <input class="input" type="date" id="return_date" name="return_date" min="{{ now()->addDay()->toDateString() }}" value="{{ old('return_date', $prefill['return_date'] ?? '') }}" required>
                    @error('return_date')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-3" id="delivery-address-wrap">
                <label class="label" for="delivery_address">Alamat antar (wajib bila memilih "antar ke alamat")</label>
                <input class="input" id="delivery_address" name="delivery_address" value="{{ old('delivery_address') }}" placeholder="Nama jalan, nomor, kelurahan, patokan">
                @error('delivery_address')<p class="error">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-500">Biaya antar-jemput otomatis ditambahkan bila memilih lokasi antar ke alamat.</p>
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Tambahan</h2>
            <p class="text-xs text-slate-500">Default: lepas kunci (tanpa sopir). Centang bila perlu.</p>
            <div class="mt-3 space-y-2">
                @foreach ($addons as $addon)
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50">
                        <input type="checkbox" class="mt-1" name="addons[]" value="{{ $addon->id }}"
                               data-price="{{ $addon->price }}" data-type="{{ $addon->charge_type }}" data-code="{{ $addon->code }}"
                               @checked(in_array($addon->id, old('addons', [])))>
                        <span class="flex-1">
                            <span class="font-semibold">{{ $addon->name }}</span>
                            <span class="text-sm text-brand-700">· {{ rupiah($addon->price) }} {{ $addon->chargeLabel() }}</span>
                            @if ($addon->description)<span class="block text-xs text-slate-500">{{ $addon->description }}</span>@endif
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-bold text-slate-900">Data pemesan</h2>
            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div>
                    <label class="label" for="customer_name">Nama lengkap (sesuai KTP)</label>
                    <input class="input" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100">
                    @error('customer_name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="customer_phone">Nomor HP/WhatsApp</label>
                    <input class="input" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required inputmode="tel" placeholder="08xxxxxxxxxx">
                    @error('customer_phone')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="customer_email">Email (opsional)</label>
                    <input class="input" type="email" id="customer_email" name="customer_email" value="{{ old('customer_email') }}">
                    @error('customer_email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="customer_address">Alamat domisili (opsional)</label>
                    <input class="input" id="customer_address" name="customer_address" value="{{ old('customer_address') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="label" for="notes">Catatan (opsional)</label>
                    <textarea class="input" id="notes" name="notes" rows="2" maxlength="500" placeholder="Mis. jam ambil, nomor penerbangan">{{ old('notes') }}</textarea>
                </div>
            </div>
            <label class="mt-4 flex items-start gap-2 text-sm">
                <input type="checkbox" name="agree_terms" value="1" class="mt-1" @checked(old('agree_terms')) required>
                <span>Saya sudah membaca dan menyetujui <a href="{{ route('terms') }}" target="_blank" class="text-brand-600 underline">syarat sewa</a>.</span>
            </label>
            @error('agree_terms')<p class="error">{{ $message }}</p>@enderror
        </section>
    </div>

    <aside>
        <div class="card sticky top-20 overflow-hidden">
            @include('partials.car-image', ['car' => $car, 'class' => 'h-36 w-full object-cover'])
            <div class="p-5">
                <p class="font-bold text-slate-900">{{ $car->name }}</p>
                <p class="text-sm text-slate-500">{{ $car->category->name }} · {{ rupiah($car->daily_price) }}/hari</p>
                <div class="mt-4 rounded-lg bg-slate-50 p-3 text-sm">
                    <p class="flex justify-between"><span>Lama sewa</span><span id="est-days">-</span></p>
                    <p class="mt-1 flex justify-between font-bold"><span>Perkiraan total</span><span id="est-total">-</span></p>
                    <p class="mt-1 text-xs text-slate-500">Perkiraan saja. Total final dihitung oleh sistem di halaman ringkasan.</p>
                </div>
                <button class="btn btn-primary mt-4 w-full py-3">Lihat ringkasan</button>
            </div>
        </div>
    </aside>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('booking-form');
    const daily = {{ (int) $car->daily_price }};
    const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID');
    const loc = document.getElementById('pickup_location_id');
    const wrap = document.getElementById('delivery-address-wrap');
    const update = () => {
        const p = new Date(form.pickup_date.value), r = new Date(form.return_date.value);
        const needsAddress = loc.selectedOptions[0]?.dataset.requiresAddress === '1';
        wrap.classList.toggle('hidden', !needsAddress);
        document.getElementById('delivery_address').required = needsAddress;
        if (isNaN(p) || isNaN(r) || r <= p) { document.getElementById('est-days').textContent = '-'; document.getElementById('est-total').textContent = '-'; return; }
        const days = Math.max(1, Math.round((r - p) / 86400000));
        let total = daily * days;
        form.querySelectorAll('input[name="addons[]"]').forEach((cb) => {
            const auto = needsAddress && cb.dataset.code === 'delivery';
            if (cb.checked || auto) total += Number(cb.dataset.price) * (cb.dataset.type === 'per_day' ? days : 1);
        });
        document.getElementById('est-days').textContent = days + ' hari';
        document.getElementById('est-total').textContent = fmt(total);
    };
    form.addEventListener('change', update);
    update();
})();
</script>
@endpush
