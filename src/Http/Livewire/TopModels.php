<?php

namespace Lmromax\LaravelAiSentinel\Http\Livewire;

use Livewire\Component;
use Lmromax\LaravelAiSentinel\Models\AiPromptsLog;

class TopModels extends Component
{
    public $topModels;

    public function mount()
    {
        $this->loadTopModels();
    }

    public function loadTopModels()
    {
        // Get all logs from this month
        $logs = AiPromptsLog::where('created_at', '>=', now()->startOfMonth())
            ->get();

        // Group by provider + model in PHP
        $this->topModels = $logs->groupBy(function ($log) {
            return $log->provider.'|'.$log->model; // Combine provider + model
        })
            ->map(function ($modelLogs) {
                $first = $modelLogs->first();

                return (object) [
                    'provider' => $first->provider,
                    'model' => $first->model,
                    'request_count' => $modelLogs->count(),
                    'total_cost' => $modelLogs->sum('cost'),
                    'avg_cost' => $modelLogs->avg('cost'),
                    'total_tokens_input' => $modelLogs->sum('tokens_input'),
                    'total_tokens_output' => $modelLogs->sum('tokens_output'),
                ];
            })
            ->sortByDesc('total_cost')
            ->take(10)
            ->values(); // Reset keys for clean array
    }

    public function render()
    {
        return view('ai-sentinel::livewire.top-models');
    }
}
