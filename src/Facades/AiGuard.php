<?php

namespace Lmromax\LaravelAiGuard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Lmromax\LaravelAiGuard\Models\AiPromptsLog track(array $data)
 * @method static array optimize(string $prompt)
 * @method static array getCostStats(string $period = 'day')
 * @method static float getTotalCost(string $period = 'month')
 * @method static float calculateCost(string $provider, string $model, int $tokensInput, int $tokensOutput)
 * @method static int estimateTokens(string $text)
 *
 * @see \Lmromax\LaravelAiGuard\Services\AiGuardService
 */
class AiGuard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ai-guard';
    }
}
