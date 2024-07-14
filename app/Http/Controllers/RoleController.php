<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function create()
    {
        return view('role/edit');
    }

    public function store(Request $request): RedirectResponse
    {
        $subdomain = str_replace([' ', '.'], ['-', ''], strtolower(trim($request->name)));

        $role = new Role;
        $role->fill($request->all());
        $role->subdomain = $subdomain;
        $role->user_id = $request->user()->id;
        $role->save();

        dd('role: ' . $role->id);

        return redirect(route('dashboard'));
    }
}
