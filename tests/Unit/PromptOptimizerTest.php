<?php

use Lmromax\LaravelAiGuard\Services\PromptOptimizer;
use Lmromax\LaravelAiGuard\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->optimizer = new PromptOptimizer();
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

    it('compresses filler phrases', function () {
        $result = $this->optimizer->optimize('Please provide an explanation of Laravel.');

        expect($result['optimized'])->not->toContain('Please provide');
        expect($result['optimized'])->toContain('Provide');
    });

    it('removes excessive whitespace', function () {
        $result = $this->optimizer->optimize('Hello    world,   this   has   extra   spaces.');

        expect($result['optimized'])->not->toContain('  ');
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

        $truncated = $this->optimizer->truncateContext($longText, 50);

        expect(strlen($truncated))->toBeLessThan(strlen($longText));
        expect($truncated)->toEndWith('...');
    });

    it('does not truncate context within token limit', function () {
        $shortText = 'Hello world, this is a short text.';

        $result = $this->optimizer->truncateContext($shortText, 4000);

        expect($result)->toBe($shortText);
    });

});