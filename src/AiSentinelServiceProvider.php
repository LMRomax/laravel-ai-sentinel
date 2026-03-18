<?php

namespace Lmromax\LaravelAiSentinel;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lmromax\LaravelAiSentinel\Console\Commands\SyncPricingCommand;
use Lmromax\LaravelAiSentinel\Http\Livewire\CostChart;
use Lmromax\LaravelAiSentinel\Http\Livewire\HistoricalChart;
use Lmromax\LaravelAiSentinel\Http\Livewire\ProviderChart;
use Lmromax\LaravelAiSentinel\Http\Livewire\RecentLogs;
use Lmromax\LaravelAiSentinel\Http\Livewire\StatsCards;
use Lmromax\LaravelAiSentinel\Http\Livewire\TopModels;
use Lmromax\LaravelAiSentinel\Models\AiPromptsLog;
use Lmromax\LaravelAiSentinel\Observers\AiPromptsLogObserver;
use Lmromax\LaravelAiSentinel\Services\AiSentinelService;
use Lmromax\LaravelAiSentinel\Services\CostCalculator;
use Lmromax\LaravelAiSentinel\Services\PricingResolver;
use Lmromax\LaravelAiSentinel\Services\PricingSyncService;
use Lmromax\LaravelAiSentinel\Services\PromptLogger;
use Lmromax\LaravelAiSentinel\Services\PromptOptimizer;

class AiSentinelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ai-sentinel.php',
            'ai-sentinel'
        );

        // Bind core services
        $this->app->singleton(PricingSyncService::class);
        $this->app->singleton(PricingResolver::class, function ($app) {
            return new PricingResolver(
                config('ai-sentinel.auto_sync_pricing', true)
                    ? $app->make(PricingSyncService::class)
                    : null
            );
        });
        $this->app->singleton(CostCalculator::class);
        $this->app->singleton(PromptLogger::class);
        $this->app->singleton(PromptOptimizer::class);

        // Bind main service via container
        $this->app->singleton(AiSentinelService::class);

        // Facade alias
        $this->app->alias(AiSentinelService::class, 'ai-sentinel');

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
            __DIR__.'/../config/ai-sentinel.php' => config_path('ai-sentinel.php'),
        ], 'ai-sentinel-config');

        // Publish & load migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'ai-sentinel-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load & publish views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ai-sentinel');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/ai-sentinel'),
        ], 'ai-sentinel-views');

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
        Livewire::component('ai-sentinel.stats-cards', StatsCards::class);
        Livewire::component('ai-sentinel.cost-chart', CostChart::class);
        Livewire::component('ai-sentinel.provider-chart', ProviderChart::class);
        Livewire::component('ai-sentinel.top-models', TopModels::class);
        Livewire::component('ai-sentinel.recent-logs', RecentLogs::class);
        Livewire::component('ai-sentinel.prompt-optimizer', Http\Livewire\PromptOptimizer::class);
        Livewire::component('ai-sentinel.historical-chart', HistoricalChart::class);
    }
}
