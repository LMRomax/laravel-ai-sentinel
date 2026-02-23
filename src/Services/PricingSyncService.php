<?php

namespace Lmromax\LaravelAiGuard\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PricingSyncService
{
    protected string $pricingUrl;

    protected int $cacheTtl = 86400; // 24 heures

    protected string $cacheKey = 'ai_guard_pricing';

    public function __construct()
    {
        $this->pricingUrl = config(
            'ai-guard.pricing_source_url',
            'https://raw.githubusercontent.com/LMRomax/ai-pricing-data/master/pricing.json'
        );
    }

    /**
     * Get pricing data (from cache or remote)
     * 
     * @return array|null The pricing data array or null if it cannot be fetched
     */
    public function getPricing(): ?array
    {
        try {
            return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
                return $this->fetchRemote();
            });
        } catch (\Exception $e) {
            Log::warning('AI Guard: Could not get pricing data', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get pricing for a specific provider/model
     * 
     * @param string $provider The provider name (e.g. 'openai')
     * @param string $model The model name (e.g. 'gpt-4o')
     * @return array|null An array with 'input' and 'output' pricing or null if not found
     */
    public function getModelPricing(string $provider, string $model): ?array
    {
        $pricing = $this->getPricing();

        if (! $pricing) {
            return null;
        }

        return $pricing['providers'][$provider][$model] ?? null;
    }

    /**
     * Force refresh the cache
     * 
     * @return array|null The new pricing data or null if it cannot be fetched
     */
    public function refresh(): ?array
    {
        Cache::forget($this->cacheKey);

        return $this->getPricing();
    }

    /**
     * Check if pricing cache exists
     * 
     * @return bool True if cache exists, false otherwise
     */
    public function isCached(): bool
    {
        return Cache::has($this->cacheKey);
    }

    /**
     * Get cache metadata
     * 
     * @return array An array containing is_cached, last_updated, version, and source_url
     */
    public function getCacheInfo(): array
    {
        $pricing = $this->getPricing();

        return [
            'is_cached' => $this->isCached(),
            'last_updated' => $pricing['last_updated'] ?? null,
            'version' => $pricing['version'] ?? null,
            'source_url' => $this->pricingUrl,
        ];
    }

    /**
     * Fetch pricing from remote source
     * 
     * @return array|null The pricing data array or null if it cannot be fetched or is invalid
     */
    protected function fetchRemote(): ?array
    {
        try {
            $response = Http::timeout(10)
                ->retry(3, 500) // 3 tentatives, 500ms entre chaque
                ->get($this->pricingUrl);

            if ($response->successful()) {
                $data = $response->json();

                // Validation basique du JSON
                if (! isset($data['providers'])) {
                    Log::warning('AI Guard: Invalid pricing JSON structure');

                    return null;
                }

                Log::info('AI Guard: Pricing synced successfully', [
                    'last_updated' => $data['last_updated'] ?? 'unknown',
                    'version' => $data['version'] ?? 'unknown',
                ]);

                return $data;
            }

            Log::warning('AI Guard: Failed to fetch pricing', [
                'status' => $response->status(),
                'url' => $this->pricingUrl,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::warning('AI Guard: Exception while fetching pricing', [
                'error' => $e->getMessage(),
                'url' => $this->pricingUrl,
            ]);

            return null;
        }
    }
}
