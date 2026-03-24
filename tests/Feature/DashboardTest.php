<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = new User;
    $this->user->id = 1;
});

describe('Dashboard', function () {

    it('requires authentication', function () {
        $response = $this->get('/ai-sentinel');
        $response->assertRedirect('/login');
    });

    it('shows dashboard when authenticated', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-sentinel');

        $response->assertOk();
        $response->assertSee('AI Sentinel');
    });

    it('displays the stats cards component', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-sentinel');

        $response->assertSeeLivewire('ai-sentinel.stats-cards');
    });

    it('displays the cost chart component', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-sentinel');

        $response->assertSeeLivewire('ai-sentinel.cost-chart');
    });

    it('displays the recent logs component', function () {
        $this->actingAs($this->user);

        $response = $this->get('/ai-sentinel');

        $response->assertSeeLivewire('ai-sentinel.recent-logs');
    });
});
