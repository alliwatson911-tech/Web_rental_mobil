@props([
    'id',
    'name' => null,
    'value' => '',
    'min' => '',
    'max' => '',
    'placeholder' => 'dd/mm/yyyy',
    'required' => false,
    'class' => '',
])

@php
    $inputName = $name ?? $id;
    $rawVal = old($inputName, $value ?? '');
    $displayVal = '';
    if (!empty($rawVal)) {
        try {
            $displayVal = \Carbon\Carbon::parse($rawVal)->format('d/m/Y');
        } catch (\Throwable $e) {
            $displayVal = $rawVal;
        }
    }
    $minVal = $min ?: now()->toDateString();
@endphp

<div class="relative padaidi-datepicker-wrapper w-full" data-datepicker-wrapper>
    {{-- Formatted Display Input (dd/mm/yyyy) --}}
    <input type="text"
           id="{{ $id }}_display"
           class="input padaidi-datepicker-display bg-white border border-slate-300 text-slate-800 rounded pl-3.5 pr-11 py-2.5 w-full focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] text-sm font-semibold transition cursor-pointer {{ $class }}"
           placeholder="{{ $placeholder }}"
           value="{{ $displayVal }}"
           autocomplete="off"
           inputmode="numeric"
           data-datepicker-display
           {{ $required ? 'required' : '' }}>

    {{-- Native Hidden Input for Form Submission & Validation (YYYY-MM-DD) --}}
    <input type="hidden"
           id="{{ $id }}"
           name="{{ $inputName }}"
           value="{{ $rawVal }}"
           data-datepicker-value
           data-min="{{ $minVal }}"
           data-max="{{ $max }}">

    {{-- Prominent Actionable Calendar Button --}}
    <button type="button"
            class="datepicker-trigger absolute inset-y-0 right-0 flex items-center pr-3.5 pl-2 text-[#F5A623] hover:text-amber-600 focus:outline-none cursor-pointer transition-transform active:scale-95"
            data-datepicker-trigger
            title="Klik untuk memilih tanggal sewa"
            aria-label="Pilih tanggal sewa">
        <x-icon name="calendar" class="w-5 h-5 text-[#F5A623]" />
    </button>
</div>

