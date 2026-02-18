<?php

namespace Lmromax\LaravelAiGuard\Http\Controllers;

use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('ai-guard::dashboard');
    }
}