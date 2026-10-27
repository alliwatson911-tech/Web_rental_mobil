<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarBlockRequest;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarBlock;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Jadwal servis / mobil tidak tersedia. */
class CarBlockController extends Controller
{
    public function index(Request $request): View
    {
        $blocks = CarBlock::with('car')
            ->when(! $request->boolean('semua'), fn ($q) => $q->where('end_date', '>=', now()->toDateString()))
            ->orderBy('start_date')->paginate(20)->withQueryString();

        return view('admin.blocks.index', compact('blocks'));
    }

    public function create(): View
    {
        return view('admin.blocks.form', ['block' => new CarBlock(['reason' => 'Servis']), 'cars' => Car::orderBy('name')->get()]);
    }

    public function store(CarBlockRequest $request): RedirectResponse
    {
        $block = CarBlock::create($request->validated());

        return redirect()->route('admin.blocks.index')->with('success', 'Jadwal servis disimpan.'.$this->conflictWarning($block));
    }

    public function edit(CarBlock $block): View
    {
        return view('admin.blocks.form', ['block' => $block, 'cars' => Car::orderBy('name')->get()]);
    }

    public function update(CarBlockRequest $request, CarBlock $block): RedirectResponse
    {
        $block->update($request->validated());

        return redirect()->route('admin.blocks.index')->with('success', 'Jadwal servis diperbarui.'.$this->conflictWarning($block));
    }

    public function destroy(CarBlock $block): RedirectResponse
    {
        $block->delete();

        return back()->with('success', 'Jadwal servis dihapus.');
    }

    /** Beri peringatan bila jadwal servis bertabrakan dengan pesanan yang sudah ada. */
    protected function conflictWarning(CarBlock $block): string
    {
        $end = CarbonImmutable::parse($block->end_date)->addDay()->toDateString(); // inklusif -> eksklusif
        $codes = Booking::where('car_id', $block->car_id)
            ->whereIn('status', BookingStatus::blocking())
            ->overlapping($block->start_date->toDateString(), $end)
            ->pluck('code');

        return $codes->isEmpty() ? '' : ' Perhatian: bertabrakan dengan pesanan '.$codes->implode(', ').'.';
    }
}
