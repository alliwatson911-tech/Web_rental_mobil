<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif Padaidi Rent Car Makassar</title>
    <meta name="description" content="Padaidi Rent Car: Layanan sewa mobil lepas kunci dan dengan sopir di Makassar. Unit terawat, harga transparan, buka 24 jam nonstop.">
    
    {{-- Favicon & Touch Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Open Graph & Social Cards --}}
    <meta property="og:site_name" content="Padaidi Rent Car">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@hasSection('title')@yield('title') · @endif Padaidi Rent Car Makassar">
    <meta property="og:description" content="Layanan sewa mobil terpercaya di Makassar. Lepas kunci & dengan sopir, transparan, bergaransi, 24 jam.">
    <meta property="og:image" content="{{ asset('images/brand/padaidi-og.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/brand/padaidi-og.jpg') }}">

    {{-- Google Font: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    {{-- Asset Stylesheets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
</head>
<body class="theme-padaidi flex min-h-screen flex-col bg-white font-sans text-slate-800 antialiased">
    {{-- Accessibility Skip Link --}}
    <a href="#main-content" class="skip-link">Menuju ke konten utama</a>

    {{-- Sticky Global Navbar --}}
    <x-navbar />

    {{-- Main Page Content --}}
    <main id="main-content" class="flex-1 bg-white" style="position: relative; z-index: 1;">
        @hasSection('hero')
            @yield('hero')
        @endif
        
        <div class="site-container py-8">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    {{-- Global 4-Column Footer --}}
    <x-footer />

    @stack('scripts')
</body>
</html>
