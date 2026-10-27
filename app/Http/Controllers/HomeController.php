<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Car;
use App\Models\Category;
use App\Models\Location;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'locations' => Location::active()->orderBy('id')->get(),
            'categories' => Category::orderBy('sort_order')->withCount(['cars' => fn ($q) => $q->where('status', Car::STATUS_AVAILABLE)])->get(),
            'featuredCars' => Car::with(['category', 'primaryPhoto'])->where('status', Car::STATUS_AVAILABLE)->orderBy('daily_price')->take(6)->get(),
        ]);
    }

    public function terms(): View
    {
        return view('pages.terms', [
            'terms' => config('padaidi.terms'),
            'addons' => Addon::active()->orderBy('id')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact');
    }
}
