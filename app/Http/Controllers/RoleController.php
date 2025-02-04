<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $users = User::all();
        $roles = Role::all();
        return view('admin.roles.index', compact('users', 'roles'));
    }

    public function assignRole(Request $request, User $user)
    {
        $user->syncRoles($request->roles);
        return back()->with('success', 'Rôles mis à jour');
    }
}
