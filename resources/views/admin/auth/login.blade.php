<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Masuk Admin · Padaidi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-gradient-to-br from-brand-900 to-brand-600 p-4 font-sans">
<div class="card w-full max-w-sm p-6 shadow-xl">
    <h1 class="text-xl font-extrabold text-slate-900">Masuk Admin Padaidi</h1>
    <p class="mt-1 text-sm text-slate-500">Khusus pengelola rental armada.</p>
    <div class="mt-4">@include('partials.flash')</div>
    <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-3">
        @csrf
        <div>
            <label class="label" for="email">Email</label>
            <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@padaidi.test">
        </div>
        <div>
            <label class="label" for="password">Kata sandi</label>
            <div class="relative">
                <input class="input pr-10" type="password" id="password" name="password" required placeholder="••••••••">
                <button type="button" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600" title="Tampilkan/sembunyikan kata sandi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
        <button class="btn btn-primary w-full py-2.5 font-semibold">Masuk ke Dasbor</button>
    </form>

    <div class="mt-4 rounded-lg bg-slate-50 border border-slate-200 p-3 text-xs text-slate-600">
        <p class="font-semibold text-slate-700">Akun Admin Tersedia:</p>
        <ul class="mt-1 list-disc pl-4 space-y-0.5 text-slate-600">
            <li><code>admin@padaidi.test</code> (Admin 1)</li>
            <li><code>admin2@padaidi.test</code> (Admin 2)</li>
        </ul>
        <p class="mt-1.5 text-slate-500">Kata sandi: <code>admin123</code> (atau <code>padaidi-admin-123</code>)</p>
    </div>

    <a href="{{ route('home') }}" class="mt-4 block text-center text-sm text-brand-600 hover:underline">← Kembali ke situs utama</a>
</div>
</body>
</html>
