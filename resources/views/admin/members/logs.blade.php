@extends('layouts.admin')
@section('title', 'Log Akses Dokumen Sensitif (Audit Trail)')
@section('actions')
    <a href="{{ route('admin.members.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar Member</a>
@endsection

@section('content')
<div class="space-y-4">
    <div class="card p-4 bg-[#111827] border border-[#1e293b]">
        <div class="flex items-start gap-3">
            <x-icon name="lock" class="w-5 h-5 text-[#F5A623] shrink-0 mt-0.5" />
            <div class="text-xs text-slate-300">
                <strong class="text-white">Kepatuhan Privasi Data & Perlindungan Data Pribadi (UU PDP):</strong>
                Seluruh aktivitas unggah, preview, maupun unduh berkas identitas pelanggan (KTP dan SIM A) dicatat secara permanen dengan IP Address dan User-Agent untuk memastikan akuntabilitas akses dan mencegah kebocoran data.
            </div>
        </div>
    </div>

    <div class="card overflow-x-auto border border-[#1e293b] bg-[#111827]">
        <table class="table-base text-xs">
            <thead>
                <tr>
                    <th>Waktu (WITA)</th>
                    <th>Pengakses (User / Admin)</th>
                    <th>Dokumen</th>
                    <th>Tindakan (Action)</th>
                    <th>IP Address</th>
                    <th>User Agent / Perangkat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e293b]">
            @forelse ($logs as $log)
                <tr class="hover:bg-[#1e293b]/50 transition">
                    <td class="whitespace-nowrap text-slate-400 font-mono text-[11px]">
                        {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                    </td>
                    <td class="font-medium text-slate-100">
                        @if ($log->user)
                            <div class="font-bold text-[#F5A623]">{{ $log->user->name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $log->user->role }} · ID #{{ $log->user_id }}</div>
                        @else
                            <span class="text-slate-400">Tamu / Sistem</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $log->document_type === 'ktp' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' }} uppercase text-[10px] font-bold">
                            {{ $log->document_type }}
                        </span>
                        <span class="text-slate-400 text-[10px] ml-1">#{{ $log->document_id }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $log->action === 'view' ? 'bg-sky-500/20 text-sky-300 border border-sky-500/40' : ($log->action === 'upload' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-800 text-slate-300 border border-[#1e293b]') }} font-semibold">
                            {{ strtoupper($log->action) }}
                        </span>
                    </td>
                    <td class="font-mono text-slate-300 text-[11px]">
                        {{ $log->ip_address ?? '-' }}
                    </td>
                    <td class="text-slate-400 text-[11px] max-w-xs truncate" title="{{ $log->user_agent }}">
                        {{ $log->user_agent ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400">
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

