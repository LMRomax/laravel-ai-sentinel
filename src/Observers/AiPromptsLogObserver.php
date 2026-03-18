<?php

namespace Lmromax\LaravelAiSentinel\Observers;

use Lmromax\LaravelAiSentinel\Models\AiPromptsLog;
use Lmromax\LaravelAiSentinel\Services\AlertService;

class AiPromptsLogObserver
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    /**
     * Handle the AiPromptsLog "created" event.
     *
     * @param  AiPromptsLog  $AiPromptsLog  The log entry that was created
     */
    public function created(AiPromptsLog $AiPromptsLog): void
    {
        // Check spending limits after each log creation
        $this->alertService->checkLimits();
    }
}
