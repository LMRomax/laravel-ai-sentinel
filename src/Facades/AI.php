<?php

namespace Lmromax\LaravelAiGuard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array openai(string $model, string $prompt, array $options = [])
 * @method static array anthropic(string $model, string $prompt, array $options = [])
 *
 * @see \Lmromax\LaravelAiGuard\Services\AiRequestService
 */
class AI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ai-request';
    }
}
