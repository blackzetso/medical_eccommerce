<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TestController extends Controller
{
    public function test()
    {
        return Inertia::render('Front/Theme1/Test', [
            'message' => 'This is a test page'
        ]);
    }
}
