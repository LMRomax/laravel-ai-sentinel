<?php

use Livewire\Livewire;
use Lmromax\LaravelAiGuard\Http\Livewire\PromptOptimizer;

beforeEach(function () {
    $this->user = new \Illuminate\Foundation\Auth\User;
    $this->user->id = 1;
});

describe('Optimizer Page', function () {

    it('requires authentication', function () {
        $response = $this->get('/ai-guard/optimizer');
        $response->assertRedirect('/login');
    });

    it('displays optimizer page when authenticated', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-guard/optimizer');

        $response->assertOk();
        $response->assertSee('Prompt Optimizer');
    });

    it('displays the optimizer component', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-guard/optimizer');

        $response->assertSeeLivewire('ai-guard.prompt-optimizer');
    });

});

describe('Optimizer Component', function () {

    it('requires a prompt', function () {
        Livewire::test(PromptOptimizer::class)
            ->set('prompt', '')
            ->call('optimize')
            ->assertHasErrors(['prompt' => 'required']);
    });

    it('requires minimum prompt length', function () {
        Livewire::test(PromptOptimizer::class)
            ->set('prompt', 'Hi')
            ->call('optimize')
            ->assertHasErrors(['prompt' => 'min']);
    });

    it('optimizes a prompt successfully', function () {
        Livewire::test(PromptOptimizer::class)
            ->set('prompt', 'Please can you help me understand Laravel?')
            ->call('optimize')
            ->assertSet('result', fn ($result) => isset($result['optimized']) &&
                isset($result['tokens_saved']) &&
                isset($result['compression_ratio'])
            );
    });

    it('clears the form', function () {
        Livewire::test(PromptOptimizer::class)
            ->set('prompt', 'Test prompt')
            ->call('optimize')
            ->call('clear')
            ->assertSet('prompt', '')
            ->assertSet('result', null);
    });

    it('shows full optimization results', function () {
        $component = Livewire::test(PromptOptimizer::class)
            ->set('prompt', 'Please provide an explanation of Laravel framework')
            ->call('optimize');

        expect($component->get('result'))->toHaveKeys([
            'original',
            'optimized',
            'tokens_original',
            'tokens_optimized',
            'tokens_saved',
            'compression_ratio',
        ]);
    });

});
