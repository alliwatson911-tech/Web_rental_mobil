@props([
    'name' => 'car',
    'class' => 'w-5 h-5',
    'size' => null,
])

@php
    $baseClass = $size ? "w-{$size} h-{$size}" : $class;
    // Guarantee shrink-0 unless already specified
    $svgClass = str_contains($baseClass, 'shrink') ? $baseClass : "{$baseClass} shrink-0";

    // Extract exact pixel dimensions so SVG never renders at 300px without CSS
    $px = 20; // default 20px (w-5)
    if ($size) {
        $px = match((string)$size) {
            '3' => 12,
            '3.5' => 14,
            '4' => 16,
            '5' => 20,
            '6' => 24,
            '7' => 28,
            '8' => 32,
            '9' => 36,
            '10' => 40,
            '12' => 48,
            '14' => 56,
            '16' => 64,
            '20' => 80,
            default => is_numeric($size) ? (int)((float)$size * 4) : 20,
        };
    } else {
        if (preg_match('/\b(?:w|h)-(\d+(?:\.\d+)?)\b/', $baseClass, $m)) {
            $px = match($m[1]) {
                '3' => 12,
                '3.5' => 14,
                '4' => 16,
                '5' => 20,
                '6' => 24,
                '7' => 28,
                '8' => 32,
                '9' => 36,
                '10' => 40,
                '12' => 48,
                '14' => 56,
                '16' => 64,
                '20' => 80,
                default => (int)((float)$m[1] * 4),
            };
        }
    }
    $dimAttrs = "width=\"{$px}\" height=\"{$px}\" style=\"width:{$px}px;height:{$px}px;min-width:{$px}px;max-width:{$px}px;\"";
@endphp

@switch($name)
    @case('shield')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        @break

    @case('car')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 17h14M5 17a2 2 0 01-2-2V9a2 2 0 012-2h1.5l1.5-3h8l1.5 3H19a2 2 0 012 2v6a2 2 0 01-2 2M5 17a2 2 0 104 0M19 17a2 2 0 104 0"/>
        </svg>
        @break

    @case('key')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a4 4 0 10-5.657 5.657L3 19v2h2v-2h2v-2h2l1.657-1.657A4 4 0 0015 7z"/>
        </svg>
        @break

    @case('wrench')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.9 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
        </svg>
        @break

    @case('whatsapp')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
        </svg>
        @break

    @case('phone')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
        </svg>
        @break

    @case('map-pin')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        @break

    @case('clock')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        @break

    @case('calendar')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        @break

    @case('check')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        @break

    @case('close')
    @case('cross')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        @break

    @case('menu')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        @break

    @case('user')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        @break

    @case('star')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        @break

    @case('mail')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        @break

    @case('arrow-right')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
        @break

    @case('chevron-down')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
        @break

    @case('tag')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        @break

    @case('printer')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        @break

    @case('credit-card')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="2" y="5" width="20" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 15h2"/>
        </svg>
        @break

    @case('download')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        @break

    @case('chart-bar')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        @break

    @case('qrcode')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 14h3v3h-3zM17 17h4v4h-4zM14 20h3v1h-3zM20 14h1v3h-1z"/>
        </svg>
        @break

    @case('lock')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="5" y="11" width="14" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0v4"/>
        </svg>
        @break

    @case('sparkles')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.286L13 21l-2.286-6.857L5 12l5.714-2.286L13 3z"/>
        </svg>
        @break

    @case('refresh')
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        @break

    @default
        <svg class="{{ $svgClass }}" {!! $dimAttrs !!} fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="9"/>
        </svg>
@endswitch
