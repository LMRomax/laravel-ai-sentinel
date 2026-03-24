<?php

namespace Lmromax\LaravelAiSentinel\Console\Commands;

use Illuminate\Console\Command;
use Lmromax\LaravelAiSentinel\Services\PricingSyncService;

class SyncPricingCommand extends Command
{
    protected $signature = 'ai-sentinel:sync-pricing
                            {--force : Force refresh, ignore cache}
                            {--show : Display all available models after sync}';

    protected $description = 'Sync AI pricing from remote source';

    public function handle(PricingSyncService $syncService): int
    {
        $this->info('');
        $this->info('🤖 AI Sentinel - Pricing Sync');
        $this->info('==========================');

        // Force refresh si option --force
        if ($this->option('force')) {
            $this->warn('⚡ Force refresh requested, clearing cache...');
            $pricing = $syncService->refresh();
        } else {
            $pricing = $syncService->getPricing();
        }

        // Echec de la sync
        if (! $pricing) {
            $this->error('');
            $this->error('❌ Failed to fetch pricing data.');
            $this->error('   Check your internet connection or pricing_source_url in config/ai-sentinel.php');
            $this->info('');

            return self::FAILURE;
        }

        // Succès
        $this->info('');
        $this->info('✅ Pricing synced successfully!');
        $this->info('   Last updated : '.($pricing['last_updated'] ?? 'unknown'));
        $this->info('   Version      : '.($pricing['version'] ?? 'unknown'));
        $this->info('   Source       : '.config('ai-sentinel.pricing_source_url'));
        $this->info('');

        // Tableau des providers
        $providers = $pricing['providers'] ?? [];

        $this->table(
            ['Provider', 'Models available'],
            collect($providers)->map(function ($models, $provider) {
                return [
                    $provider,
                    count($models).' model(s)',
                ];
            })->values()->toArray()
        );

        // Affiche tous les modèles si --show
        if ($this->option('show')) {
            $this->info('');
            $this->info('📋 Available models :');
            $this->info('');

            foreach ($providers as $provider => $models) {
                $this->line("  <fg=cyan>{$provider}</>");

                foreach ($models as $model => $pricing) {
                    $this->line(sprintf(
                        '    %-45s input: $%s / output: $%s (per 1K tokens)',
                        $model,
                        number_format($pricing['input'], 6),
                        number_format($pricing['output'], 6)
                    ));
                }

                $this->info('');
            }
        }

        return self::SUCCESS;
    }
}
