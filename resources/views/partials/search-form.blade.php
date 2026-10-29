{{-- Form pencarian: lokasi ambil, tanggal ambil, tanggal kembali (+ kategori opsional) --}}
<form method="GET" action="{{ route('cars.index') }}" class="grid gap-4 {{ ($withCategory ?? false) ? 'md:grid-cols-5' : 'md:grid-cols-4' }} items-end">
    <div>
        <label class="label text-slate-700 font-bold flex items-center gap-1.5 mb-1.5 text-xs uppercase tracking-wider" for="pickup_location_id">
            <x-icon name="map-pin" class="w-3.5 h-3.5 text-[#F5A623]" />
            <span>Lokasi Ambil</span>
        </label>
        <select class="input bg-white border border-slate-300 text-slate-800 rounded px-3 py-2.5 w-full focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] text-sm font-semibold transition" id="pickup_location_id" name="pickup_location_id">
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected(($filters['pickup_location_id'] ?? null) == $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label text-slate-700 font-bold flex items-center gap-1.5 mb-1.5 text-xs uppercase tracking-wider" for="pickup_date">
            <x-icon name="calendar" class="w-3.5 h-3.5 text-[#F5A623]" />
            <span>Tanggal Ambil</span>
        </label>
        <input class="input bg-white border border-slate-300 text-slate-800 rounded px-3 py-2.5 w-full focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] text-sm font-semibold transition" type="date" id="pickup_date" name="pickup_date" min="{{ now()->toDateString() }}" value="{{ $filters['pickup_date'] ?? '' }}" required>
    </div>
    <div>
        <label class="label text-slate-700 font-bold flex items-center gap-1.5 mb-1.5 text-xs uppercase tracking-wider" for="return_date">
            <x-icon name="calendar" class="w-3.5 h-3.5 text-[#F5A623]" />
            <span>Tanggal Kembali</span>
        </label>
        <input class="input bg-white border border-slate-300 text-slate-800 rounded px-3 py-2.5 w-full focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] text-sm font-semibold transition" type="date" id="return_date" name="return_date" min="{{ now()->addDay()->toDateString() }}" value="{{ $filters['return_date'] ?? '' }}" required>
    </div>
    @if ($withCategory ?? false)
        <div>
            <label class="label text-slate-700 font-bold flex items-center gap-1.5 mb-1.5 text-xs uppercase tracking-wider" for="category">
                <x-icon name="car" class="w-3.5 h-3.5 text-[#F5A623]" />
                <span>Kategori Mobil</span>
            </label>
            <select class="input bg-white border border-slate-300 text-slate-800 rounded px-3 py-2.5 w-full focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] text-sm font-semibold transition" id="category" name="category">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(($filters['category'] ?? null) === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div>
        <button type="submit" class="btn btn-primary w-full py-2.5 px-4 font-black uppercase tracking-wider text-xs flex items-center justify-center gap-2 shadow-sm hover:shadow transition">
            <x-icon name="car" class="w-4 h-4" />
            <span>Cari Mobil Tersedia</span>
        </button>
    </div>
</form>
