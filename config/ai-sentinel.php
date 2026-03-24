<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Sentinel Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable AI Sentinel tracking globally.
    |
    */
    'enabled' => env('AI_SENTINEL_ENABLED', true),

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
    'auto_sync_pricing' => env('AI_SENTINEL_AUTO_SYNC', true),

    'pricing_source_url' => env(
        'AI_SENTINEL_PRICING_URL',
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
    'unknown_model_strategy' => env('AI_SENTINEL_UNKNOWN_MODEL_STRATEGY', 'use_default'),

    'default_pricing' => [
        'input' => 0.001, // $1.00 per 1M tokens
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
        'enabled' => env('AI_SENTINEL_ALERTS_ENABLED', true),
        'daily_limit' => env('AI_SENTINEL_DAILY_LIMIT', 100),
        'monthly_limit' => env('AI_SENTINEL_MONTHLY_LIMIT', 1000),
        'channels' => ['mail'], // mail, slack, discord (TODO: implement slack/discord)
        'recipients' => explode(',', env('AI_SENTINEL_ALERT_EMAILS', '')), // comma-separated emails
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
        'enabled' => env('AI_SENTINEL_OPTIMIZATION_ENABLED', true),
        'enable_compression' => env('AI_SENTINEL_ENABLE_COMPRESSION', true),
        'max_context_tokens' => env('AI_SENTINEL_MAX_CONTEXT_TOKENS', 4000),

        // AI-powered compression (recommended)
        'use_ai_compression' => env('AI_SENTINEL_USE_AI_COMPRESSION', true),
        'compression_provider' => env('AI_SENTINEL_COMPRESSION_PROVIDER', 'openai'), // openai, anthropic
        'compression_model' => env('AI_SENTINEL_COMPRESSION_MODEL', 'gpt-4o-mini'), // Ultra cheap: $0.00015/1K tokens
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | Customize the table name used to store AI prompt logs.
    |
    */
    'table_name' => env('AI_SENTINEL_TABLE_NAME', 'ai_prompt_logs'),

];
