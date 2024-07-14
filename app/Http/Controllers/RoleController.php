<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function create()
    {
        return view('role/edit');
    }

    public function store(Request $request): RedirectResponse
    {
        $subdomain = $request->name;

        $role = Role::create([
            'name' => $request->name,
            'subdomain' => $subdomain,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'phone' => $request->phone,
            'email' => $request->email,
            'address1' => $request->address1,
        ]);

        return redirect(route('dashboard'));
    }
}
