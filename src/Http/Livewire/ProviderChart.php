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
        // Get provider distribution for current month
        $data = AiPromptsLog::selectRaw('provider, SUM(cost) as total_cost')
            ->where('created_at', '>=', now()->startOfMonth())
            ->groupBy('provider')
            ->orderByDesc('total_cost')
            ->get();

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

        foreach ($data as $row) {
            $labels[] = ucfirst($row->provider);
            $costs[] = (float) $row->total_cost;
            $backgroundColors[] = $colors[$row->provider] ?? 'rgb(156, 163, 175)';
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