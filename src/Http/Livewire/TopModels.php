<?php

namespace Lmromax\LaravelAiGuard\Http\Livewire;

use Livewire\Component;
use Lmromax\LaravelAiGuard\Models\AiPromptsLog;

class TopModels extends Component
{
    public $topModels;

    public function mount()
    {
        $this->loadTopModels();
    }

    public function loadTopModels()
    {
        $this->topModels = AiPromptsLog::selectRaw('
                provider,
                model,
                COUNT(*) as request_count,
                SUM(cost) as total_cost,
                AVG(cost) as avg_cost,
                SUM(tokens_input) as total_tokens_input,
                SUM(tokens_output) as total_tokens_output
            ')
            ->where('created_at', '>=', now()->startOfMonth())
            ->groupBy('provider', 'model')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('ai-guard::livewire.top-models');
    }
}
