<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function newVenue()
    {
        return view('role/edit');
    }

    public function newVendor()
    {
        return view('role/edit');
    }

    public function newTalent()
    {
        return view('role/edit');
    }
}
