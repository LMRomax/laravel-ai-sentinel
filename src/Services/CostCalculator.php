<?php

namespace Lmromax\LaravelAiGuard\Services;

class CostCalculator
{
    protected PricingResolver $pricingResolver;

    public function __construct()
    {
        $this->pricingResolver = new PricingResolver;
    }

    /**
     * Calculate cost based on token usage
     *
     * @param  string  $provider  The provider name (e.g. 'openai')
     * @param  string  $model  The model name (e.g. 'gpt-4o')
     * @param  int  $tokensInput  The number of input tokens
     * @param  int  $tokensOutput  The number of output tokens
     * @return float The calculated cost in dollars
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
     *
     * @param  string  $text  The input text to estimate tokens for
     * @return int The estimated number of tokens
     */
    public function estimateTokens(string $text): int
    {
        // More accurate: count words and multiply by 1.3
        $wordCount = str_word_count($text);

        return (int) ceil($wordCount * 1.3);
    }

    /**
     * Get pricing for a specific model
     *
     * @param  string  $provider  The provider name (e.g. 'openai')
     * @param  string  $model  The model name (e.g. 'gpt-4o')
     * @return array An array with 'input' and 'output' pricing
     */
    public function getPricing(string $provider, string $model): array
    {
        return $this->pricingResolver->resolve($provider, $model);
    }
}
