<?php

namespace Lmromax\LaravelAiSentinel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyLimitExceeded extends Notification
{
    use Queueable;

    public function __construct(
        public float $currentCost,
        public float $limit
    ) {}

    public function via($notifiable): array
    {
        return config('ai-sentinel.alerts.channels', ['mail']);
    }

    public function toMail($notifiable): MailMessage
    {
        $percentage = ($this->currentCost / $this->limit) * 100;

        return (new MailMessage)
            ->error()
            ->subject('⚠️ AI Guard: Daily Spending Limit Exceeded')
            ->greeting('Daily Spending Alert!')
            ->line('Your AI spending today has exceeded the daily limit.')
            ->line('**Current spending:** $'.number_format($this->currentCost, 2))
            ->line('**Daily limit:** $'.number_format($this->limit, 2))
            ->line('**Over budget:** '.number_format($percentage - 100, 1).'%')
            ->action('View Dashboard', url('/ai-sentinel'))
            ->line('Consider optimizing your prompts or pausing AI features temporarily.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'daily_limit_exceeded',
            'current_cost' => $this->currentCost,
            'limit' => $this->limit,
            'percentage' => ($this->currentCost / $this->limit) * 100,
        ];
    }
}
