<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** Status yang "memegang" mobil sehingga tanggalnya tidak boleh dipesan orang lain. */
    public static function blocking(): array
    {
        return [self::Pending->value, self::Confirmed->value, self::Active->value];
    }

    /** Perpindahan status yang diizinkan. Status lain tidak boleh melompat. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Active, self::Cancelled],
            self::Active => [self::Completed],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu konfirmasi',
            self::Confirmed => 'Dikonfirmasi',
            self::Active => 'Sedang disewa',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Confirmed => 'bg-blue-100 text-blue-800',
            self::Active => 'bg-indigo-100 text-indigo-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-slate-200 text-slate-700',
        };
    }
}
