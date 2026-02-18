<?php

namespace Lmromax\LaravelAiGuard\Services;

use Lmromax\LaravelAiGuard\Models\AiPromptLog;
use Lmromax\LaravelAiGuard\Services\PromptLogger;
use Lmromax\LaravelAiGuard\Services\PromptOptimizer;
use Lmromax\LaravelAiGuard\Services\CostCalculator;

class AiGuardService
{
    protected PromptLogger $logger;
    protected PromptOptimizer $optimizer;
    protected CostCalculator $costCalculator;

    public function __construct()
    {
        $this->logger = new PromptLogger();
        $this->optimizer = new PromptOptimizer();
        $this->costCalculator = new CostCalculator();
    }

    /**
     * Track an AI request
     */
    public function track(array $data): AiPromptLog
    {
        return $this->logger->log($data);
    }

    /**
     * Optimize a prompt
     */
    public function optimize(string $prompt): array
    {
        return $this->optimizer->optimize($prompt);
    }

    /**
     * Get cost statistics
     */
    public function getCostStats(string $period = 'day'): array
    {
        return $this->logger->getStats($period);
    }

    /**
     * Get total cost for a period
     */
    public function getTotalCost(string $period = 'month'): float
    {
        $stats = $this->getCostStats($period);
        return $stats['total_cost'] ?? 0.0;
    }

    /**
     * Calculate cost for given tokens
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
     */
    public function estimateTokens(string $text): int
    {
        return $this->costCalculator->estimateTokens($text);
    }
}