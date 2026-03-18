<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lmromax\LaravelAiSentinel\Services\PricingSyncService;

beforeEach(function () {
    Cache::flush();
    $this->service = new PricingSyncService;
});

describe('PricingSyncService', function () {

    it('fetches and caches pricing', function () {

        $mockData = [
            'last_updated' => '2025-01-01',
            'version' => '1.0.0',
            'unit' => 'per_1k_tokens',
            'providers' => [
                'openai' => [
                    'gpt-4o' => ['input' => 0.0025, 'output' => 0.01],
                ],
            ],
        ];

        Http::fake([
            '*' => Http::response($mockData, 200),
        ]);

        // First call hits HTTP
        $pricing1 = $this->service->getPricing();

        expect($pricing1)->toBe($mockData);

        // Second call uses cache
        $pricing2 = $this->service->getPricing();

        expect($pricing2)->toBe($mockData);
        expect($this->service->isCached())->toBeTrue();
    });

    it('returns specific model pricing from cache', function () {

        $mockData = [
            'providers' => [
                'openai' => [
                    'gpt-4o' => ['input' => 0.0025, 'output' => 0.01],
                ],
            ],
        ];

        Cache::put('ai_guard_pricing', $mockData, now()->addDay());

        $modelPricing = $this->service->getModelPricing('openai', 'gpt-4o');

        expect($modelPricing)->toBe(['input' => 0.0025, 'output' => 0.01]);
    });

    it('returns null for unknown model', function () {

        $mockData = [
            'providers' => [
                'openai' => [
                    'gpt-4o' => ['input' => 0.0025, 'output' => 0.01],
                ],
            ],
        ];

        Cache::put('ai_guard_pricing', $mockData, now()->addDay());

        $modelPricing = $this->service->getModelPricing('openai', 'unknown');

        expect($modelPricing)->toBeNull();
    });

    it('refreshes cache', function () {

        $mockData = [
            'providers' => [
                'test' => ['foo' => 'bar'],
            ],
        ];

        Http::fake([
            '*' => Http::response($mockData, 200),
        ]);

        // Old cache
        Cache::put('ai_guard_pricing', ['old' => 'data'], now()->addDay());

        // Refresh
        $new = $this->service->refresh();

        expect($new)->toBe($mockData);
    });

});
