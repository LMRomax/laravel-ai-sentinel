# 🤖 Laravel AI Guard

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lmromax/laravel-ai-guard.svg?style=flat-square)](https://packagist.org/packages/lmromax/laravel-ai-guard)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lmromax/laravel-ai-guard/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lmromax/laravel-ai-guard/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lmromax/laravel-ai-guard/code-style.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/lmromax/laravel-ai-guard/actions?query=workflow%3A"code-style"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/lmromax/laravel-ai-guard.svg?style=flat-square)](https://packagist.org/packages/lmromax/laravel-ai-guard)
[![License](https://img.shields.io/packagist/l/lmromax/laravel-ai-guard.svg?style=flat-square)](https://packagist.org/packages/lmromax/laravel-ai-guard)

**Track, optimize and control your AI API costs in Laravel.**

Laravel AI Guard gives you full visibility over your AI spending (OpenAI, Anthropic, Groq, Google, Mistral...) with prompt logging, cost calculation, optimization tools and spending alerts.

---

## ✨ Features

- 💰 **Cost tracking** — Automatic cost calculation per request (input + output tokens)
- 📊 **Statistics** — Daily, weekly, monthly cost reports per provider and model
- 🔔 **Alerts** — Get notified when you exceed your spending limits
- ⚡ **Prompt optimization** — Reduce token usage with automatic compression
- 🔄 **Auto-sync pricing** — Always up-to-date pricing from a maintained remote source
- 🛠️ **Artisan commands** — Manage and inspect your AI usage from the CLI
- 🧩 **Provider agnostic** — Works with any AI provider

---

## 📦 Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+

---

## 🚀 Installation

```bash
composer require lmromax/laravel-ai-guard
```

### Publish config

```bash
php artisan vendor:publish --tag=ai-guard-config
```

### Publish and run migrations

```bash
php artisan vendor:publish --tag=ai-guard-migrations
php artisan migrate
```

---

## ⚙️ Configuration

Add the following variables to your `.env` file:

```env
AI_GUARD_ENABLED=true
AI_GUARD_AUTO_SYNC=true

# API Keys (only for providers you use)
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
GROQ_API_KEY=gsk_...
GOOGLE_AI_API_KEY=...
MISTRAL_API_KEY=...

# Spending alerts
AI_GUARD_ALERTS_ENABLED=true
AI_GUARD_DAILY_LIMIT=100
AI_GUARD_MONTHLY_LIMIT=1000

# Prompt optimization
AI_GUARD_OPTIMIZATION_ENABLED=true
```

---

## 📖 Usage

### Track an AI request

```php
use Lmromax\LaravelAiGuard\Facades\AiGuard;

// After calling your AI provider, track the request
AiGuard::track([
    'provider'      => 'anthropic',
    'model'         => 'claude-3-5-sonnet-20241022',
    'prompt'        => 'Explain Laravel in 50 words',
    'response'      => 'Laravel is a PHP framework...',
    'tokens_input'  => 120,
    'tokens_output' => 95,
    'duration_ms'   => 1200,
]);
```

### Optimize a prompt before sending

```php
$result = AiGuard::optimize('Please can you help me to explain what Laravel is ?');

// Returns:
// [
//     'original'          => 'Please can you help me to explain what Laravel is ?',
//     'optimized'         => 'Help me explain what Laravel is?',
//     'tokens_original'   => 14,
//     'tokens_optimized'  => 8,
//     'tokens_saved'      => 6,
//     'compression_ratio' => 42.86,
// ]

// Use the optimized prompt
$response = $yourAiClient->send($result['optimized']);
```

### Get cost statistics

```php
// Today
$stats = AiGuard::getCostStats('day');

// This week
$stats = AiGuard::getCostStats('week');

// This month
$stats = AiGuard::getCostStats('month');

// Returns:
// [
//     'total_requests'     => 142,
//     'total_cost'         => 4.23,
//     'total_tokens_input' => 58000,
//     'total_tokens_output'=> 32000,
//     'avg_cost_per_request' => 0.029,
//     'by_provider'        => [...],
//     'by_model'           => [...],
// ]
```

### Get total cost

```php
$monthlyCost = AiGuard::getTotalCost('month'); // 4.23
$dailyCost   = AiGuard::getTotalCost('day');   // 0.87
```

### Calculate cost manually

```php
$cost = AiGuard::calculateCost(
    provider: 'openai',
    model: 'gpt-4o',
    tokensInput: 500,
    tokensOutput: 300
);
// Returns: 0.004250 (USD)
```

### Estimate tokens

```php
$tokens = AiGuard::estimateTokens('Hello, how are you today?');
// Returns: ~8
```

---

## 🔄 Pricing Sync

Laravel AI Guard automatically fetches up-to-date pricing from a maintained remote source every 24 hours.

### Manual sync

```bash
# Sync pricing
php artisan ai-guard:sync-pricing

# Force refresh cache
php artisan ai-guard:sync-pricing --force

# Display all available models
php artisan ai-guard:sync-pricing --show
```

### Add a custom model

If your model is not in the remote pricing source, add it to `config/ai-guard.php`:

```php
'custom_models' => [
    'my-provider' => [
        'my-custom-model' => [
            'input'  => 0.01,
            'output' => 0.02,
        ],
    ],
],
```

---

## 📊 Real-world example with OpenAI

```php
use OpenAI\Laravel\Facades\OpenAI;
use Lmromax\LaravelAiGuard\Facades\AiGuard;

public function askAi(string $question): string
{
    // 1. Optimize the prompt
    $optimized = AiGuard::optimize($question);

    $start = microtime(true);

    // 2. Call OpenAI
    $response = OpenAI::chat()->create([
        'model'    => 'gpt-4o',
        'messages' => [
            ['role' => 'user', 'content' => $optimized['optimized']],
        ],
    ]);

    $duration = (int) ((microtime(true) - $start) * 1000);

    // 3. Track the request
    AiGuard::track([
        'provider'      => 'openai',
        'model'         => 'gpt-4o',
        'prompt'        => $optimized['optimized'],
        'response'      => $response->choices[0]->message->content,
        'tokens_input'  => $response->usage->promptTokens,
        'tokens_output' => $response->usage->completionTokens,
        'duration_ms'   => $duration,
        'metadata'      => [
            'tokens_saved' => $optimized['tokens_saved'],
        ],
    ]);

    return $response->choices[0]->message->content;
}
```

---

## 📊 Real-world example with Anthropic

```php
use Anthropic\Laravel\Facades\Anthropic;
use Lmromax\LaravelAiGuard\Facades\AiGuard;

public function askClaude(string $question): string
{
    $optimized = AiGuard::optimize($question);

    $start = microtime(true);

    $response = Anthropic::messages()->create([
        'model'      => 'claude-3-5-sonnet-20241022',
        'max_tokens' => 1024,
        'messages'   => [
            ['role' => 'user', 'content' => $optimized['optimized']],
        ],
    ]);

    $duration = (int) ((microtime(true) - $start) * 1000);

    AiGuard::track([
        'provider'      => 'anthropic',
        'model'         => 'claude-3-5-sonnet-20241022',
        'prompt'        => $optimized['optimized'],
        'response'      => $response->content[0]->text,
        'tokens_input'  => $response->usage->inputTokens,
        'tokens_output' => $response->usage->outputTokens,
        'duration_ms'   => $duration,
    ]);

    return $response->content[0]->text;
}
```

---

## 🔔 Spending Alerts

Configure spending limits in your `.env`:

```env
AI_GUARD_DAILY_LIMIT=50      # Alert when daily spend exceeds $50
AI_GUARD_MONTHLY_LIMIT=500   # Alert when monthly spend exceeds $500
```

Alerts are sent via Laravel's notification system. Supported channels: `mail`, `slack`, `discord`.

---

## 🛠️ Artisan Commands

| Command | Description |
|---|---|
| `ai-guard:sync-pricing` | Sync pricing from remote source |
| `ai-guard:sync-pricing --force` | Force refresh cache |
| `ai-guard:sync-pricing --show` | Display all available models |

---

## 🧩 Supported Providers

| Provider | Status |
|---|---|
| OpenAI (GPT-4o, o1, o3...) | ✅ |
| Anthropic (Claude 3.5, 3.7...) | ✅ |
| Groq (Llama, Mixtral...) | ✅ |
| Google (Gemini 2.0, 1.5...) | ✅ |
| Mistral | ✅ |
| DeepSeek | ✅ |
| xAI (Grok) | ✅ |
| Custom provider | ✅ via `custom_models` |

---

## 📜 License

MIT — [Maxence Lemaitre](https://github.com/LMRomax)