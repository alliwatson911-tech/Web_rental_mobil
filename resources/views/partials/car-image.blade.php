{{-- Foto utama mobil, atau ilustrasi bila belum ada foto. Butuh relasi primaryPhoto sudah dimuat. --}}
@php($url = $car->relationLoaded('primaryPhoto') && $car->primaryPhoto ? $car->primaryPhoto->url() : null)
@if ($url)
    <img src="{{ $url }}" alt="{{ $car->name }}" class="{{ $class ?? 'h-44 w-full object-cover' }}" loading="lazy">
@else
    <div class="{{ $class ?? 'h-44 w-full' }} grid place-items-center bg-gradient-to-br from-brand-100 to-brand-50 text-brand-600">
        <svg class="h-20 w-20 opacity-70" viewBox="0 0 64 64" fill="currentColor" aria-hidden="true"><path d="M14 38l4-12a6 6 0 0 1 5.7-4h16.6a6 6 0 0 1 5.7 4l4 12h2a4 4 0 0 1 4 4v8a2 2 0 0 1-2 2h-4a6 6 0 0 1-12 0H25a6 6 0 0 1-12 0H8a2 2 0 0 1-2-2v-8a4 4 0 0 1 4-4zm5.2 0h25.6l-3-9a2 2 0 0 0-1.9-1.4H24.1a2 2 0 0 0-1.9 1.4zM19 54a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm26 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
    </div>
@endif
