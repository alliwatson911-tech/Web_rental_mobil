@if (session('success'))
    <div class="mb-6 rounded border border-emerald-500/50 bg-emerald-950/80 px-4 py-3 text-xs text-emerald-200 flex items-center gap-2" role="status">
        <x-icon name="check" class="w-4 h-4 text-emerald-400 shrink-0" />
        <span>{{ session('success') }}</span>
    </div>
@endif
@if (session('info'))
    <div class="mb-6 rounded border border-sky-500/50 bg-sky-950/80 px-4 py-3 text-xs text-sky-200 flex items-center gap-2" role="status">
        <x-icon name="sparkles" class="w-4 h-4 text-sky-400 shrink-0" />
        <span>{{ session('info') }}</span>
    </div>
@endif
@if ($errors->any())
    <div class="mb-6 rounded border border-rose-500/50 bg-rose-950/80 px-4 py-3 text-xs text-rose-200" role="alert">
        <p class="font-extrabold uppercase tracking-wider text-rose-300">Mohon periksa kembali:</p>
        <ul class="mt-1 list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
