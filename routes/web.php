<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Member;
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
Route::post('/cek-pesanan', [BookingController::class, 'lookup'])->middleware('throttle:5,1')->name('bookings.lookup.submit');
Route::get('/pesanan/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::get('/pesanan/{booking}/bayar', [CustomerPaymentController::class, 'checkout'])->name('bookings.pay');
Route::post('/pesanan/{booking}/bayar', [CustomerPaymentController::class, 'process'])->name('bookings.pay.process');
Route::get('/pesanan/{booking}/bayar/{payment}', [CustomerPaymentController::class, 'instruction'])->name('bookings.pay.instruction');
Route::get('/pembayaran/checkout/{ref}', [CustomerPaymentController::class, 'checkoutRef'])->name('bookings.pay.checkout');
Route::get('/pesanan/{booking}/invoice', [CustomerPaymentController::class, 'invoice'])->name('bookings.invoice');
Route::post('/pesanan/{booking}/pembayaran', [BookingController::class, 'uploadPayment'])->name('bookings.payment');
Route::get('/pesanan/{booking}/pembayaran/{payment}/bukti', [BookingController::class, 'showPaymentProof'])->name('bookings.payment.proof');
Route::post('/pesanan/{booking}/dokumen', [BookingController::class, 'uploadDocument'])->name('bookings.document');
Route::get('/pesanan/{booking}/dokumen/{document}', [BookingController::class, 'showDocument'])->name('bookings.document.show');
Route::post('/pesanan/{booking}/ulasan', [BookingController::class, 'storeReview'])->name('bookings.review.store');

// Webhook Payment Gateway (CSRF exempt via bootstrap/app.php)
Route::post('/api/payment/webhook', [PaymentWebhookController::class, 'handle'])->name('payment.webhook');
Route::post('/webhook/payment', [PaymentWebhookController::class, 'handle'])->name('payment.webhook.alt');

/*
|--------------------------------------------------------------------------
| Autentikasi & Akun Member Pelanggan
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [Member\AuthController::class, 'showRegister'])->name('register');
    Route::get('/member/register', [Member\AuthController::class, 'showRegister'])->name('member.register');
    Route::post('/register', [Member\AuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [Member\AuthController::class, 'showLogin'])->name('login');
    Route::get('/member/login', [Member\AuthController::class, 'showLogin'])->name('member.login');
    Route::post('/login', [Member\AuthController::class, 'login'])->name('login.submit');
    Route::get('/forgot-password', [Member\AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [Member\AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [Member\AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [Member\AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [Member\AuthController::class, 'logout'])->name('logout');
    Route::post('/member/logout', [Member\AuthController::class, 'logout'])->name('member.logout');

    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/akun', [Member\AccountController::class, 'index'])->name('account');
        Route::get('/dashboard', [Member\AccountController::class, 'index'])->name('dashboard');
        Route::get('/profil', [Member\AccountController::class, 'showProfile'])->name('profile');
        Route::put('/profil', [Member\AccountController::class, 'updateProfile'])->name('profile.update');
        Route::get('/dokumen', [Member\AccountController::class, 'showDocuments'])->name('documents');
        Route::post('/dokumen', [Member\AccountController::class, 'uploadDocument'])->name('documents.upload');
        Route::get('/dokumen/{document}', [Member\AccountController::class, 'viewDocument'])->name('documents.view');
        Route::get('/pesanan', [Member\AccountController::class, 'bookings'])->name('bookings');
        Route::get('/verifikasi-kontak', [Member\AuthController::class, 'showVerifyContact'])->name('verify.contact');
        Route::post('/verifikasi-wa', [Member\AuthController::class, 'verifyWhatsapp'])->name('verify.wa');
    });
});

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

        // Manajemen Member
        Route::get('/member', [Admin\MemberController::class, 'index'])->name('members.index');
        Route::get('/member/{member}', [Admin\MemberController::class, 'show'])->name('members.show');
        Route::post('/member/{member}/tier', [Admin\MemberController::class, 'updateTier'])->name('members.tier.update');
        Route::post('/member/{member}/tier-reset', [Admin\MemberController::class, 'resetTier'])->name('members.tier.reset');
        Route::post('/member/{member}/status', [Admin\MemberController::class, 'toggleStatus'])->name('members.status.toggle');
        Route::post('/member/{member}/dokumen/{document}/verifikasi', [Admin\MemberController::class, 'verifyDocument'])->name('members.documents.verify');
        Route::post('/member/{member}/dokumen/{document}/tolak', [Admin\MemberController::class, 'rejectDocument'])->name('members.documents.reject');
        Route::get('/log-dokumen', [Admin\MemberController::class, 'auditLogs'])->name('members.logs');

        // Manajemen Diskon & Promosi
        Route::get('/promosi/laporan', [Admin\PromotionController::class, 'report'])->name('promotions.report');
        Route::resource('promosi', Admin\PromotionController::class)
            ->parameters(['promosi' => 'promotion'])->names('promotions');

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
        Route::post('/dokumen/{document}/verifikasi', [Admin\BookingController::class, 'verifyDocument'])->name('documents.verify');
        Route::post('/dokumen/{document}/tolak', [Admin\BookingController::class, 'rejectDocument'])->name('documents.reject');

        Route::get('/pembayaran/export', [Admin\PaymentController::class, 'export'])->name('payments.export');
        Route::get('/pembayaran', [Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/pesanan/{booking}/pembayaran', [Admin\PaymentController::class, 'store'])->name('payments.store');
        Route::post('/pembayaran/{payment}/verifikasi', [Admin\PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/pembayaran/{payment}/tolak', [Admin\PaymentController::class, 'reject'])->name('payments.reject');
        Route::post('/pembayaran/{payment}/refund', [Admin\PaymentController::class, 'refund'])->name('payments.refund');
    });
});
