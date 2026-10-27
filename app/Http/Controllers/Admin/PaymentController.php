<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordPaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::with('booking')
            ->when($request->query('status', Payment::STATUS_PENDING), fn ($q, $s) => $s === 'semua' ? $q : $q->where('status', $s))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    /** Admin mencatat pembayaran (tunai/transfer) yang langsung terverifikasi. */
    public function store(RecordPaymentRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();
        $booking->payments()->create([
            'amount' => $data['amount'],
            'method' => $data['method'],
            'sender_name' => $data['sender_name'] ?? null,
            'notes' => $data['notes'] ?? null,
            'paid_at' => $data['paid_at'],
            'proof_path' => $request->file('proof')?->store('payments', 'public'),
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Pembayaran dicatat.');
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $payment->update(['status' => Payment::STATUS_VERIFIED, 'verified_by' => $request->user()->id, 'verified_at' => now()]);

        return back()->with('success', 'Pembayaran diverifikasi.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $notes = $request->validate(['notes' => ['nullable', 'string', 'max:255']], [], ['notes' => 'catatan'])['notes'] ?? null;
        $payment->update(['status' => Payment::STATUS_REJECTED, 'verified_by' => $request->user()->id, 'verified_at' => now(), 'notes' => $notes ?: $payment->notes]);

        return back()->with('success', 'Pembayaran ditandai ditolak.');
    }
}
