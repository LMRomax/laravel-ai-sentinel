<?php

namespace Lmromax\LaravelAiGuard\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Lmromax\LaravelAiGuard\Models\AiPromptsLog;
use Lmromax\LaravelAiGuard\Notifications\DailyLimitExceeded;
use Lmromax\LaravelAiGuard\Notifications\MonthlyLimitExceeded;

class AlertService
{
    /**
     * Check if spending limits are exceeded and send alerts
     */
    public function checkLimits(): void
    {
        if (! config('ai-guard.alerts.enabled', true)) {
            return;
        }

        $this->checkDailyLimit();
        $this->checkMonthlyLimit();
    }

    /**
     * Check daily spending limit
     */
    protected function checkDailyLimit(): void
    {
        $dailyLimit = config('ai-guard.alerts.daily_limit');

        if (! $dailyLimit) {
            return;
        }

        // Get today's total cost
        $todayCost = AiPromptsLog::where('created_at', '>=', now()->startOfDay())
            ->sum('cost');

        // Check if limit exceeded and not already notified today
        if ($todayCost > $dailyLimit && ! $this->wasNotifiedToday('daily')) {
            $this->sendAlert(new DailyLimitExceeded($todayCost, $dailyLimit));
            $this->markAsNotified('daily');
        }
    }

    /**
     * Check monthly spending limit
     */
    protected function checkMonthlyLimit(): void
    {
        $monthlyLimit = config('ai-guard.alerts.monthly_limit');

        if (! $monthlyLimit) {
            return;
        }

        // Get this month's total cost
        $monthCost = AiPromptsLog::where('created_at', '>=', now()->startOfMonth())
            ->sum('cost');

        // Check if limit exceeded and not already notified this month
        if ($monthCost > $monthlyLimit && ! $this->wasNotifiedThisMonth()) {
            $this->sendAlert(new MonthlyLimitExceeded($monthCost, $monthlyLimit));
            $this->markAsNotified('monthly');
        }
    }

    /**
     * Send alert notification
     * 
     * @param \Illuminate\Notifications\Notification $notification The notification instance to send
     */
    protected function sendAlert($notification): void
    {
        $channels = config('ai-guard.alerts.channels', ['mail']);
        $recipients = config('ai-guard.alerts.recipients', []);

        if (empty($recipients)) {
            // Fallback: notify all admins or first user
            $recipients = $this->getDefaultRecipients();
        }

        Notification::route('mail', $recipients)
            ->notify($notification);
    }

    /**
     * Get default notification recipients
     * 
     * @return array An array of email addresses to receive alerts
     */
    protected function getDefaultRecipients(): array
    {
        // Try to find admin users or fallback to first user
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');

        if (class_exists($userModel)) {
            $user = $userModel::first();

            return $user ? [$user->email] : [];
        }

        return [];
    }

    /**
     * Check if daily alert was already sent today
     * 
     * @param string $type The alert type (e.g. 'daily' or 'monthly')
     * @return bool True if alert was already sent today, false otherwise
     */
    protected function wasNotifiedToday(string $type): bool
    {
        return Cache::has("ai-guard-alert-{$type}-".now()->format('Y-m-d'));
    }

    /**
     * Check if monthly alert was already sent this month
     * 
     * @return bool True if alert was already sent this month, false otherwise
     */
    protected function wasNotifiedThisMonth(): bool
    {
        return Cache::has('ai-guard-alert-monthly-'.now()->format('Y-m'));
    }

    /**
     * Mark alert as sent
     * 
     * @param string $type The alert type (e.g. 'daily' or 'monthly')
     */
    protected function markAsNotified(string $type): void
    {
        $key = match ($type) {
            'daily' => 'ai-guard-alert-daily-'.now()->format('Y-m-d'),
            'monthly' => 'ai-guard-alert-monthly-'.now()->format('Y-m'),
            default => 'ai-guard-alert-'.$type,
        };

        // Cache for 24 hours (daily) or 30 days (monthly)
        $ttl = $type === 'daily' ? now()->endOfDay() : now()->endOfMonth();

        Cache::put($key, true, $ttl);
    }
}
