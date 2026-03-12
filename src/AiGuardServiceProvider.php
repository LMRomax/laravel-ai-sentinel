<?php

namespace Lmromax\LaravelAiGuard;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lmromax\LaravelAiGuard\Console\Commands\SyncPricingCommand;
use Lmromax\LaravelAiGuard\Http\Livewire\CostChart;
use Lmromax\LaravelAiGuard\Http\Livewire\HistoricalChart;
use Lmromax\LaravelAiGuard\Http\Livewire\ProviderChart;
use Lmromax\LaravelAiGuard\Http\Livewire\RecentLogs;
use Lmromax\LaravelAiGuard\Http\Livewire\StatsCards;
use Lmromax\LaravelAiGuard\Http\Livewire\TopModels;
use Lmromax\LaravelAiGuard\Models\AiPromptsLog;
use Lmromax\LaravelAiGuard\Observers\AiPromptsLogObserver;
use Lmromax\LaravelAiGuard\Services\AiGuardService;
use Lmromax\LaravelAiGuard\Services\CostCalculator;
use Lmromax\LaravelAiGuard\Services\PricingResolver;
use Lmromax\LaravelAiGuard\Services\PricingSyncService;
use Lmromax\LaravelAiGuard\Services\PromptLogger;
use Lmromax\LaravelAiGuard\Services\PromptOptimizer;

class AiGuardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ai-guard.php',
            'ai-guard'
        );

        // Bind core services
        $this->app->singleton(PricingSyncService::class);
        $this->app->singleton(PricingResolver::class, function ($app) {
            return new PricingResolver(
                config('ai-guard.auto_sync_pricing', true)
                    ? $app->make(PricingSyncService::class)
                    : null
            );
        });
        $this->app->singleton(CostCalculator::class);
        $this->app->singleton(PromptLogger::class);
        $this->app->singleton(PromptOptimizer::class);

        // Bind main service via container
        $this->app->singleton(AiGuardService::class);

        // Facade alias
        $this->app->alias(AiGuardService::class, 'ai-guard');

        // Register observer for automatic alerts
        AiPromptsLog::observe(AiPromptsLogObserver::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/ai-guard.php' => config_path('ai-guard.php'),
        ], 'ai-guard-config');

        // Publish & load migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'ai-guard-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load & publish views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ai-guard');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/ai-guard'),
        ], 'ai-guard-views');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

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
        Livewire::component('ai-guard.stats-cards', StatsCards::class);
        Livewire::component('ai-guard.cost-chart', CostChart::class);
        Livewire::component('ai-guard.provider-chart', ProviderChart::class);
        Livewire::component('ai-guard.top-models', TopModels::class);
        Livewire::component('ai-guard.recent-logs', RecentLogs::class);
        Livewire::component('ai-guard.prompt-optimizer', Http\Livewire\PromptOptimizer::class);
        Livewire::component('ai-guard.historical-chart', HistoricalChart::class);
    }
}
