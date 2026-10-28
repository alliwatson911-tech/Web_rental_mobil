<?php

use Illuminate\Support\Facades\Schedule;

// Batalkan otomatis pesanan pending yang melewati batas waktu (lihat config/padaidi.php).
// Jalankan scheduler di server: * * * * * php /path/ke/app/artisan schedule:run
Schedule::command('bookings:cancel-expired')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('payments:expire')->hourly()->withoutOverlapping();
Schedule::command('payments:reconcile')->everyFifteenMinutes()->withoutOverlapping();
