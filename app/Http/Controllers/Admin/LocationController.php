<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        return view('admin.locations.index', ['locations' => Location::withCount('bookings')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('admin.locations.form', ['location' => new Location(['is_active' => true])]);
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Location::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'requires_address' => $request->boolean('requires_address'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.locations.index')->with('success', 'Lokasi disimpan.');
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.form', compact('location'));
    }

    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $data = $request->validated();
        $location->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'requires_address' => $request->boolean('requires_address'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.locations.index')->with('success', 'Lokasi diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->bookings()->exists()) {
            $location->update(['is_active' => false]);

            return back()->with('success', 'Lokasi sudah dipakai di pesanan, jadi hanya dinonaktifkan.');
        }
        $location->delete();

        return back()->with('success', 'Lokasi dihapus.');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Location::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
