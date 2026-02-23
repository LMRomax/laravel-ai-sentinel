<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = new \Illuminate\Foundation\Auth\User;
    $this->user->id = 1;
});

describe('Dashboard', function () {

    it('requires authentication', function () {
        $response = $this->get('/ai-guard');
        $response->assertRedirect('/login');
    });

    it('shows dashboard when authenticated', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-guard');

        $response->assertOk();
        $response->assertSee('AI Guard');
    });

    it('displays the stats cards component', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-guard');

        $response->assertSeeLivewire('ai-guard.stats-cards');
    });

    it('displays the cost chart component', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-guard');

        $response->assertSeeLivewire('ai-guard.cost-chart');
    });

    it('displays the recent logs component', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-guard');

        $response->assertSeeLivewire('ai-guard.recent-logs');
    });
});
