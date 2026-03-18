<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Lmromax\LaravelAiSentinel\Models\AiPromptsLog;
use Lmromax\LaravelAiSentinel\Notifications\DailyLimitExceeded;
use Lmromax\LaravelAiSentinel\Notifications\MonthlyLimitExceeded;
use Lmromax\LaravelAiSentinel\Services\AlertService;

beforeEach(function () {
    $this->artisan('migrate');
    Cache::flush();
    Notification::fake();
    $this->service = new AlertService;
});

describe('AlertService', function () {

    it('does not check limits when alerts are disabled', function () {
        config(['ai-sentinel.alerts.enabled' => false]);

        $this->service->checkLimits();

        Notification::assertNothingSent();
    });

    it('sends alert when daily limit is exceeded', function () {
        config([
            'ai-sentinel.alerts.enabled' => true,
            'ai-sentinel.alerts.daily_limit' => 10,
        ]);

        // Create logs that exceed daily limit
        AiPromptsLog::create([
            'provider' => 'openai',
            'model' => 'gpt-4o',
            'prompt' => 'Test',
            'tokens_input' => 1000,
            'tokens_output' => 1000,
            'cost' => 15, // Exceeds $10 limit
        ]);

        $this->service->checkLimits();

        Notification::assertSentTimes(
            DailyLimitExceeded::class,
            1
        );
    });

    it('does not send duplicate daily alerts', function () {
        config([
            'ai-sentinel.alerts.enabled' => true,
            'ai-sentinel.alerts.daily_limit' => 10,
        ]);

        AiPromptsLog::create([
            'provider' => 'openai',
            'model' => 'gpt-4o',
            'prompt' => 'Test',
            'tokens_input' => 1000,
            'tokens_output' => 1000,
            'cost' => 15,
        ]);

        // Check twice
        $this->service->checkLimits();
        $this->service->checkLimits();

        // Should only send once
        Notification::assertSentTimes(
            DailyLimitExceeded::class,
            1
        );
    });

    it('sends alert when monthly limit is exceeded', function () {
        config([
            'ai-sentinel.alerts.enabled' => true,
            'ai-sentinel.alerts.monthly_limit' => 100,
        ]);

        AiPromptsLog::create([
            'provider' => 'openai',
            'model' => 'gpt-4o',
            'prompt' => 'Test',
            'tokens_input' => 10000,
            'tokens_output' => 10000,
            'cost' => 150, // Exceeds $100 limit
        ]);

        $this->service->checkLimits();

        Notification::assertSentTimes(
            MonthlyLimitExceeded::class,
            1
        );
    });

    it('does not send alert when under limit', function () {
        config([
            'ai-sentinel.alerts.enabled' => true,
            'ai-sentinel.alerts.daily_limit' => 100,
        ]);

        AiPromptsLog::create([
            'provider' => 'openai',
            'model' => 'gpt-4o',
            'prompt' => 'Test',
            'tokens_input' => 100,
            'tokens_output' => 100,
            'cost' => 5, // Under $100 limit
        ]);

        $this->service->checkLimits();

        Notification::assertNothingSent();
    });

});
