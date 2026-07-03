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
            'three_days_before' => "「{$this->bookTitle}」の読了期限が近づいています",
            'on_due_date'       => "「{$this->bookTitle}」の読了期限になりました",
            'three_days_after'  => "「{$this->bookTitle}」の読了期限を3日以上経過しています",
            default             => "「{$this->bookTitle}」に読書計画が設定されています",
        };

        return [
            'title'  => '読書計画のリマインダー',
            'body'   => $body,
            'timing' => $this->timing,
            'planId' => $this->planId,
        ];
    }
}
