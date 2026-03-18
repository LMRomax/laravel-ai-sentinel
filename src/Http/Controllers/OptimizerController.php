<?php

namespace Lmromax\LaravelAiSentinel\Http\Controllers;

use Illuminate\Routing\Controller;

class OptimizerController extends Controller
{
    public function index()
    {
        return view('ai-sentinel::optimizer');
    }
}
