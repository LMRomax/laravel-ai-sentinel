<?php

namespace Lmromax\LaravelAiSentinel\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Lmromax\LaravelAiSentinel\Models\AiPromptsLog;

class RecentLogs extends Component
{
    use WithPagination;

    public function render()
    {
        $logs = AiPromptsLog::with('user')
            ->latest()
            ->paginate(20);

        return view('ai-sentinel::livewire.recent-logs', [
            'logs' => $logs,
        ]);
    }
}
