<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'padaidi:create-admin 
                            {email=admin2@padaidi.test : Alamat email admin}
                            {--name=Admin Padaidi 2 (Operasional) : Nama admin}
                            {--phone=6282235585445 : Nomor HP WhatsApp admin}
                            {--password=padaidi-admin-123 : Kata sandi admin}';

    protected $description = 'Membuat atau memperbarui akun administrator Padaidi';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name');
        $phone = $this->option('phone');
        $password = $this->option('password');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->info("Akun admin berhasil dibuat/diperbarui:");
        $this->line(" - ID       : {$admin->id}");
        $this->line(" - Nama     : {$admin->name}");
        $this->line(" - Email    : {$admin->email}");
        $this->line(" - HP       : {$admin->phone}");
        $this->line(" - Role     : {$admin->role}");

        return self::SUCCESS;
    }
}

