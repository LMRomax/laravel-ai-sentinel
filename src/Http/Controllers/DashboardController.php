<?php

namespace Lmromax\LaravelAiSentinel\Http\Controllers;

use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('ai-sentinel::dashboard');
    }
}
