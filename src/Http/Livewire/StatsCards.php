<?php

namespace Lmromax\LaravelAiSentinel\Http\Livewire;

use Livewire\Component;
use Lmromax\LaravelAiSentinel\Facades\AiSentinel;

class StatsCards extends Component
{
    public $todayCost;

    public $todayRequests;

    public $monthCost;

    public $monthRequests;

    public $todayCostRaw;

    public $monthCostRaw;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $todayStats = AiSentinel::getCostStats('day');
        $monthStats = AiSentinel::getCostStats('month');

        $this->todayCost = number_format($todayStats['total_cost'] ?? 0, 2);
        $this->todayRequests = number_format($todayStats['total_requests'] ?? 0);

        $this->monthCost = number_format($monthStats['total_cost'] ?? 0, 2);
        $this->monthRequests = number_format($monthStats['total_requests'] ?? 0);

        $this->todayCostRaw = $todayStats['total_cost'] ?? 0;
        $this->monthCostRaw = $monthStats['total_cost'] ?? 0;
    }

    public function render()
    {
        return view('ai-sentinel::livewire.stats-cards');
    }
}
