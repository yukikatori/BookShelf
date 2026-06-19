<?php

namespace App\Enums;

enum ReadingPlanStatus: int
{
    case Reading = 1;
    case Completed = 2;
    case Expire = 3;

    public function label(): string
    {
        return match($this) {
            self::Reading => '進行中',
            self::Completed => '読了',
            self::Expire => '期限切れ',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Reading => 'bg-blue-200 text-blue-800',
            self::Completed => 'bg-green-200 text-green-800',
            self::Expire => 'bg-red-200 text-red-800',
        };
    }
}