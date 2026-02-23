<?php

namespace Lmromax\LaravelAiGuard\Observers;

use Lmromax\LaravelAiGuard\Models\AiPromptsLog;
use Lmromax\LaravelAiGuard\Services\AlertService;

class AiPromptsLogObserver
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    /**
     * Handle the AiPromptsLog "created" event.
     * 
     * @param AiPromptsLog $AiPromptsLog The log entry that was created
     */
    public function created(AiPromptsLog $AiPromptsLog): void
    {
        // Check spending limits after each log creation
        $this->alertService->checkLimits();
    }
}
