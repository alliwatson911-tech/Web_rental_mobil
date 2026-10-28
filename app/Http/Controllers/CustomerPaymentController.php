<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /** Halaman Pemilihan Opsi dan Metode Pembayaran Online */
    public function checkout(Request $request, Booking $booking): View|RedirectResponse
    {
        abort_unless($this->hasAccess($request, $booking), 403);

        if ($booking->isPaidOff()) {
            return redirect()->route('bookings.show', $booking)
                ->with('info', 'Pesanan Anda sudah lunas.');
        }

        $booking->load(['car', 'pickupLocation', 'addons']);
        $dpAmount = (int) round($booking->grandTotal() * (config('payment.dp_percentage', 30) / 100));
        $remaining = $booking->remainingAmount();

        return view('bookings.pay', compact('booking', 'dpAmount', 'remaining'));
    }

    /** Proses Pembuatan Tagihan Gateway */
    public function process(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($this->hasAccess($request, $booking), 403);

        $request->validate([
            'payment_type' => ['required', 'string', 'in:full,dp,settlement'],
            'channel' => ['required', 'string', 'in:qris,va_bca,va_bni,va_bri,va_mandiri,va_permata'],
        ]);

        try {
            $payment = $this->paymentService->createPayment(
                $booking,
                $request->input('payment_type'),
                $request->input('channel')
            );

            return redirect()->route('bookings.pay.instruction', [$booking, $payment])
                ->with('success', 'Tagihan pembayaran berhasil dibuat. Silakan lakukan pembayaran.');
        } catch (\Throwable $e) {
            return back()->withErrors(['channel' => $e->getMessage()]);
        }
    }

    /** Halaman Instruksi Pembayaran (QRIS / Virtual Account) */
    public function instruction(Request $request, Booking $booking, Payment $payment): View
    {
        abort_unless($this->hasAccess($request, $booking), 403);
        abort_unless($payment->booking_id === $booking->id, 404);

        $booking->load(['car', 'pickupLocation']);

        return view('bookings.payment-instruction', compact('booking', 'payment'));
    }

    /** Redirect dari URL Checkout Referensi */
    public function checkoutRef(Request $request, Booking $booking, string $ref): RedirectResponse
    {
        abort_unless($this->hasAccess($request, $booking), 403);
        $payment = Payment::where('reference_id', $ref)->firstOrFail();

        return redirect()->route('bookings.pay.instruction', [$booking, $payment]);
    }

    /** Halaman Cetak Invoice Resmi Sewa Mobil */
    public function invoice(Request $request, Booking $booking): View
    {
        // Admin atau pemilik pesanan berhak mencetak invoice
        $user = $request->user();
        $isAuthorized = ($user && $user->isAdmin()) || $this->hasAccess($request, $booking);
        abort_unless($isAuthorized, 403);

        $booking->load(['car.category', 'pickupLocation', 'addons', 'payments', 'member']);

        return view('bookings.invoice', compact('booking'));
    }

    protected function hasAccess(Request $request, Booking $booking): bool
    {
        if ($request->user() && $request->user()->isAdmin()) {
            return true;
        }

        if ($request->user() && $request->user()->id === $booking->member_id) {
            return true;
        }

        $sessionKeys = $request->session()->get('booking_access', []);

        return in_array($booking->code, $sessionKeys, true);
    }
}
