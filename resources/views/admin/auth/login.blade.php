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
<div class="card w-full max-w-sm p-6">
    <h1 class="text-xl font-extrabold text-slate-900">Masuk Admin Padaidi</h1>
    <p class="mt-1 text-sm text-slate-500">Khusus pengelola rental.</p>
    <div class="mt-4">@include('partials.flash')</div>
    <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-3">
        @csrf
        <div>
            <label class="label" for="email">Email</label>
            <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div>
            <label class="label" for="password">Kata sandi</label>
            <input class="input" type="password" id="password" name="password" required>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
        <button class="btn btn-primary w-full">Masuk</button>
    </form>
    <a href="{{ route('home') }}" class="mt-4 block text-center text-sm text-brand-600 hover:underline">← Kembali ke situs</a>
</div>
</body>
</html>
