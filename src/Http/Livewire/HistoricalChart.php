<?php

namespace Lmromax\LaravelAiGuard\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Lmromax\LaravelAiGuard\Models\AiPromptsLog;

class HistoricalChart extends Component
{
    public $chartData;

    public $months = 6;

    public function mount()
    {
        $this->loadChartData();
    }

    public function setMonths($months)
    {
        $this->months = $months;
        $this->loadChartData();

        $this->dispatch('historicalChartUpdated', chartData: $this->chartData);
    }

    public function loadChartData()
    {
        $logs = AiPromptsLog::where('created_at', '>=', now()->subMonths($this->months))
            ->get();

        $costsByMonth = $logs->groupBy(function ($log) {
            return Carbon::parse($log->created_at)->format('Y-m');
        })->map(function ($monthLogs) {
            return $monthLogs->sum('cost');
        });

        $labels = [];
        $data = [];

        for ($i = $this->months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $labels[] = $date->format('M Y');
            $data[] = (float) ($costsByMonth[$monthKey] ?? 0);
        }

        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Monthly Cost',
                    'data' => $data,
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }

    public function render()
    {
        return view('ai-guard::livewire.historical-chart');
    }
}
