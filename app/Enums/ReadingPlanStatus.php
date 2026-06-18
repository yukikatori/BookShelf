<?php

namespace App\Enums;

enum ReadingPlanStatus: int
{
    case Unread = 1;
    case Reading = 2;
    case Completed = 3;
    case Expire = 4;

    public function label(): string
    {
        return match($this) {
            self::Unread => '未読',
            self::Reading => '進行中',
            self::Completed => '読了',
            self::Expire => '期限切れ',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Unread => 'bg-gray-200 text-gray-800',
            self::Reading => 'bg-blue-200 text-blue-800',
            self::Completed => 'bg-green-200 text-green-800',
            self::Expire => 'bg-red-200 text-red-800',
        };
    }
}