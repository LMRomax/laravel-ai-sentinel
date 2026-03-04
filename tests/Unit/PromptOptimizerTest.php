<?php

use Lmromax\LaravelAiGuard\Services\PromptOptimizer;

beforeEach(function () {
    // Disable AI compression
    config(['ai-guard.optimization.use_ai_compression' => false]);

    $this->optimizer = new PromptOptimizer;
});

describe('PromptOptimizer', function () {

    it('returns optimization result with required keys', function () {
        $result = $this->optimizer->optimize('Please can you help me explain Laravel?');

        expect($result)
            ->toHaveKeys([
                'original',
                'optimized',
                'tokens_original',
                'tokens_optimized',
                'tokens_saved',
                'compression_ratio',
            ]);
    });

    it('returns original when optimization is disabled', function () {
        config(['ai-guard.optimization.enabled' => false]);

        $prompt = 'Please can you help me understand Laravel?';
        $result = $this->optimizer->optimize($prompt);

        expect($result['optimized'])->toBe($prompt);
        expect($result['tokens_saved'])->toBe(0);
    });

    it('calculates compression ratio correctly', function () {
        $result = $this->optimizer->optimize('Please provide a very detailed explanation.');

        expect($result['compression_ratio'])->toBeFloat()->toBeGreaterThanOrEqual(0);
        expect($result['compression_ratio'])->toBeLessThanOrEqual(100);
    });

    it('truncates context that exceeds max tokens', function () {
        $longText = str_repeat('This is a word. ', 1000); // Très long texte

        $truncated = $this->optimizer->optimize($longText, 50);
        $result = $truncated['optimized'];

        expect(strlen($result))->toBeLessThan(strlen($longText));
    });
});
