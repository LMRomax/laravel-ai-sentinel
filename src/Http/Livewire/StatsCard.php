<?php

namespace Lmromax\LaravelAiGuard\Http\Livewire;

use Livewire\Component;
use Lmromax\LaravelAiGuard\Facades\AiGuard;

class StatsCards extends Component
{
    public $todayCost;

    public $todayRequests;

    public $monthCost;

    public $monthRequests;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $todayStats = AiGuard::getCostStats('day');
        $monthStats = AiGuard::getCostStats('month');

        $this->todayCost = number_format($todayStats['total_cost'] ?? 0, 2);
        $this->todayRequests = number_format($todayStats['total_requests'] ?? 0);

        $this->monthCost = number_format($monthStats['total_cost'] ?? 0, 2);
        $this->monthRequests = number_format($monthStats['total_requests'] ?? 0);
    }

    public function render()
    {
        return view('ai-guard::livewire.stats-cards');
    }
}
