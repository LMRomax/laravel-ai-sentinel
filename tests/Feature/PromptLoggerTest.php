<?php

use Lmromax\LaravelAiGuard\Models\AiPromptLog;
use Lmromax\LaravelAiGuard\Services\PromptLogger;

beforeEach(function () {
    $this->logger = new PromptLogger();

    // Run migrations
    $this->artisan('migrate');
});

describe('PromptLogger', function () {

    it('logs an ai request to the database', function () {
        $this->logger->log([
            'provider'       => 'openai',
            'model'          => 'gpt-4o',
            'prompt'         => 'Hello, world!',
            'response'       => 'Hi there!',
            'tokens_input'   => 100,
            'tokens_output'  => 50,
            'duration_ms'    => 800,
        ]);

        expect(AiPromptLog::count())->toBe(1);

        $log = AiPromptLog::first();
        expect($log->provider)->toBe('openai');
        expect($log->model)->toBe('gpt-4o');
        expect($log->tokens_input)->toBe(100);
        expect($log->tokens_output)->toBe(50);
        expect($log->cost)->toBeNumeric()->toBeGreaterThan(0);
    });

    it('calculates cost when logging', function () {
        $this->logger->log([
            'provider'      => 'openai',
            'model'         => 'gpt-4o',
            'prompt'        => 'Test',
            'tokens_input'  => 1000,
            'tokens_output' => 1000,
        ]);

        $log = AiPromptLog::first();

        // gpt-4o: input=0.0025, output=0.01 → total=0.0125
        expect((float) $log->cost)->toBe(0.0125);
    });

    it('does not log when package is disabled', function () {
        config(['ai-guard.enabled' => false]);

        $this->logger->log([
            'provider'      => 'openai',
            'model'         => 'gpt-4o',
            'prompt'        => 'Test',
            'tokens_input'  => 100,
            'tokens_output' => 50,
        ]);

        expect(AiPromptLog::count())->toBe(0);
    });

    it('returns stats for a given period', function () {
        // Crée 3 logs
        for ($i = 0; $i < 3; $i++) {
            $this->logger->log([
                'provider'      => 'openai',
                'model'         => 'gpt-4o',
                'prompt'        => 'Test ' . $i,
                'tokens_input'  => 100,
                'tokens_output' => 50,
            ]);
        }

        $stats = $this->logger->getStats('day');

        expect($stats['total_requests'])->toBe(3);
        expect($stats['total_cost'])->toBeNumeric()->toBeGreaterThan(0);
        expect($stats['by_provider'])->toHaveKey('openai');
    });

    it('stores metadata correctly', function () {
        $this->logger->log([
            'provider'      => 'anthropic',
            'model'         => 'claude-3-5-sonnet-20241022',
            'prompt'        => 'Test',
            'tokens_input'  => 100,
            'tokens_output' => 50,
            'metadata'      => ['source' => 'test', 'tokens_saved' => 10],
        ]);

        $log = AiPromptLog::first();

        expect($log->metadata)->toBeArray();
        expect($log->metadata['source'])->toBe('test');
        expect($log->metadata['tokens_saved'])->toBe(10);
    });

});