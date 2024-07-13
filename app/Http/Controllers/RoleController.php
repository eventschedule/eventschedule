<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function newVenue()
    {
        return view('role/new');
    }

    public function newVendor()
    {
        return view('role/new');
    }

    public function newTalent()
    {
        return view('role/new');
    }
}
