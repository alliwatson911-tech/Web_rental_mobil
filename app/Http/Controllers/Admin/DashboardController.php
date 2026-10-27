<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        return view('admin.dashboard', [
            'counts' => Booking::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'carCount' => Car::count(),
            'pendingPayments' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'pickupsToday' => Booking::with(['car', 'pickupLocation'])
                ->where('status', BookingStatus::Confirmed->value)->where('pickup_date', $today)->get(),
            'returnsToday' => Booking::with('car')
                ->where('status', BookingStatus::Active->value)->where('return_date', '<=', $today)->get(),
            'latest' => Booking::with('car')->latest()->take(8)->get(),
        ]);
    }
}
