<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchCarsRequest;
use App\Models\Car;
use App\Models\Category;
use App\Models\Location;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Illuminate\View\View;

class CarController extends Controller
{
    public function __construct(
        protected AvailabilityService $availability,
        protected PricingService $pricing,
    ) {}

    /** Daftar armada + hasil pencarian ketersediaan. */
    public function index(SearchCarsRequest $request): View
    {
        $filters = $request->validated();
        $hasDates = $request->hasDates();

        $query = $hasDates
            ? $this->availability->availableCarsQuery($filters['pickup_date'], $filters['return_date'])
            : Car::query()->where('status', '!=', Car::STATUS_INACTIVE);

        $cars = $query
            ->with(['category', 'primaryPhoto'])
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['transmission'] ?? null, fn ($q, $t) => $q->where('transmission', $t))
            ->when($filters['seats'] ?? null, fn ($q, $s) => $q->where('seats', '>=', (int) $s))
            ->orderBy('daily_price')
            ->get();

        return view('cars.index', [
            'cars' => $cars,
            'filters' => $filters,
            'hasDates' => $hasDates,
            'days' => $hasDates ? $this->pricing->days($filters['pickup_date'], $filters['return_date']) : null,
            'categories' => Category::orderBy('sort_order')->get(),
            'locations' => Location::active()->orderBy('id')->get(),
        ]);
    }

    public function show(SearchCarsRequest $request, Car $car): View
    {
        abort_if($car->status === Car::STATUS_INACTIVE, 404);

        $filters = $request->validated();
        $car->load(['category', 'photos', 'reviews' => fn ($q) => $q->where('is_published', true)->latest()->take(10)]);

        $available = null;
        if ($request->hasDates()) {
            $available = $this->availability->isAvailable($car, $filters['pickup_date'], $filters['return_date']);
        }

        return view('cars.show', [
            'car' => $car,
            'filters' => $filters,
            'available' => $available,
            'locations' => Location::active()->orderBy('id')->get(),
        ]);
    }
}
