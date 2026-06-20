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
        self::Reading => 'badge-reading',
        self::Completed => 'badge-completed',
        self::Expire => 'badge-expire',
    };
    }
}