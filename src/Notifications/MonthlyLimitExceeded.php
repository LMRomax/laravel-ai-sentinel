<?php

namespace Lmromax\LaravelAiGuard\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyLimitExceeded extends Notification
{
    use Queueable;

    public function __construct(
        public float $currentCost,
        public float $limit
    ) {}

    public function via($notifiable): array
    {
        return config('ai-guard.alerts.channels', ['mail']);
    }

    public function toMail($notifiable): MailMessage
    {
        $percentage = ($this->currentCost / $this->limit) * 100;

        return (new MailMessage)
            ->error()
            ->subject('🚨 AI Guard: Monthly Spending Limit Exceeded')
            ->greeting('Monthly Spending Alert!')
            ->line('Your AI spending this month has exceeded the monthly limit.')
            ->line('**Current spending:** $'.number_format($this->currentCost, 2))
            ->line('**Monthly limit:** $'.number_format($this->limit, 2))
            ->line('**Over budget:** '.number_format($percentage - 100, 1).'%')
            ->action('View Dashboard', url('/ai-guard'))
            ->line('Please review your AI usage and consider upgrading your plan or optimizing your prompts.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'monthly_limit_exceeded',
            'current_cost' => $this->currentCost,
            'limit' => $this->limit,
            'percentage' => ($this->currentCost / $this->limit) * 100,
        ];
    }
}
