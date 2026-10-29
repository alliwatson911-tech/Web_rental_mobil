@extends('layouts.admin')
@section('title', 'Log Akses Dokumen Sensitif (Audit Trail)')
@section('actions')
    <a href="{{ route('admin.members.index') }}" class="btn btn-outline text-xs">&larr; Kembali ke Daftar Member</a>
@endsection

@section('content')
<div class="space-y-4">
    <div class="card p-4 bg-slate-50 border border-slate-200">
        <div class="flex items-start gap-3">
            <x-icon name="lock" class="w-5 h-5 text-slate-700 shrink-0 mt-0.5" />
            <div class="text-xs text-slate-600">
                <strong class="text-slate-800">Kepatuhan Privasi Data & Perlindungan Data Pribadi (UU PDP):</strong>
                Seluruh aktivitas unggah, preview, maupun unduh berkas identitas pelanggan (KTP dan SIM A) dicatat secara permanen dengan IP Address dan User-Agent untuk memastikan akuntabilitas akses dan mencegah kebocoran data.
            </div>
        </div>
    </div>

    <div class="card overflow-x-auto">
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
            <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $log)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap text-slate-500 font-mono text-[11px]">
                        {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                    </td>
                    <td class="font-medium text-slate-800">
                        @if ($log->user)
                            <div>{{ $log->user->name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $log->user->role }} · ID #{{ $log->user_id }}</div>
                        @else
                            <span class="text-slate-400">Tamu / Sistem</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $log->document_type === 'ktp' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }} uppercase text-[10px] font-bold">
                            {{ $log->document_type }}
                        </span>
                        <span class="text-slate-400 text-[10px] ml-1">#{{ $log->document_id }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $log->action === 'view' ? 'bg-sky-100 text-sky-800' : ($log->action === 'upload' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }} font-semibold">
                            {{ strtoupper($log->action) }}
                        </span>
                    </td>
                    <td class="font-mono text-slate-600 text-[11px]">
                        {{ $log->ip_address ?? '-' }}
                    </td>
                    <td class="text-slate-500 text-[11px] max-w-xs truncate" title="{{ $log->user_agent }}">
                        {{ $log->user_agent ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-500">
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

