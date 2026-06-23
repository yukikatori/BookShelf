<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    public function __construct(
        public string $bookTitle,
        public int $planId,
        public ?string $timing = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $body = match ($this->timing) {
            'three_days_before' => "「{$this->bookTitle}」の読了期限まであと3日です",
            'on_due_date'       => "「{$this->bookTitle}」の読了期限になりました",
            'three_days_after'  => "「{$this->bookTitle}」の読了期限を3日過ぎています",
            default             => "「{$this->bookTitle}」の読書計画に関する通知です",
        };

        return [
            'title'  => '読書計画のリマインダー',
            'body'   => $body,
            'timing' => $this->timing,
        ];
    }
}
