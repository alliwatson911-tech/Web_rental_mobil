<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Category;
use App\Models\Location;
use App\Models\Promotion;
use App\Models\PromotionRedemption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    /** Daftar Seluruh Promo & Diskon */
    public function index(Request $request): View
    {
        $promotions = Promotion::with(['category', 'car', 'location'])
            ->withCount('redemptions')
            ->when($request->query('q'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->query('target_type'), function ($q, $targetType) {
                $q->where('target_type', $targetType);
            })
            ->when($request->query('status') !== null && $request->query('status') !== '', function ($q) use ($request) {
                $q->where('is_active', (bool) $request->query('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.promotions.index', compact('promotions'));
    }

    /** Form Tambah Diskon / Promo Baru */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $cars = Car::orderBy('brand')->orderBy('model')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.promotions.create', compact('categories', 'cars', 'locations'));
    }

    /** Simpan Promo Baru */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', 'unique:promotions,code'],
            'target_type' => ['required', 'string', 'in:coupon,first_order,period,tier'],
            'type' => ['required', 'string', 'in:percentage,nominal'],
            'value' => ['required', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'integer', 'min:1'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'min_days' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'is_combinable' => ['boolean'],
            'is_active' => ['boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'car_id' => ['nullable', 'exists:cars,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama promo wajib diisi.',
            'code.unique' => 'Kode promo ini sudah digunakan oleh promo lain.',
            'value.required' => 'Besaran nilai diskon wajib diisi.',
            'ends_at.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        if (! empty($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        } else {
            $data['code'] = null;
        }

        $data['is_combinable'] = $request->boolean('is_combinable', true);
        $data['is_active'] = $request->boolean('is_active', true);

        Promotion::create($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo / Diskon baru berhasil dibuat.');
    }

    /** Form Edit Promo */
    public function edit(Promotion $promotion): View
    {
        $categories = Category::orderBy('name')->get();
        $cars = Car::orderBy('brand')->orderBy('model')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.promotions.edit', compact('promotion', 'categories', 'cars', 'locations'));
    }

    /** Perbarui Promo */
    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', 'unique:promotions,code,' . $promotion->id],
            'target_type' => ['required', 'string', 'in:coupon,first_order,period,tier'],
            'type' => ['required', 'string', 'in:percentage,nominal'],
            'value' => ['required', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'integer', 'min:1'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'min_days' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'is_combinable' => ['boolean'],
            'is_active' => ['boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'car_id' => ['nullable', 'exists:cars,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! empty($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        } else {
            $data['code'] = null;
        }

        $data['is_combinable'] = $request->boolean('is_combinable', true);
        $data['is_active'] = $request->boolean('is_active', true);

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', "Promo {$promotion->name} berhasil diperbarui.");
    }

    /** Hapus Promo */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        $name = $promotion->name;
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', "Promo {$name} berhasil dihapus.");
    }

    /** Laporan Pemakaian Diskon & Promo */
    public function report(Request $request): View
    {
        $query = PromotionRedemption::with(['promotion', 'booking.car', 'user'])
            ->when($request->query('promotion_id'), fn ($q, $id) => $q->where('promotion_id', $id))
            ->when($request->query('start_date'), fn ($q, $start) => $q->whereDate('created_at', '>=', $start))
            ->when($request->query('end_date'), fn ($q, $end) => $q->whereDate('created_at', '<=', $end));

        $totalRedemptions = (clone $query)->count();
        $totalDiscountGiven = (clone $query)->sum('discount_amount');
        $averageDiscount = $totalRedemptions > 0 ? (int) round($totalDiscountGiven / $totalRedemptions) : 0;

        $redemptions = $query->latest('id')->paginate(25)->withQueryString();
        $allPromotions = Promotion::orderBy('name')->get();

        return view('admin.promotions.report', compact(
            'redemptions',
            'allPromotions',
            'totalRedemptions',
            'totalDiscountGiven',
            'averageDiscount'
        ));
    }
}

