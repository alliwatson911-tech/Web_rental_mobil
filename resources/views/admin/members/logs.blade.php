@extends('layouts.admin')
@section('title', 'Log Akses Dokumen Sensitif (Audit Trail)')
@section('actions')
    <a href="{{ route('admin.members.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar Member</a>
@endsection

@section('content')
<div class="space-y-4">
    <div class="card p-4 bg-white border border-slate-200 border-l-4 border-l-amber-500 shadow-sm rounded-xl">
        <div class="flex items-start gap-3">
            <x-icon name="lock" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
            <div class="text-xs text-slate-700 leading-relaxed">
                <strong class="text-slate-900 font-bold">Kepatuhan Privasi Data & Perlindungan Data Pribadi (UU PDP):</strong>
                Seluruh aktivitas unggah, preview, maupun unduh berkas identitas pelanggan (KTP dan SIM A) dicatat secara permanen dengan IP Address dan User-Agent untuk memastikan akuntabilitas akses dan mencegah kebocoran data.
            </div>
        </div>
    </div>

    <div class="card overflow-x-auto border border-slate-200 bg-white shadow-sm rounded-xl p-0">
        <table class="table-base text-xs w-full min-w-[960px]">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Waktu (WITA)</th>
                    <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Pengakses (User / Admin)</th>
                    <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Dokumen</th>
                    <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">Tindakan (Action)</th>
                    <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">IP Address</th>
                    <th class="py-3 px-4 text-left font-black text-slate-700 uppercase tracking-wider text-[11px]">User Agent / Perangkat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
            @forelse ($logs as $log)
                <tr class="hover:bg-amber-50/40 transition-colors">
                    <td class="py-3 px-4 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                        {{ tanggal($log->created_at, 'd/m/Y, H:i:s') }}
                    </td>
                    <td class="py-3 px-4 font-medium text-slate-900">
                        @if ($log->user)
                            <div class="font-bold text-amber-800">{{ $log->user->name }}</div>
                            <div class="text-[10px] text-slate-500 font-semibold">{{ $log->user->role }} · ID #{{ $log->user_id }}</div>
                        @else
                            <span class="text-slate-500 italic">Tamu / Sistem</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge {{ $log->document_type === 'ktp' ? 'bg-indigo-100 text-indigo-800 border border-indigo-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }} uppercase text-[10px] font-bold">
                            {{ $log->document_type }}
                        </span>
                        <span class="text-slate-500 text-[10px] font-medium ml-1">#{{ $log->document_id }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="badge {{ $log->action === 'view' ? 'bg-sky-100 text-sky-800 border border-sky-300' : ($log->action === 'upload' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-800 border border-slate-300') }} font-bold text-[10px]">
                            {{ strtoupper($log->action) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 font-mono text-slate-700 text-[11px]">
                        {{ $log->ip_address ?? '-' }}
                    </td>
                    <td class="py-3 px-4 text-slate-500 text-[11px] max-w-xs truncate" title="{{ $log->user_agent }}">
                        {{ $log->user_agent ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-500 italic bg-slate-50/50">
                        Belum ada aktivitas akses dokumen yang tercatat.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
