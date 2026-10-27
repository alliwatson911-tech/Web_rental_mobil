@extends('layouts.admin')
@section('title', 'Pembayaran')

@section('content')
@php($current = request('status', 'pending'))
<div class="mb-4 flex flex-wrap gap-2 text-sm">
    @foreach (['pending' => 'Perlu dicek', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak', 'semua' => 'Semua'] as $k => $v)
        <a href="{{ route('admin.payments.index', ['status' => $k]) }}" class="badge px-3 py-1 {{ $current === $k ? 'bg-brand-600 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-300' }}">{{ $v }}</a>
    @endforeach
</div>
<div class="card overflow-x-auto">
    <table class="table-base">
        <thead><tr><th>Tanggal</th><th>Pesanan</th><th>Jumlah</th><th>Pengirim</th><th>Bukti</th><th>Status</th><th></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($payments as $payment)
            <tr>
                <td class="whitespace-nowrap">{{ tanggal($payment->created_at, 'd M Y H:i') }}</td>
                <td><a class="font-semibold text-brand-600 hover:underline" href="{{ route('admin.bookings.show', $payment->booking) }}">{{ $payment->booking->code }}</a><br><span class="text-xs text-slate-500">Total {{ rupiah($payment->booking->total) }}</span></td>
                <td>{{ rupiah($payment->amount) }}</td>
                <td>{{ $payment->sender_name }}</td>
                <td>@if ($payment->proofUrl())<a class="text-brand-600 underline" target="_blank" href="{{ $payment->proofUrl() }}">Lihat</a>@else - @endif</td>
                <td>{{ $payment->statusLabel() }}</td>
                <td class="whitespace-nowrap">
                    @if ($payment->status === 'pending')
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">@csrf<button class="btn btn-success px-2 py-1 text-xs">Verifikasi</button></form>
                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="inline" data-confirm="Tolak bukti pembayaran ini?">@csrf<button class="btn btn-danger px-2 py-1 text-xs">Tolak</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-6 text-center text-slate-500">Tidak ada data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
