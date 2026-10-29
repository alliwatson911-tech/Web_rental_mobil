@props([
    'kicker' => null,
    'title' => '',
    'highlight' => null,
    'desc' => null,
    'center' => false,
])

<div class="{{ $center ? 'text-center mx-auto' : '' }} mb-10 max-w-3xl">
    @if ($kicker)
        <span class="section-kicker">
            {{ $kicker }}
        </span>
    @endif

    <h2 class="section-title">
        {{ $title }}
        @if ($highlight)
            <span class="text-[#F5A623]">{{ $highlight }}</span>
        @endif
    </h2>

    @if ($desc)
        <p class="section-desc {{ $center ? 'mx-auto' : '' }}">
            {{ $desc }}
        </p>
    @endif
</div>

