<?php

namespace Lmromax\LaravelAiGuard\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Lmromax\LaravelAiGuard\Models\AiPromptsLog;

class RecentLogs extends Component
{
    use WithPagination;

    public function render()
    {
        $logs = AiPromptsLog::with('user')
            ->latest()
            ->paginate(20);

        return view('ai-guard::livewire.recent-logs', [
            'logs' => $logs,
        ]);
    }
}
