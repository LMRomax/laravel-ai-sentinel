<?php

use Lmromax\LaravelAiGuard\Services\PricingResolver;
use Lmromax\LaravelAiGuard\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->resolver = new PricingResolver();
});

describe('PricingResolver', function () {

    it('resolves pricing for a known model from config', function () {
        $pricing = $this->resolver->resolve('openai', 'gpt-4o');

        expect($pricing)
            ->toHaveKeys(['input', 'output'])
            ->and($pricing['input'])->toBe(0.0025)
            ->and($pricing['output'])->toBe(0.01);
    });

    it('resolves pricing for anthropic model', function () {
        $pricing = $this->resolver->resolve('anthropic', 'claude-3-5-sonnet-20241022');

        expect($pricing)
            ->toHaveKeys(['input', 'output'])
            ->and($pricing['input'])->toBe(0.003)
            ->and($pricing['output'])->toBe(0.015);
    });

    it('falls back to default pricing for unknown model', function () {
        $pricing = $this->resolver->resolve('unknown-provider', 'unknown-model');

        expect($pricing)->toBe(config('ai-guard.default_pricing'));
    });

    it('resolves custom model pricing', function () {
        config(['ai-guard.custom_models' => [
            'my-provider' => [
                'my-model' => [
                    'input'  => 0.05,
                    'output' => 0.10,
                ],
            ],
        ]]);

        $pricing = $this->resolver->resolve('my-provider', 'my-model');

        expect($pricing['input'])->toBe(0.05);
        expect($pricing['output'])->toBe(0.10);
    });

    it('throws exception when strategy is fail', function () {
        config(['ai-guard.unknown_model_strategy' => 'fail']);

        expect(fn() => $this->resolver->resolve('unknown', 'unknown-model'))
            ->toThrow(\RuntimeException::class);
    });

    it('estimates pricing when strategy is estimate', function () {
        config(['ai-guard.unknown_model_strategy' => 'estimate']);

        $pricing = $this->resolver->resolve('openai', 'gpt-4-future');

        expect($pricing)->toHaveKeys(['input', 'output']);
        expect($pricing['input'])->toBeFloat()->toBeGreaterThan(0);
    });
});
