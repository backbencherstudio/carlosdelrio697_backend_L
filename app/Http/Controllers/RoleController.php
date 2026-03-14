<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\RoleHasPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class RoleController extends Controller
{
    public function index()
    {
        return view('role.index');
    }

    public function data()
    {
        $roles = Role::with('permissions')->latest()->get();

        $data = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('route_name'),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => strtolower($request->name),
                'guard_name' => 'web',
            ]);

            foreach ($request->permissions as $permissionId => $types) {
                $exists = RoleHasPermission::where('role_id', $role->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$exists) {
                    $saved = RoleHasPermission::create([
                        'role_id' => $role->id,
                        'permission_id' => $permissionId,
                    ]);

                    if (!$saved) {
                        throw new \Exception("Failed to save permission ID {$permissionId}");
                    }
                }
            }

            DB::commit();

            return redirect()->route('role.index')->with('success', 'Role created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();

        return view('role.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'required|array|min:1',
        ]);

        $role->update([
            'name' => strtolower($request->name),
        ]);

        RoleHasPermission::where('role_id', $role->id)->delete();

        foreach ($request->permissions as $permission_id => $types) {
            foreach ($types as $type) {
                RoleHasPermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $permission_id,
                ]);
            }
        }

        return redirect()->route('role.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $assigned = DB::table('model_has_roles')->where('role_id', $role->id)->exists();

        if ($assigned) {
            return redirect()->back()->with('error', 'Cannot delete this role because it is assigned to one or more users.');
        }

        RoleHasPermission::where('role_id', $role->id)->delete();
        $role->delete();

        return redirect()->route('role.index')->with('success', 'Role deleted successfully.');
    }

}
