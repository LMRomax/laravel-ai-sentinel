<?php

use Lmromax\LaravelAiGuard\Facades\AiGuard;
use Lmromax\LaravelAiGuard\Models\AiPromptLog;

beforeEach(function () {
    $this->artisan('migrate');
});

describe('AiGuard Facade', function () {

    it('tracks a request via facade', function () {
        AiGuard::track([
            'provider'      => 'openai',
            'model'         => 'gpt-4o',
            'prompt'        => 'Hello!',
            'response'      => 'Hi!',
            'tokens_input'  => 100,
            'tokens_output' => 50,
        ]);

        expect(AiPromptLog::count())->toBe(1);
    });

    it('optimizes a prompt via facade', function () {
        $result = AiGuard::optimize('Please can you help me understand Laravel?');

        expect($result)->toHaveKeys([
            'original',
            'optimized',
            'tokens_saved',
            'compression_ratio',
        ]);
    });

    it('calculates cost via facade', function () {
        $cost = AiGuard::calculateCost('openai', 'gpt-4o', 1000, 1000);

        expect($cost)->toBe(0.0125);
    });

    it('estimates tokens via facade', function () {
        $tokens = AiGuard::estimateTokens('Hello world');

        expect($tokens)->toBeInt()->toBeGreaterThan(0);
    });

    it('returns cost stats via facade', function () {
        AiGuard::track([
            'provider'      => 'openai',
            'model'         => 'gpt-4o',
            'prompt'        => 'Test',
            'tokens_input'  => 500,
            'tokens_output' => 300,
        ]);

        $stats = AiGuard::getCostStats('day');

        expect($stats)->toHaveKeys([
            'total_requests',
            'total_cost',
            'total_tokens_input',
            'total_tokens_output',
            'avg_cost_per_request',
            'by_provider',
            'by_model',
        ]);

        expect($stats['total_requests'])->toBe(1);
    });

    it('returns total cost via facade', function () {
        AiGuard::track([
            'provider'      => 'openai',
            'model'         => 'gpt-4o',
            'prompt'        => 'Test',
            'tokens_input'  => 1000,
            'tokens_output' => 1000,
        ]);

        $cost = AiGuard::getTotalCost('month');

        expect($cost)->toBeNumeric()->toBeGreaterThan(0);
    });

});