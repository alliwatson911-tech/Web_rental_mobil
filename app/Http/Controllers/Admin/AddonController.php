<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddonRequest;
use App\Models\Addon;
use App\Models\BookingAddon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function index(): View
    {
        return view('admin.addons.index', ['addons' => Addon::orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('admin.addons.form', ['addon' => new Addon(['charge_type' => Addon::PER_DAY, 'is_active' => true])]);
    }

    public function store(AddonRequest $request): RedirectResponse
    {
        Addon::create($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.addons.index')->with('success', 'Tambahan disimpan.');
    }

    public function edit(Addon $addon): View
    {
        return view('admin.addons.form', compact('addon'));
    }

    public function update(AddonRequest $request, Addon $addon): RedirectResponse
    {
        $addon->update(['is_active' => $request->boolean('is_active')] + $request->validated());

        return redirect()->route('admin.addons.index')->with('success', 'Tambahan diperbarui. Pesanan lama tetap memakai harga lama.');
    }

    public function destroy(Addon $addon): RedirectResponse
    {
        if (BookingAddon::where('addon_id', $addon->id)->exists()) {
            $addon->update(['is_active' => false]);

            return back()->with('success', 'Tambahan sudah dipakai di pesanan, jadi hanya dinonaktifkan.');
        }
        $addon->delete();

        return back()->with('success', 'Tambahan dihapus.');
    }
}
