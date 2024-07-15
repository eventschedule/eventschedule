<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function view($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = [
            'role' => $role,
        ];

        return view('role/view', $data);
    }

    public function viewVenues()
    {
        return view('role/index');
    }

    public function viewTalent()
    {
        return view('role/index');
    }

    public function viewVendors()
    {
        return view('role/index');
    }

    public function create()
    {
        $role = new Role;

        $data = [
            'role' => $role,
        ];

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

        return redirect(url($role->type . '/' . $role->subdomain));
    }

    public function edit($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = [
            'role' => $role,
        ];

        return view('role/edit');
    }

    public function update(RoleUpdateRequest $request): RedirectResponse
    {
        $role = new Role;
        $role->fill($request->all());
        $role->save();

        return redirect(url($role->type . '/' . $role->subdomain));
    }
}
