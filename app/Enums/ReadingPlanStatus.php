<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Reading = 'reading';
    case Completed = 'completed';
    case Expired = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Reading => '進行中',
            self::Completed => '読了',
            self::Expired => '期限切れ',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Reading => 'bg-blue-200 hover:bg-blue-400',
            self::Completed => 'bg-green-200 hover:bg-green-400',
            self::Expired => 'bg-red-200 hover:bg-red-400',
        };
    }
}