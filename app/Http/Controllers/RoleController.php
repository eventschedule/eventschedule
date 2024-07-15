<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function create()
    {
        return view('role/edit');
    }

    public function store(RoleCreateRequest $request): RedirectResponse
    {
        $user = $request->user();        
        $subdomain = str_replace([' ', '.'], ['-', ''], strtolower(trim($request->name)));

        $role = new Role;
        $role->fill($request->all());
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
        $role->save();

        $user->roles()->attach($role->id);

        return redirect(route('dashboard'));
    }
}
