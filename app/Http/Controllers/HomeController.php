<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function privacy() 
    {
        return view('privacy');
    }

    public function terms() 
    {
        return view('terms');
    }
}