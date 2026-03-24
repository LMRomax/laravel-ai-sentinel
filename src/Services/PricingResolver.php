<?php

namespace Lmromax\LaravelAiSentinel\Services;

use Illuminate\Support\Facades\Log;

class PricingResolver
{
    protected ?PricingSyncService $syncService = null;

    public function __construct(?PricingSyncService $syncService = null)
    {
        $this->syncService = $syncService;
    }

    /**
     * Resolve pricing for a given provider and model
     *
     * Priority :
     * 1. Remote JSON (GitHub) via cache
     * 2. Custom models defined by user in config
     * 3. Default pricing fallback
     *
     * @param  string  $provider  The provider name (e.g. 'openai')
     * @param  string  $model  The model name (e.g. 'gpt-4o')
     * @return array An array with 'input' and 'output' pricing
     */
    public function resolve(string $provider, string $model): array
    {
        // Remote JSON via cache
        if ($this->syncService) {
            $remotePricing = $this->syncService->getModelPricing($provider, $model);

            if ($remotePricing) {
                return $remotePricing;
            }

            Log::info("AI Sentinel: Model [{$provider}/{$model}] not found in remote pricing, falling back.");
        }

        $configPricing = config("ai-sentinel.providers.{$provider}.models.{$model}");

        if ($configPricing) {
            return $configPricing;
        }

        // Custom models définis par l'utilisateur
        $customPricing = config("ai-sentinel.custom_models.{$provider}.{$model}");

        if ($customPricing) {
            return $customPricing;
        }

        // Stratégie de fallback
        $strategy = config('ai-sentinel.unknown_model_strategy', 'use_default');

        if ($strategy === 'estimate') {
            return $this->estimatePricing($provider, $model);
        }

        if ($strategy === 'fail') {
            $this->throwUnknownModelException($provider, $model);
        }

        return config('ai-sentinel.default_pricing');
    }

    /**
     * Estimate pricing based on model name patterns
     *
     * @param  string  $provider  The provider name (e.g. 'openai')
     * @param  string  $model  The model name (e.g. 'gpt-4o')
     * @return array An array with 'input' and 'output' pricing estimates
     */
    protected function estimatePricing(string $provider, string $model): array
    {
        // OpenAI
        if (str_contains($model, 'gpt-4o')) {
            return ['input' => 0.0025, 'output' => 0.01];
        }

        if (str_contains($model, 'gpt-4')) {
            return ['input' => 0.01, 'output' => 0.03];
        }

        if (str_contains($model, 'gpt-3.5')) {
            return ['input' => 0.0005, 'output' => 0.0015];
        }

        // Anthropic
        if (str_contains($model, 'claude') && str_contains($model, 'opus')) {
            return ['input' => 0.015, 'output' => 0.075];
        }

        if (str_contains($model, 'claude') && str_contains($model, 'haiku')) {
            return ['input' => 0.0008, 'output' => 0.004];
        }

        if (str_contains($model, 'claude')) {
            return ['input' => 0.003, 'output' => 0.015];
        }

        // Groq (généralement très bon marché)
        if ($provider === 'groq') {
            return ['input' => 0.0005, 'output' => 0.0008];
        }

        Log::warning("AI Sentinel: Could not estimate pricing for [{$provider}/{$model}], using default.");

        return config('ai-sentinel.default_pricing');
    }

    /**
     * Throw exception for unknown model
     *
     * @param  string  $provider  The provider name (e.g. 'openai')
     * @param  string  $model  The model name (e.g. 'gpt-4o')
     */
    protected function throwUnknownModelException(string $provider, string $model): never
    {
        throw new \RuntimeException(
            "AI Sentinel: Unknown model [{$provider}/{$model}]. ".
                "Add it to 'custom_models' in config/ai-sentinel.php or change 'unknown_model_strategy'."
        );
    }
}
