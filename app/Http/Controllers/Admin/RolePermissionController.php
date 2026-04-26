<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['pageTitle'] = 'Role List';
        $data['administration_active'] = 'active';
        $data['role_list_active'] = 'active';
        $data['roles'] = Role::get();

        return view('backend.administration.role.list')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['pageTitle'] = 'Create New Role';
        $data['administration_active'] = 'active';
        $data['role_add_active'] = 'active';
        $data['permissions'] = Permission::where('submodule_id', 0)->get();

        return view('backend.administration.role.add')->with($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'unique:roles|required',
            'group_a' => ['required', 'array', 'min:1'],
        ], [
            'group_a.required' => 'The permissions is required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $permissions = $request->group_a;

        // Create a new Role instance
        $role = new Role();
        $role->name = $request->input('name');
        $role->guard_name = 'admin';
        $role->save();

        foreach ($permissions as $permission) {
            $perm = Permission::where('id', $permission)->where('guard_name', 'admin')->first();
            if ($perm) {
                $role->givePermissionTo($perm);
            }
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created Successfully');
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);

        $permissionIds = $role->permissions()->pluck('submodule_id');

        $parentSelectedPermissions = Permission::whereIn('id', $permissionIds)->get();

        $permissions = $role->permissions()->get();

        $data = [
            'administration_active' => 'active',
            'role_list_active' => 'active',
            'role' => $role,
            'permissions' => $permissions,
            'pageTitle' => 'Role Permission Show - '.$role->name,
            'parentSelectedPermissions' => $parentSelectedPermissions,
        ];

        return view('backend.administration.role.view', $data);
    }

    public function edit($id)
    {

        $data['role'] = Role::find($id);

        $data['pageTitle'] = 'Role Permission Edit - '.' '.$data['role']->name;
        $data['administration_active'] = 'active';
        $data['role_list_active'] = 'active';
        $data['permissions'] = Permission::where('submodule_id', 0)->get();

        $data['getPermissionsid'] = $data['role']->permissions()->get();

        return view('backend.administration.role.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,'.$id,
            'group_a' => ['required', 'array', 'min:1'],
        ], [
            'group_a.required' => 'The permissions is required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $permissions = $request->group_a;

        $role = Role::find($id);
        $role->name = $request->get('name');
        $role->guard_name = 'admin';
        $role->save();
        $role->syncPermissions();

        foreach ($permissions as $permission) {
            $perm = Permission::where('id', $permission)->where('guard_name', 'admin')->first();
            if ($perm) {
                $role->givePermissionTo($perm);
            }
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->name == 'Admin') {
            return redirect()->back()->with('error', 'Admin Role can not be deleted');
        }

        $role->delete();

        return redirect()->back()->with('success', 'Role delete successfully');
    }

    public function getsubmodule($id)
    {

        $permissions = Permission::where('submodule_id', $id)->get();
        $permission = Permission::where('id', $id)->first();

        return response()->json(['permission' => $permission, 'permissions' => $permissions]);
    }

    public function permission()
    {
        $data['pageTitle'] = 'Permission List';
        $data['administration_active'] = 'active';
        $data['permission_list_active'] = 'active';
        $data['permissions'] = Permission::latest()->get();

        return view('backend.administration.role.permission')->with($data);
    }

    public function permissionPost(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'display_name' => 'required',
            'submodule' => 'required',
        ]);

        Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'submodule_id' => $request->submodule,
            'guard_name' => 'admin',
        ]);

        return redirect()->back()->with('success', 'Permissions added Successfully');
    }

    public function permissionUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'display_name' => 'required',
            'submodule' => 'required',
        ]);
        $data = Permission::find($id);
        $data->name = $request->name;
        $data->display_name = $request->display_name;
        $data->submodule_id = $request->submodule;

        $data->save();

        return redirect()->back()->with('success', 'Permissions updated Successfully');
    }

    public function destroyPermission($id)
    {
        Permission::find($id)->delete();

        return redirect()->back()->with('success', 'Permission delete successfully');
    }
}
