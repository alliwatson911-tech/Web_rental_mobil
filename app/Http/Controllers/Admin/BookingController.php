<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(protected BookingService $service) {}

    public function index(Request $request): View
    {
        $request->validate(['status' => ['nullable', Rule::enum(BookingStatus::class)]]);

        $bookings = Booking::with(['car', 'pickupLocation'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $s) => $q->where(fn ($w) => $w->where('code', 'like', "%{$s}%")
                ->orWhere('customer_name', 'like', "%{$s}%")->orWhere('customer_phone', 'like', "%{$s}%")))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.bookings.index', ['bookings' => $bookings]);
    }

    public function show(Booking $booking): View
    {
        $booking->load(['car.category', 'pickupLocation', 'addons', 'payments.verifier', 'review']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        return $this->move($booking, BookingStatus::Confirmed, 'Pesanan dikonfirmasi.');
    }

    public function reject(CancelBookingRequest $request, Booking $booking): RedirectResponse
    {
        return $this->move($booking, BookingStatus::Cancelled, 'Pesanan dibatalkan/ditolak.', $request->validated('reason') ?: 'Ditolak admin');
    }

    public function pickup(Booking $booking): RedirectResponse
    {
        return $this->move($booking, BookingStatus::Active, 'Mobil ditandai sudah diambil.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        return $this->move($booking, BookingStatus::Completed, 'Pesanan ditandai selesai.');
    }

    public function updateNotes(Request $request, Booking $booking): RedirectResponse
    {
        $booking->update($request->validate(['admin_notes' => ['nullable', 'string', 'max:2000']], [], ['admin_notes' => 'catatan admin']));

        return back()->with('success', 'Catatan disimpan.');
    }

    protected function move(Booking $booking, BookingStatus $target, string $message, ?string $reason = null): RedirectResponse
    {
        try {
            $this->service->transition($booking, $target, $reason);
        } catch (InvalidStatusTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', $message);
    }
}
