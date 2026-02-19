<?php

namespace Lmromax\LaravelAiGuard\Http\Livewire;

use Livewire\Component;
use Lmromax\LaravelAiGuard\Models\AiPromptsLog;
use Carbon\Carbon;

class CostChart extends Component
{
    public $chartData;

    public function mount()
    {
        $this->loadChartData();
    }

    public function loadChartData()
    {
        // Get all logs from last 7 days
        $logs = AiPromptsLog::where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at')
            ->get();

        // Group by date in PHP (database-agnostic)
        $costsByDate = $logs->groupBy(function ($log) {
            return Carbon::parse($log->created_at)->format('Y-m-d');
        })->map(function ($dayLogs) {
            return $dayLogs->sum('cost');
        });

        $labels = [];
        $costs = [];

        // Fill all 7 days (even if no data)
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $costs[] = (float) ($costsByDate[$date] ?? 0);
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
