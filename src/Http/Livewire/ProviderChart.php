<?php

namespace Lmromax\LaravelAiGuard\Http\Livewire;

use Livewire\Component;
use Lmromax\LaravelAiGuard\Models\AiPromptsLog;

class ProviderChart extends Component
{
    public $chartData;

    public function mount()
    {
        $this->loadChartData();
    }

    public function loadChartData()
    {
        // Get all logs from this month
        $logs = AiPromptsLog::where('created_at', '>=', now()->startOfMonth())
            ->get();

        // Group by provider in PHP (database-agnostic)
        $costsByProvider = $logs->groupBy('provider')
            ->map(function ($providerLogs) {
                return $providerLogs->sum('cost');
            })
            ->sortDesc();

        $labels = [];
        $costs = [];
        $colors = [
            'openai'    => 'rgb(16, 185, 129)',
            'anthropic' => 'rgb(139, 92, 246)',
            'groq'      => 'rgb(249, 115, 22)',
            'google'    => 'rgb(59, 130, 246)',
            'mistral'   => 'rgb(236, 72, 153)',
            'deepseek'  => 'rgb(234, 179, 8)',
            'xai'       => 'rgb(239, 68, 68)',
        ];

        $backgroundColors = [];

        foreach ($costsByProvider as $provider => $cost) {
            $labels[] = ucfirst($provider);
            $costs[] = (float) $cost;
            $backgroundColors[] = $colors[$provider] ?? 'rgb(156, 163, 175)';
        }

        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Cost by Provider ($)',
                    'data' => $costs,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
        ];
    }

    public function render()
    {
        return view('ai-guard::livewire.provider-chart');
    }
}
