<?php

namespace Lmromax\LaravelAiGuard\Services;

use Lmromax\LaravelAiGuard\Models\AiPromptsLog;
use Lmromax\LaravelAiGuard\Services\CostCalculator;

class PromptLogger
{
    protected CostCalculator $costCalculator;

    public function __construct()
    {
        $this->costCalculator = new CostCalculator();
    }

    /**
     * Log an AI prompt and response
     */
    public function log(array $data): AiPromptsLog
    {
        if (!config('ai-guard.enabled', true)) {
            return new AiPromptsLog(); // Return empty model if disabled
        }

        $cost = $this->costCalculator->calculate(
            $data['provider'],
            $data['model'],
            $data['tokens_input'] ?? 0,
            $data['tokens_output'] ?? 0
        );

        return AiPromptsLog::create([
            'provider' => $data['provider'],
            'model' => $data['model'],
            'prompt' => $data['prompt'] ?? '',
            'response' => $data['response'] ?? null,
            'tokens_input' => $data['tokens_input'] ?? 0,
            'tokens_output' => $data['tokens_output'] ?? 0,
            'cost' => $cost,
            'duration_ms' => $data['duration_ms'] ?? null,
            'user_id' => $data['user_id'] ?? auth()->id(),
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Get stats for a specific period
     */
    public function getStats(string $period = 'day'): array
    {
        $query = AiPromptsLog::query();

        $dateRange = match ($period) {
            'day' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };

        $query->dateRange($dateRange[0], $dateRange[1]);

        return [
            'total_requests' => $query->count(),
            'total_cost' => $query->sum('cost'),
            'total_tokens_input' => $query->sum('tokens_input'),
            'total_tokens_output' => $query->sum('tokens_output'),
            'avg_cost_per_request' => $query->avg('cost'),
            'by_provider' => $this->getStatsByProvider($query),
            'by_model' => $this->getStatsByModel($query),
        ];
    }

    /**
     * Get stats grouped by provider
     */
    protected function getStatsByProvider($query): array
    {
        return $query->selectRaw('
            provider,
            COUNT(*) as count,
            SUM(cost) as total_cost,
            AVG(cost) as avg_cost
        ')
        ->groupBy('provider')
        ->get()
        ->keyBy('provider')
        ->toArray();
    }

    /**
     * Get stats grouped by model
     */
    protected function getStatsByModel($query): array
    {
        return $query->selectRaw('
            model,
            COUNT(*) as count,
            SUM(cost) as total_cost,
            AVG(cost) as avg_cost
        ')
        ->groupBy('model')
        ->get()
        ->keyBy('model')
        ->toArray();
    }
}