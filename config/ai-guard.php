<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Guard Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable AI Guard tracking globally.
    |
    */
    'enabled' => env('AI_GUARD_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | AI Providers & Pricing
    |--------------------------------------------------------------------------
    |
    | Pricing is per 1K tokens in USD.
    | These values are used as fallback if auto-sync is disabled or fails.
    | Keep this list updated or rely on auto_sync_pricing below.
    |
    */
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
        ],
        'google' => [
            'api_key' => env('GOOGLE_AI_API_KEY'),
        ],
        'mistral' => [
            'api_key' => env('MISTRAL_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Sync Pricing
    |--------------------------------------------------------------------------
    |
    | Automatically fetch latest pricing from a remote JSON source.
    | Falls back to local config if sync fails or is disabled.
    | Cache is refreshed every 24 hours.
    |
    */
    'auto_sync_pricing' => env('AI_GUARD_AUTO_SYNC', true),

    'pricing_source_url' => env(
        'AI_GUARD_PRICING_URL',
        'https://raw.githubusercontent.com/LMRomax/ai-pricing-data/master/pricing.json'
    ),

    /*
    |--------------------------------------------------------------------------
    | Pricing Fallback Strategy
    |--------------------------------------------------------------------------
    |
    | What to do when a model is not found anywhere:
    |   - 'use_default' : Use the default_pricing values below
    |   - 'estimate'    : Estimate based on similar known models
    |   - 'fail'        : Throw an exception
    |
    */
    'unknown_model_strategy' => env('AI_GUARD_UNKNOWN_MODEL_STRATEGY', 'use_default'),

    'default_pricing' => [
        'input'  => 0.001, // $1.00 per 1M tokens
        'output' => 0.003, // $3.00 per 1M tokens
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Model Pricing
    |--------------------------------------------------------------------------
    |
    | Add your own providers or models without modifying the package.
    |
    | Example:
    |   'my-provider' => [
    |       'my-custom-model' => ['input' => 0.01, 'output' => 0.02],
    |   ],
    |
    */
    'custom_models' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Alerts
    |--------------------------------------------------------------------------
    |
    | Receive notifications when spending exceeds defined thresholds.
    | Supported channels: 'mail', 'slack', 'discord'
    |
    */
    'alerts' => [
        'enabled'               => env('AI_GUARD_ALERTS_ENABLED', true),
        'daily_limit'           => env('AI_GUARD_DAILY_LIMIT', 100),
        'monthly_limit'         => env('AI_GUARD_MONTHLY_LIMIT', 1000),
        'notification_channels' => ['mail'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Optimization
    |--------------------------------------------------------------------------
    |
    | Automatically optimize prompts to reduce token usage and cost.
    |
    */
    'optimization' => [
        'enabled'            => env('AI_GUARD_OPTIMIZATION_ENABLED', true),
        'max_context_tokens' => 4000,
        'enable_compression' => true,
        'cache_responses'    => true,
        'cache_ttl'          => 3600, // seconds (1 hour)
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | Customize the table name used to store AI prompt logs.
    |
    */
    'table_name' => env('AI_GUARD_TABLE_NAME', 'ai_prompt_logs'),

];
