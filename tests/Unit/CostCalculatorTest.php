<?php

use Lmromax\LaravelAiGuard\Services\CostCalculator;

beforeEach(function () {
    $this->calculator = new CostCalculator();
});

describe('CostCalculator', function () {

    it('calculates cost correctly for a known model', function () {
        // gpt-4o : input = 0.0025, output = 0.01 (per 1K tokens)
        // 1000 input tokens = 0.0025$
        // 1000 output tokens = 0.01$
        // total = 0.0125$
        $cost = $this->calculator->calculate('openai', 'gpt-4o', 1000, 1000);

        expect($cost)->toBe(0.0125);
    });

    it('calculates cost correctly with partial tokens', function () {
        // gpt-4o-mini : input = 0.00015, output = 0.0006
        // 500 input tokens = 500/1000 * 0.00015 = 0.000075$
        // 250 output tokens = 250/1000 * 0.0006  = 0.00015$
        // total = 0.000225$
        $cost = $this->calculator->calculate('openai', 'gpt-4o-mini', 500, 250);

        expect($cost)->toBe(0.000225);
    });

    it('returns zero cost when tokens are zero', function () {
        $cost = $this->calculator->calculate('openai', 'gpt-4o', 0, 0);

        expect($cost)->toBe(0.0);
    });

    it('uses default pricing for unknown model', function () {
        // default_pricing : input = 0.001, output = 0.003
        // 1000 input = 0.001$, 1000 output = 0.003$, total = 0.004$
        $cost = $this->calculator->calculate('unknown-provider', 'unknown-model', 1000, 1000);

        expect($cost)->toBe(0.004);
    });

    it('estimates tokens from text', function () {
        $tokens = $this->calculator->estimateTokens('Hello world');

        expect($tokens)->toBeInt()->toBeGreaterThan(0);
    });

    it('estimates more tokens for longer text', function () {
        $short = $this->calculator->estimateTokens('Hello');
        $long  = $this->calculator->estimateTokens('Hello world, this is a much longer sentence with many words');

        expect($long)->toBeGreaterThan($short);
    });

    it('returns zero tokens for empty string', function () {
        $tokens = $this->calculator->estimateTokens('');

        expect($tokens)->toBe(0);
    });
});
