<?php

namespace Lmromax\LaravelAiGuard\Services;

use Lmromax\LaravelAiGuard\Models\AiPromptsLog;

class AiGuardService
{
    public function __construct(
        protected PromptLogger $logger,
        protected PromptOptimizer $optimizer,
        protected CostCalculator $costCalculator
    ) {}

    /**
     * Track an AI request
     * 
     * @param array $data An array containing provider, model, prompt, response, tokens_input, tokens_output, duration_ms, user_id, and metadata
     * @return AiPromptsLog The created log entry
     */
    public function track(array $data): AiPromptsLog
    {
        return $this->logger->log($data);
    }

    /**
     * Optimize a prompt
     * 
     * @param string $prompt The original prompt to optimize
     * @return array An array containing original, optimized, tokens saved, and compression ratio
     */
    public function optimize(string $prompt): array
    {
        return $this->optimizer->optimize($prompt);
    }

    /**
     * Get cost statistics
     * 
     * @param string $period The period to get stats for (day, week, month, year)
     * @return array An array containing total requests, total cost, total tokens input/output, average cost per request, and breakdown by provider and model
     */
    public function getCostStats(string $period = 'day'): array
    {
        return $this->logger->getStats($period);
    }

    /**
     * Get total cost for a period
     * 
     * @param string $period The period to get total cost for (day, week, month, year)
     * @return float The total cost for the specified period
     */
    public function getTotalCost(string $period = 'month'): float
    {
        $stats = $this->getCostStats($period);

        return $stats['total_cost'] ?? 0.0;
    }

    /**
     * Calculate cost for given tokens
     * 
     * @param string $provider The provider name (e.g. 'openai')
     * @param string $model The model name (e.g. 'gpt-4o')
     * @param int $tokensInput The number of input tokens
     * @param int $tokensOutput The number of output tokens
     * @return float The calculated cost in dollars
     */
    public function calculateCost(
        string $provider,
        string $model,
        int $tokensInput,
        int $tokensOutput
    ): float {
        return $this->costCalculator->calculate($provider, $model, $tokensInput, $tokensOutput);
    }

    /**
     * Estimate tokens from text
     * 
     * @param string $text The input text to estimate tokens for
     * @return int The estimated number of tokens
     */
    public function estimateTokens(string $text): int
    {
        return $this->costCalculator->estimateTokens($text);
    }
}
