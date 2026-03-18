<?php

namespace Lmromax\LaravelAiSentinel\Facades;

use Illuminate\Support\Facades\Facade;
use Lmromax\LaravelAiSentinel\Services\AiSentinelService;

/**
 * @method static \Lmromax\LaravelAiSentinel\Models\AiPromptsLog track(array $data)
 * @method static array optimize(string $prompt)
 * @method static array getCostStats(string $period = 'day')
 * @method static float getTotalCost(string $period = 'month')
 * @method static float calculateCost(string $provider, string $model, int $tokensInput, int $tokensOutput)
 * @method static int estimateTokens(string $text)
 *
 * @see AiSentinelService
 */
class AiSentinel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ai-sentinel';
    }
}
