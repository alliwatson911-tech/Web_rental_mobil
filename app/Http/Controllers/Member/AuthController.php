<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\RegisterRequest;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('member.auth.register');
    }

    public function register(RegisterRequest $request, MembershipService $membership): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => preg_replace('/\s+/', '', $data['phone']),
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $profile = $user->ensureMemberProfile();
        if (! empty($data['address'])) {
            $profile->update(['address' => $data['address']]);
        }

        // Tembakkan event registered untuk notifikasi verifikasi email bila disetel
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('member.account')
            ->with('success', 'Selamat datang di Padaidi Rental Mobil! Akun keanggotaan Anda telah aktif dengan tier Reguler.');
    }

    public function showLogin(): View
    {
        return view('member.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email atau nomor HP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginInput = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        $key = 'member-login:'.strtolower($loginInput).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->onlyInput('email')->withErrors([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        // Cari berdasarkan email atau nomor telepon
        $user = User::where(function ($q) use ($loginInput) {
            $q->where('email', strtolower($loginInput))
              ->orWhere('phone', $loginInput)
              ->orWhere('phone', preg_replace('/\D+/', '', $loginInput));
        })->first();

        // Jika akun terdaftar sebagai admin, alihkan ke admin login
        if ($user && $user->isAdmin()) {
            RateLimiter::clear($key);
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        // Cek apakah member aktif
        if ($user && $user->memberProfile && ! $user->memberProfile->is_active) {
            return back()->onlyInput('email')->withErrors([
                'email' => 'Akun member Anda telah dinonaktifkan oleh pengelola rental. Silakan hubungi admin.',
            ]);
        }

        $isValid = false;
        if ($user) {
            try {
                if (Hash::check($password, $user->password)) {
                    $isValid = true;
                }
            } catch (\Throwable $e) {
                if (hash_equals((string) $user->password, $password)) {
                    $user->update(['password' => Hash::make($password)]);
                    $isValid = true;
                }
            }
        }

        if (! $isValid) {
            RateLimiter::hit($key, 60);

            return back()->onlyInput('email')->withErrors(['email' => 'Email/No. HP atau kata sandi tidak cocok.']);
        }

        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('member.account'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah berhasil keluar dari akun member.');
    }

    public function showForgotPassword(): View
    {
        return view('member.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Tautan pemulihan kata sandi telah dikirim ke email Anda.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('member.auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk.')
            : back()->withErrors(['email' => __($status)]);
    }

    /** Halaman Verifikasi Kontak */
    public function showVerifyContact(Request $request): View
    {
        $user = $request->user();

        return view('member.auth.verify-contact', compact('user'));
    }

    /** Simulasi verifikasi WhatsApp OTP */
    public function verifyWhatsapp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Masukkan 6-digit kode verifikasi.',
            'otp.size' => 'Kode OTP harus berupa 6 karakter.',
        ]);

        $user = $request->user();
        $profile = $user->ensureMemberProfile();
        $profile->update(['phone_verified_at' => now()]);

        return redirect()->route('member.account')->with('success', 'Nomor WhatsApp berhasil diverifikasi!');
    }
}

