<?php

namespace Lmromax\LaravelAiGuard\Tests;

use Livewire\LivewireServiceProvider;
use Lmromax\LaravelAiGuard\AiGuardServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function getPackageProviders($app): array
    {
        return [
            AiGuardServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // add login route
        $app['router']->get('/login', fn () => 'login-page')->name('login');
        // Setup database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup ai-guard config
        $app['config']->set('ai-guard.enabled', true);

        // IMPORTANT : Désactive auto-sync en tests
        $app['config']->set('ai-guard.auto_sync_pricing', false);

        $app['config']->set('ai-guard.table_name', 'ai_prompt_logs');
        $app['config']->set('ai-guard.default_pricing', [
            'input' => 0.001,
            'output' => 0.003,
        ]);
        $app['config']->set('ai-guard.unknown_model_strategy', 'use_default');
        $app['config']->set('ai-guard.optimization', [
            'enabled' => true,
            'max_context_tokens' => 4000,
            'enable_compression' => true,
            'cache_responses' => false,
            'cache_ttl' => 3600,
        ]);
        $app['config']->set('ai-guard.custom_models', []);

        // IMPORTANT : Définis les providers pour les tests
        $app['config']->set('ai-guard.providers', [
            'openai' => [
                'models' => [
                    'gpt-4o' => [
                        'input' => 0.0025,
                        'output' => 0.01,
                    ],
                    'gpt-4o-mini' => [
                        'input' => 0.00015,
                        'output' => 0.0006,
                    ],
                ],
            ],
            'anthropic' => [
                'models' => [
                    'claude-3-5-sonnet-20241022' => [
                        'input' => 0.003,
                        'output' => 0.015,
                    ],
                ],
            ],
        ]);
    }
}
