@extends('layouts.app')
@section('title', 'Pesan '.$car->name.' - Padaidi Rent Car')

@section('content')
<ol class="mb-6 flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wider">
    <li class="badge badge-accent px-3 py-1">1. Isi pesanan</li>
    <li class="badge bg-slate-100 text-slate-600 border border-slate-200 px-3 py-1">2. Ringkasan</li>
    <li class="badge bg-slate-100 text-slate-600 border border-slate-200 px-3 py-1">3. Selesai</li>
</ol>

<form method="POST" action="{{ route('bookings.review', $car) }}" class="grid gap-6 lg:grid-cols-3 items-start" id="booking-form">
    @csrf
    <div class="space-y-6 lg:col-span-2">
        {{-- Jadwal & Lokasi --}}
        <section class="card card-accent-top p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
            <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide mb-4">Jadwal &amp; lokasi</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="label text-slate-700 font-bold" for="pickup_location_id">Lokasi ambil</label>
                    <select class="input" id="pickup_location_id" name="pickup_location_id" required>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" data-requires-address="{{ $location->requires_address ? 1 : 0 }}" @selected(old('pickup_location_id', $prefill['pickup_location_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('pickup_location_id')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label text-slate-700 font-bold" for="pickup_date">Tanggal ambil</label>
                    <input class="input" type="date" id="pickup_date" name="pickup_date" min="{{ now()->toDateString() }}" value="{{ old('pickup_date', $prefill['pickup_date'] ?? '') }}" required>
                    @error('pickup_date')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label text-slate-700 font-bold" for="return_date">Tanggal kembali</label>
                    <input class="input" type="date" id="return_date" name="return_date" min="{{ now()->addDay()->toDateString() }}" value="{{ old('return_date', $prefill['return_date'] ?? '') }}" required>
                    @error('return_date')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-4" id="delivery-address-wrap">
                <label class="label text-slate-700 font-bold" for="delivery_address">Alamat antar (wajib bila memilih "antar ke alamat")</label>
                <input class="input" id="delivery_address" name="delivery_address" value="{{ old('delivery_address') }}" placeholder="Nama jalan, nomor rumah/kantor, kelurahan, patokan">
                @error('delivery_address')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-xs text-slate-500">Biaya antar-jemput otomatis ditambahkan bila memilih lokasi antar ke alamat.</p>
            </div>
        </section>

        {{-- Tambahan / Addons --}}
        <section class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
            <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">Tambahan</h2>
            <p class="text-xs text-slate-500 mt-1 mb-4">Default: lepas kunci (tanpa sopir). Centang layanan yang Anda butuhkan.</p>
            <div class="space-y-3">
                @foreach ($addons as $addon)
                    <label class="flex cursor-pointer items-start gap-3 rounded bg-slate-50 border border-slate-200 p-3.5 hover:border-slate-300 transition-colors">
                        <input type="checkbox" class="mt-1 rounded border-slate-300 text-[#F5A623] focus:ring-[#F5A623]" name="addons[]" value="{{ $addon->id }}"
                               data-price="{{ $addon->price }}" data-type="{{ $addon->charge_type }}" data-code="{{ $addon->code }}"
                               @checked(in_array($addon->id, old('addons', [])))>
                        <span class="flex-1">
                            <span class="font-bold text-slate-900 text-sm">{{ $addon->name }}</span>
                            <span class="text-xs font-black text-[#F5A623] ml-2">· {{ rupiah($addon->price) }} {{ $addon->chargeLabel() }}</span>
                            @if ($addon->description)<span class="block text-xs text-slate-500 mt-0.5">{{ $addon->description }}</span>@endif
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- Voucher & Kode Promo --}}
        <section class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <div>
                    <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide">Voucher &amp; Kode Promo</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Punya kode kupon potongan harga? Masukkan kode promo Anda di sini.</p>
                </div>
                @auth
                    @if (auth()->user()->isMember())
                        @php $userTier = auth()->user()->tier(); @endphp
                        @if ($userTier && $userTier->discount_percentage > 0)
                            <span class="badge badge-accent text-xs font-bold">
                                Member {{ $userTier->name }} Aktif (Diskon {{ $userTier->discount_percentage }}%)
                            </span>
                        @endif
                    @endif
                @endauth
            </div>
            <div class="mt-3">
                <input class="input font-mono uppercase max-w-xs text-sm tracking-wider font-bold" 
                       id="promo_code" name="promo_code" 
                       value="{{ old('promo_code', request('promo_code')) }}" 
                       placeholder="Contoh: HEMAT50K">
                @error('promo_code')
                    <p class="text-xs text-rose-500 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- Data Pemesan --}}
        <section class="card p-6 md:p-8 bg-white border border-slate-200 shadow-sm">
            <h2 class="font-extrabold text-base uppercase text-slate-900 tracking-wide mb-4">Data pemesan</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="label text-slate-700 font-bold" for="customer_name">Nama lengkap (sesuai KTP)</label>
                    <input class="input" id="customer_name" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required maxlength="100">
                    @error('customer_name')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label text-slate-700 font-bold" for="customer_phone">Nomor HP/WhatsApp</label>
                    <input class="input" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', auth()->user()?->phone) }}" required inputmode="tel" placeholder="08xxxxxxxxxx">
                    @error('customer_phone')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label text-slate-700 font-bold" for="customer_email">Email (opsional)</label>
                    <input class="input" type="email" id="customer_email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}">
                    @error('customer_email')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label text-slate-700 font-bold" for="customer_address">Alamat domisili (opsional)</label>
                    <input class="input" id="customer_address" name="customer_address" value="{{ old('customer_address', auth()->user()?->memberProfile?->address) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="label text-slate-700 font-bold" for="notes">Catatan (opsional)</label>
                    <textarea class="input" id="notes" name="notes" rows="2" maxlength="500" placeholder="Mis. jam ambil, nomor penerbangan pesawat">{{ old('notes') }}</textarea>
                </div>
            </div>
            <label class="mt-5 flex items-start gap-2.5 text-xs text-slate-700 cursor-pointer">
                <input type="checkbox" name="agree_terms" value="1" class="mt-0.5 rounded border-slate-300 text-[#F5A623] focus:ring-[#F5A623]" @checked(old('agree_terms')) required>
                <span>Saya sudah membaca dan menyetujui <a href="{{ route('terms') }}" target="_blank" class="text-amber-600 underline font-bold hover:text-amber-800">syarat sewa</a>.</span>
            </label>
            @error('agree_terms')<p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>@enderror
        </section>
    </div>

    {{-- Sidebar Ringkasan --}}
    <aside>
        <div class="card card-accent-top sticky top-24 overflow-hidden bg-white border border-slate-200 p-0 shadow-xl" style="position: sticky; top: 6rem; z-index: 20;">
            @include('partials.car-image', ['car' => $car, 'class' => 'h-40 w-full object-cover'])
            <div class="p-6">
                <h3 class="font-black text-lg uppercase text-slate-900 tracking-wide">{{ $car->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $car->category->name }} · <span class="text-[#F5A623] font-bold">{{ rupiah($car->daily_price) }} / hari</span></p>

                <div class="mt-5 rounded bg-slate-50 border border-slate-200 p-4 text-xs space-y-2">
                    <div class="flex justify-between text-slate-500">
                        <span>Lama sewa:</span>
                        <strong class="text-slate-900" id="est-days">-</strong>
                    </div>
                    <div class="flex justify-between text-slate-700 pt-2 border-t border-slate-200 font-bold">
                        <span>Perkiraan total:</span>
                        <span class="text-[#F5A623] text-sm font-black" id="est-total">-</span>
                    </div>
                    <p class="text-[10px] text-slate-500 pt-1 italic">Perkiraan saja. Total final dihitung oleh sistem di halaman ringkasan.</p>
                </div>

                <button type="submit" class="btn btn-primary mt-5 w-full py-3.5 font-black uppercase tracking-wider text-xs shadow-md">
                    Lihat ringkasan
                </button>
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
