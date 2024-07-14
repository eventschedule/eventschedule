<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function create()
    {
        return view('role/edit');
    }
}
