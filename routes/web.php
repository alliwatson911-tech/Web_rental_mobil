<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/armada', [CarController::class, 'index'])->name('cars.index');
Route::get('/mobil/{car}', [CarController::class, 'show'])->name('cars.show');
Route::get('/syarat-sewa', [HomeController::class, 'terms'])->name('terms');
Route::get('/kontak', [HomeController::class, 'contact'])->name('contact');

/*
|--------------------------------------------------------------------------
| Pemesanan
|--------------------------------------------------------------------------
*/
Route::get('/pesan/{car}', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/pesan/{car}/ringkasan', [BookingController::class, 'review'])->name('bookings.review');
Route::post('/pesan/{car}', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
Route::get('/cek-pesanan', [BookingController::class, 'lookupForm'])->name('bookings.lookup');
Route::post('/cek-pesanan', [BookingController::class, 'lookup'])->middleware('throttle:10,1')->name('bookings.lookup.submit');
Route::get('/pesanan/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::post('/pesanan/{booking}/pembayaran', [BookingController::class, 'uploadPayment'])->name('bookings.payment');
Route::post('/pesanan/{booking}/ulasan', [BookingController::class, 'storeReview'])->name('bookings.review.store');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('mobil', Admin\CarController::class)->except('show')
            ->parameters(['mobil' => 'car'])->names('cars');
        Route::delete('/mobil/{car}/foto/{photo}', [Admin\CarController::class, 'destroyPhoto'])->name('cars.photos.destroy');
        Route::post('/mobil/{car}/foto/{photo}/utama', [Admin\CarController::class, 'primaryPhoto'])->name('cars.photos.primary');

        Route::resource('tambahan', Admin\AddonController::class)->except('show')
            ->parameters(['tambahan' => 'addon'])->names('addons');
        Route::resource('lokasi', Admin\LocationController::class)->except('show')
            ->parameters(['lokasi' => 'location'])->names('locations');
        Route::resource('servis', Admin\CarBlockController::class)->except('show')
            ->parameters(['servis' => 'block'])->names('blocks');

        Route::get('/pesanan', [Admin\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/pesanan/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');
        Route::post('/pesanan/{booking}/konfirmasi', [Admin\BookingController::class, 'confirm'])->name('bookings.confirm');
        Route::post('/pesanan/{booking}/tolak', [Admin\BookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/pesanan/{booking}/diambil', [Admin\BookingController::class, 'pickup'])->name('bookings.pickup');
        Route::post('/pesanan/{booking}/selesai', [Admin\BookingController::class, 'complete'])->name('bookings.complete');
        Route::post('/pesanan/{booking}/catatan', [Admin\BookingController::class, 'updateNotes'])->name('bookings.notes');

        Route::get('/pembayaran', [Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/pesanan/{booking}/pembayaran', [Admin\PaymentController::class, 'store'])->name('payments.store');
        Route::post('/pembayaran/{payment}/verifikasi', [Admin\PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/pembayaran/{payment}/tolak', [Admin\PaymentController::class, 'reject'])->name('payments.reject');
    });
});
