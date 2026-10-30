@props([
    'backUrl' => route('home'),
    'backText' => 'Kembali ke Beranda',
    'crumbs' => [],
])

<nav class="mb-5 flex flex-wrap items-center justify-between gap-3 text-xs" aria-label="Navigasi Halaman">
    <div class="flex items-center gap-3">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-1.5 font-bold uppercase tracking-wider text-amber-700 hover:text-amber-900 transition-colors">
            <span class="text-sm">←</span>
            <span>{{ $backText }}</span>
        </a>
        @if ($backUrl !== route('home'))
            <span class="text-slate-300">|</span>
            <a href="{{ route('home') }}" class="font-bold text-slate-500 hover:text-amber-700 transition-colors">
                Beranda
            </a>
        @endif
    </div>

    @if (!empty($crumbs))
        <ol class="flex flex-wrap items-center gap-2 text-slate-500 font-medium">
            <li>
                <a href="{{ route('home') }}" class="hover:text-amber-700 transition-colors">Beranda</a>
            </li>
            @foreach ($crumbs as $label => $url)
                <li class="text-slate-300">/</li>
                <li>
                    @if (is_string($label) && $url)
                        <a href="{{ $url }}" class="hover:text-amber-700 transition-colors">{{ $label }}</a>
                    @else
                        <span class="text-slate-800 font-bold" aria-current="page">{{ is_string($label) ? $label : $url }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</nav>

