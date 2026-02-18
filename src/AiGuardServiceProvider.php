<?php

namespace Lmromax\LaravelAiGuard;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lmromax\LaravelAiGuard\Console\Commands\SyncPricingCommand;
use Lmromax\LaravelAiGuard\Services\AiGuardService;

class AiGuardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ai-guard.php',
            'ai-guard'
        );

        // Register singleton
        $this->app->singleton('ai-guard', function ($app) {
            return new AiGuardService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/ai-guard.php' => config_path('ai-guard.php'),
        ], 'ai-guard-config');

        // Publish & load migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'ai-guard-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load & publish views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ai-guard');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/ai-guard'),
        ], 'ai-guard-views');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Register Livewire components
        if (class_exists(Livewire::class)) {
            $this->registerLivewireComponents();
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncPricingCommand::class,
            ]);
        }
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component('ai-guard.stats-cards', \Lmromax\LaravelAiGuard\Http\Livewire\StatsCards::class);
        Livewire::component('ai-guard.cost-chart', \Lmromax\LaravelAiGuard\Http\Livewire\CostChart::class);
        Livewire::component('ai-guard.provider-chart', \Lmromax\LaravelAiGuard\Http\Livewire\ProviderChart::class);
        Livewire::component('ai-guard.top-models', \Lmromax\LaravelAiGuard\Http\Livewire\TopModels::class);
        Livewire::component('ai-guard.recent-logs', \Lmromax\LaravelAiGuard\Http\Livewire\RecentLogs::class);
    }
}
