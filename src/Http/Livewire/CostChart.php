<?php

namespace Lmromax\LaravelAiGuard\Http\Livewire;

use Livewire\Component;
use Lmromax\LaravelAiGuard\Models\AiPromptsLog;

class CostChart extends Component
{
    public $chartData;

    public function mount()
    {
        $this->loadChartData();
    }

    public function loadChartData()
    {
        // Get last 7 days of data
        $data = AiPromptsLog::selectRaw('DATE(created_at) as date, SUM(cost) as total_cost')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $costs = [];

        // Fill all 7 days (even if no data)
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');

            $dayData = $data->firstWhere('date', $date);
            $costs[] = $dayData ? (float) $dayData->total_cost : 0;
        }

        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Daily Cost ($)',
                    'data' => $costs,
                    'borderColor' => 'rgb(79, 70, 229)',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
        ];
    }

    public function render()
    {
        return view('ai-guard::livewire.cost-chart');
    }
}
