<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Fake user instance only for actingAs
beforeEach(function () {
    $this->user = new User;
    $this->user->id = 1;
});

describe('Optimizer Page', function () {

    it('requires authentication', function () {
        $response = $this->get('/ai-guard/optimizer');

        // Ton package redirige vers /login via le middleware auth
        $response->assertRedirect('/login');
    });

    it('renders the optimizer page when authenticated', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-guard/optimizer');

        $response->assertOk();
        $response->assertSee('Prompt Optimizer');
    });

    it('loads the Livewire component', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-guard/optimizer');

        $response->assertSeeLivewire('ai-guard.prompt-optimizer');
    });
});
