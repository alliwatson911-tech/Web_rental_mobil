<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarRequest;
use App\Models\Car;
use App\Models\CarPhoto;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CarController extends Controller
{
    public function index(Request $request): View
    {
        $cars = Car::with(['category', 'primaryPhoto'])
            ->when($request->query('q'), fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('plate_number', 'like', "%{$s}%")))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.cars.index', compact('cars'));
    }

    public function create(): View
    {
        return view('admin.cars.form', ['car' => new Car(['status' => Car::STATUS_AVAILABLE, 'seats' => 5, 'fuel' => 'Bensin', 'transmission' => 'manual']), 'categories' => Category::orderBy('sort_order')->get()]);
    }

    public function store(CarRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('photos')->all();
        $data['slug'] = $this->uniqueSlug($data['name'].' '.$data['plate_number']);
        $car = Car::create($data);
        $this->storePhotos($request, $car);

        return redirect()->route('admin.cars.edit', $car)->with('success', 'Mobil berhasil ditambahkan.');
    }

    public function edit(Car $car): View
    {
        $car->load('photos');

        return view('admin.cars.form', ['car' => $car, 'categories' => Category::orderBy('sort_order')->get()]);
    }

    public function update(CarRequest $request, Car $car): RedirectResponse
    {
        $car->update(collect($request->validated())->except('photos')->all());
        $this->storePhotos($request, $car);

        return redirect()->route('admin.cars.edit', $car)->with('success', 'Data mobil diperbarui.');
    }

    public function destroy(Car $car): RedirectResponse
    {
        if ($car->bookings()->exists()) {
            return back()->withErrors(['car' => 'Mobil sudah punya riwayat pesanan sehingga tidak bisa dihapus. Ubah statusnya menjadi "Tidak aktif".']);
        }

        foreach ($car->photos()->get() as $photo) {
            Storage::disk('public')->delete($photo->path);
            $webpPath = pathinfo($photo->path, PATHINFO_DIRNAME) . '/' . pathinfo($photo->path, PATHINFO_FILENAME) . '.webp';
            if (Storage::disk('public')->exists($webpPath)) {
                Storage::disk('public')->delete($webpPath);
            }
        }
        $car->delete();

        return redirect()->route('admin.cars.index')->with('success', 'Mobil dihapus.');
    }

    public function destroyPhoto(Car $car, CarPhoto $photo): RedirectResponse
    {
        abort_unless($photo->car_id === $car->id, 404);
        Storage::disk('public')->delete($photo->path);
        $webpPath = pathinfo($photo->path, PATHINFO_DIRNAME) . '/' . pathinfo($photo->path, PATHINFO_FILENAME) . '.webp';
        if (Storage::disk('public')->exists($webpPath)) {
            Storage::disk('public')->delete($webpPath);
        }
        $wasPrimary = $photo->is_primary;
        $photo->delete();

        if ($wasPrimary && ($next = $car->photos()->first())) {
            $next->update(['is_primary' => true]);
        }

        return back()->with('success', 'Foto dihapus.');
    }

    public function primaryPhoto(Car $car, CarPhoto $photo): RedirectResponse
    {
        abort_unless($photo->car_id === $car->id, 404);
        $car->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        return back()->with('success', 'Foto utama diganti.');
    }

    protected function storePhotos(CarRequest $request, Car $car): void
    {
        $hasPrimary = $car->photos()->where('is_primary', true)->exists();
        $order = (int) $car->photos()->max('sort_order');

        foreach ($request->file('photos', []) as $file) {
            $path = $file->store('cars', 'public');
            $this->generateWebpVersion($path);

            $car->photos()->create([
                'path' => $path,
                'is_primary' => ! $hasPrimary,
                'sort_order' => ++$order,
            ]);
            $hasPrimary = true;
        }
    }

    protected function generateWebpVersion(string $path): void
    {
        $fullPath = Storage::disk('public')->path($path);
        if (!file_exists($fullPath) || !extension_loaded('gd')) {
            return;
        }

        $info = @getimagesize($fullPath);
        if (!$info) return;

        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'];

        if ($mime === 'image/jpeg') {
            $srcImg = @imagecreatefromjpeg($fullPath);
        } elseif ($mime === 'image/png') {
            $srcImg = @imagecreatefrompng($fullPath);
        } else {
            return;
        }

        if (!$srcImg) return;

        $maxWidth = 720;
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round(($height / $width) * $newWidth);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $destImg = imagecreatetruecolor($newWidth, $newHeight);
        if ($mime === 'image/png') {
            imagealphablending($destImg, false);
            imagesavealpha($destImg, true);
        }
        imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $destRel = ($dir === '.' ? '' : $dir . '/') . $filename . '.webp';
        $destFull = Storage::disk('public')->path($destRel);

        @imagewebp($destImg, $destFull, 82);

        imagedestroy($srcImg);
        imagedestroy($destImg);
    }

    protected function uniqueSlug(string $text): string
    {
        $base = Str::slug($text);
        $slug = $base;
        $i = 2;
        while (Car::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
