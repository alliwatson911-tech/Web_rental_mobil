<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Exceptions\CarUnavailableException;
use App\Http\Requests\LookupBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StorePaymentProofRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Location;
use App\Models\Payment;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookings,
        protected PricingService $pricing,
        protected AvailabilityService $availability,
    ) {}

    /** Langkah 1: pilih tanggal, lokasi, tambahan, dan isi data pelanggan. */
    public function create(Request $request, Car $car): View
    {
        abort_if($car->status !== Car::STATUS_AVAILABLE, 404);
        $car->load(['category', 'primaryPhoto']);

        return view('bookings.create', [
            'car' => $car,
            'locations' => Location::active()->orderBy('id')->get(),
            'addons' => Addon::active()->orderBy('id')->get(),
            'prefill' => $request->only(['pickup_location_id', 'pickup_date', 'return_date']),
        ]);
    }

    /** Langkah 2: ringkasan & total (dihitung di server, belum disimpan). */
    public function review(StoreBookingRequest $request, Car $car): View|RedirectResponse
    {
        abort_if($car->status !== Car::STATUS_AVAILABLE, 404);
        $data = $request->validated();

        if (! $this->availability->isAvailable($car, $data['pickup_date'], $data['return_date'])) {
            return back()->withInput()->withErrors(['pickup_date' => (new CarUnavailableException)->getMessage()]);
        }

        $location = Location::findOrFail($data['pickup_location_id']);
        $addons = $this->bookings->resolveAddons($data['addons'] ?? [], $location);
        $quote = $this->pricing->quote($car, $data['pickup_date'], $data['return_date'], $addons);

        return view('bookings.review', [
            'car' => $car->load(['category', 'primaryPhoto']),
            'data' => $data,
            'location' => $location,
            'quote' => $quote,
        ]);
    }

    /** Langkah 3: kirim pesanan. */
    public function store(StoreBookingRequest $request, Car $car): RedirectResponse
    {
        abort_if($car->status !== Car::STATUS_AVAILABLE, 404);

        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;

        try {
            $booking = $this->bookings->create($car, $data);
        } catch (CarUnavailableException $e) {
            return redirect()
                ->route('bookings.create', ['car' => $car, 'pickup_date' => $data['pickup_date'], 'return_date' => $data['return_date']])
                ->withInput()
                ->withErrors(['pickup_date' => $e->getMessage()]);
        }

        $this->grantAccess($request, $booking);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Pesanan berhasil dikirim! Simpan kode pesanan Anda: '.$booking->code);
    }

    /** Halaman status pesanan berdasarkan kode. */
    public function show(Request $request, Booking $booking): View|RedirectResponse
    {
        if (! $this->hasAccess($request, $booking)) {
            return redirect()->route('bookings.lookup', ['code' => $booking->code])
                ->with('info', 'Masukkan nomor HP yang dipakai saat memesan untuk melihat pesanan ini.');
        }

        $booking->load(['car.category', 'car.primaryPhoto', 'pickupLocation', 'addons', 'payments', 'review']);

        return view('bookings.show', ['booking' => $booking]);
    }

    public function lookupForm(Request $request): View
    {
        return view('bookings.lookup', ['code' => $request->query('code')]);
    }

    public function lookup(LookupBookingRequest $request): RedirectResponse
    {
        $code = strtoupper(trim($request->validated('code')));
        $booking = Booking::where('code', $code)->first();

        if (! $booking || ! $this->samePhone($booking->customer_phone, $request->validated('customer_phone'))) {
            return back()->withInput()->withErrors(['code' => 'Pesanan tidak ditemukan. Periksa kembali kode pesanan dan nomor HP.']);
        }

        $this->grantAccess($request, $booking);

        return redirect()->route('bookings.show', $booking);
    }

    /** Pelanggan mengunggah bukti transfer. */
    public function uploadPayment(StorePaymentProofRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($this->hasAccess($request, $booking), 403);

        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed, BookingStatus::Active], true)) {
            return back()->withErrors(['proof' => 'Pembayaran tidak bisa diunggah untuk pesanan berstatus '.$booking->status->label().'.']);
        }

        $path = $request->file('proof')->store('payments', 'public');

        $booking->payments()->create([
            'amount' => $request->validated('amount'),
            'method' => 'transfer',
            'sender_name' => $request->validated('sender_name'),
            'proof_path' => $path,
            'status' => Payment::STATUS_PENDING,
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Bukti transfer terkirim. Admin akan memverifikasi pembayaran Anda.');
    }

    /** Ulasan setelah pesanan selesai. */
    public function storeReview(StoreReviewRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($this->hasAccess($request, $booking), 403);

        if ($booking->status !== BookingStatus::Completed || $booking->review()->exists()) {
            return back()->withErrors(['rating' => 'Ulasan hanya bisa diberikan sekali setelah pesanan selesai.']);
        }

        $booking->review()->create([
            'car_id' => $booking->car_id,
            'customer_name' => $booking->customer_name,
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'is_published' => true,
        ]);

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    protected function grantAccess(Request $request, Booking $booking): void
    {
        $codes = $request->session()->get('booking_access', []);
        $codes[] = $booking->code;
        $request->session()->put('booking_access', array_values(array_unique($codes)));
    }

    protected function hasAccess(Request $request, Booking $booking): bool
    {
        return in_array($booking->code, $request->session()->get('booking_access', []), true)
            || (bool) $request->user()?->isAdmin();
    }

    protected function samePhone(string $a, string $b): bool
    {
        $normalize = function (string $n): string {
            $n = preg_replace('/\D+/', '', $n);

            return str_starts_with($n, '62') ? '0'.substr($n, 2) : $n;
        };

        return $normalize($a) !== '' && $normalize($a) === $normalize($b);
    }
}
