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

<div class="padaidi-datepicker-wrapper" data-datepicker-wrapper style="position: relative; display: flex; align-items: center; width: 100%;">
    {{-- Formatted Display Input (dd/mm/yyyy) --}}
    <input type="text"
           id="{{ $id }}_display"
           class="input padaidi-datepicker-display bg-white border border-slate-300 text-slate-800 rounded pl-3.5 pr-11 py-2.5 w-full focus:border-[#F5A623] focus:ring-1 focus:ring-[#F5A623] text-sm font-semibold transition cursor-pointer {{ $class }}"
           style="width: 100%; padding-right: 44px; cursor: pointer; height: 42px;"
           placeholder="{{ $placeholder }}"
           value="{{ $displayVal }}"
           autocomplete="off"
           maxlength="10"
           data-datepicker-display
           readonly
           {{ $required ? 'required' : '' }}>

    {{-- Native Date Input that anchors picker and submits YYYY-MM-DD --}}
    <input type="date"
           id="{{ $id }}"
           name="{{ $inputName }}"
           value="{{ $rawVal }}"
           min="{{ $minVal }}"
           @if($max) max="{{ $max }}" @endif
           data-datepicker-value
           data-min="{{ $minVal }}"
           data-max="{{ $max }}"
           style="position: absolute; right: 0; top: 0; width: 44px; height: 100%; opacity: 0; cursor: pointer; z-index: 20;"
           aria-label="Pilih tanggal">

    {{-- Prominent Actionable Calendar Button --}}
    <div class="datepicker-trigger"
         style="position: absolute; right: 0; top: 0; bottom: 0; width: 44px; display: flex; align-items: center; justify-content: center; color: #F5A623; pointer-events: none; z-index: 10;"
         aria-hidden="true">
        <x-icon name="calendar" class="w-5 h-5 text-[#F5A623]" />
    </div>
</div>
