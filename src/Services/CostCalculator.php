<?php

namespace Lmromax\LaravelAiGuard\Services;

class CostCalculator
{
    protected PricingResolver $pricingResolver;

    public function __construct()
    {
        $this->pricingResolver = new PricingResolver();
    }

    /**
     * Calculate cost based on token usage
     */
    public function calculate(
        string $provider,
        string $model,
        int $tokensInput,
        int $tokensOutput
    ): float {
        $pricing = $this->pricingResolver->resolve($provider, $model);

        // Pricing is per 1K tokens, so divide by 1000
        $inputCost = ($tokensInput / 1000) * $pricing['input'];
        $outputCost = ($tokensOutput / 1000) * $pricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Estimate tokens from text (rough approximation)
     * Rule of thumb: 1 token ≈ 4 characters for English
     */
    public function estimateTokens(string $text): int
    {
        // More accurate: count words and multiply by 1.3
        $wordCount = str_word_count($text);
        return (int) ceil($wordCount * 1.3);
    }

    /**
     * Get pricing for a specific model
     */
    public function getPricing(string $provider, string $model): array
    {
        return $this->pricingResolver->resolve($provider, $model);
    }
}