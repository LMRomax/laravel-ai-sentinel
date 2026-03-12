<?php

namespace Lmromax\LaravelAiGuard\Facades;

use Illuminate\Support\Facades\Facade;
use Lmromax\LaravelAiGuard\Services\AiRequestService;

/**
 * @method static array openai(string $model, string $prompt, array $options = [])
 * @method static array anthropic(string $model, string $prompt, array $options = [])
 *
 * @see AiRequestService
 */
class AI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ai-request';
    }
}
