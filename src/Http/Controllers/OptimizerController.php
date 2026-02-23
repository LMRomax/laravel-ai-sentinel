<?php

namespace Lmromax\LaravelAiGuard\Http\Controllers;

use Illuminate\Routing\Controller;

class OptimizerController extends Controller
{
    public function index()
    {
        return view('ai-guard::optimizer');
    }
}
