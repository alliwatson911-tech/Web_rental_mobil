{{-- Form pencarian: lokasi ambil, tanggal ambil, tanggal kembali (+ kategori opsional) --}}
<form method="GET" action="{{ route('cars.index') }}" class="grid gap-3 {{ ($withCategory ?? false) ? 'md:grid-cols-5' : 'md:grid-cols-4' }}">
    <div>
        <label class="label" for="pickup_location_id">Lokasi ambil</label>
        <select class="input" id="pickup_location_id" name="pickup_location_id">
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected(($filters['pickup_location_id'] ?? null) == $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="pickup_date">Tanggal ambil</label>
        <input class="input" type="date" id="pickup_date" name="pickup_date" min="{{ now()->toDateString() }}" value="{{ $filters['pickup_date'] ?? '' }}" required>
    </div>
    <div>
        <label class="label" for="return_date">Tanggal kembali</label>
        <input class="input" type="date" id="return_date" name="return_date" min="{{ now()->addDay()->toDateString() }}" value="{{ $filters['return_date'] ?? '' }}" required>
    </div>
    @if ($withCategory ?? false)
        <div>
            <label class="label" for="category">Kategori</label>
            <select class="input" id="category" name="category">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(($filters['category'] ?? null) === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="flex items-end">
        <button class="btn btn-accent w-full py-2.5">Cari mobil tersedia</button>
    </div>
</form>
