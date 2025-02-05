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
        if ($request->has('roles')) {
            foreach ($request->roles as $roleName) {
                if ($user->hasRole($roleName)) {
                    $user->removeRole($roleName); // ❌ Supprime le rôle si déjà présent
                } else {
                    $user->assignRole($roleName); // ✅ Ajoute le rôle sinon
                }
            }
        }

        return response()->json(['success' => true, 'roles' => $user->roles->pluck('name')]);
    }

}
