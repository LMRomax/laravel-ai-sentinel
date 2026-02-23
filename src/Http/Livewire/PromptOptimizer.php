<?php

namespace Lmromax\LaravelAiGuard\Http\Livewire;

use Livewire\Component;
use Lmromax\LaravelAiGuard\Facades\AiGuard;

class PromptOptimizer extends Component
{
    public $prompt = '';

    public $result = null;

    public $isOptimizing = false;

    protected $rules = [
        'prompt' => 'required|string|min:10',
    ];

    public function optimize()
    {
        $this->validate();

        $this->isOptimizing = true;

        // Optimize the prompt
        $this->result = AiGuard::optimize($this->prompt);

        $this->isOptimizing = false;
    }

    public function clear()
    {
        $this->prompt = '';
        $this->result = null;
        $this->resetValidation();
    }

    public function copyOptimized()
    {
        $this->dispatch('prompt-copied');
    }

    public function render()
    {
        return view('ai-guard::livewire.prompt-optimizer');
    }
}
