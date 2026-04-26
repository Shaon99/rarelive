<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = FacadesAuth::guard('admin')->user();

            return $next($request);
        });
    }

    public function index()
    {
        if (is_null($this->user) || ! $this->user->can('admin_user_list')) {
            abort(401);
        }

        $data['pageTitle'] = 'Admin List';
        $data['adminListActive'] = 'active';
        $data['admins'] = Admin::with('warehouse')->where('id', '!=', auth()->guard('admin')->user()->id)->latest()->get();

        return view('backend.administration.admin_user.list')->with($data);
    }

    public function create()
    {
        if (is_null($this->user) || ! $this->user->can('admin_user_add')) {
            abort(401);
        }
        $data['pageTitle'] = 'Create Admin';
        $data['adminAddActive'] = 'active';

        $data['roles'] = Role::all();
        $data['warehouse'] = Warehouse::all();

        $data['employees'] = Employee::select('id', 'employee_name', 'phone', 'email')->get();

        return view('backend.administration.admin_user.add')->with($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => [
                'required',
                'unique:admins',
                'not_regex:/^[\w\.]+@([\w-]+\.)+[\w-]{2,4}$/',
            ],
            'phone' => 'required|unique:admins',
            'email' => 'email|unique:admins',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $admin = new Admin();
        $admin->name = $request->name;
        $admin->phone = $request->phone;
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->warehouse_id = $request->warehouse_id;
        $admin->employee_id = $request->employee_id;
        $admin->password = bcrypt($request->password);

        if ($request->has('image')) {
            $path = filePath('admin');
            $size = '150x150';
            $filename = uploadImage($request->image, $path, $size);
            $admin->image = $filename;
        }
        $admin->save();

        $admin->assignRole($request->role);

        return redirect()->route('admin.index')->with('success', 'Admin User Created Successfully');
    }

    public function edit($id)
    {
        if (is_null($this->user) || ! $this->user->can('admin_user_edit')) {
            abort(401);
        }
        $data['pageTitle'] = 'Edit Admin';
        $data['adminListActive'] = 'active';

        $data['roles'] = Role::all();
        $data['warehouse'] = Warehouse::all();
        $data['admin'] = Admin::find($id);

        $data['employees'] = Employee::select('id', 'employee_name', 'phone', 'email')->get();

        return view('backend.administration.admin_user.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required|unique:admins,phone,'.$id,
            'email' => 'required|email|unique:admins,email,'.$id,
            'username' => [
                'required',
                'unique:admins,username,'.$id,
                'not_regex:/^[\w\.]+@([\w-]+\.)+[\w-]{2,4}$/',
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $admin = Admin::find($id);
        $admin->name = $request->name;
        $admin->phone = $request->phone;
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->warehouse_id = $request->warehouse_id;
        $admin->employee_id = $request->employee_id;
        if ($request->password) {
            $admin->password = bcrypt($request->password);
        }

        if ($request->has('image')) {
            $path = filePath('admin');
            $size = '150x150';
            $filename = uploadImage($request->image, $path, $size, $admin->image);
            $admin->image = $filename;
        }

        $admin->save();

        $admin->roles()->detach();

        $admin->assignRole($request->role);

        return redirect()->route('admin.index')->with('success', 'Admin User Updated Successfully');
    }

    public function destroy($id)
    {
        if (is_null($this->user) || ! $this->user->can('admin_user_delete')) {
            abort(401);
        }
        $admin = Admin::find($id);
        if ($admin) {
            $admin->roles()->detach();
            removeFile(filePath('admin').'/'.@$admin->image);
            $admin->delete();
        }

        return redirect()->back()->with('success', 'User deleted successfully');
    }

    public function profile()
    {
        $pageTitle = 'Profile';

        return view('backend.profile', compact('pageTitle'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required',
            'phone' => 'required|unique:admins,id,'.auth()->guard('admin')->user()->phone,
            'email' => 'required|email|unique:admins,id,'.auth()->guard('admin')->user()->email,
            'image' => 'sometimes|image|mimes:jpg,jpeg,png',
        ]);

        $admin = auth()->guard('admin')->user();

        if ($request->has('image')) {

            $path = filePath('admin');

            $size = '150x150';

            $filename = uploadImage($request->image, $path, $size, $admin->image);

            $admin->image = $filename;
        }
        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->phone = $request->phone;
        $admin->email = $request->email;
        $admin->save();

        return redirect()->back()->with('success', 'Admin Profile Update Success');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:5|confirmed',
        ]);

        $admin = auth()->guard('admin')->user();

        if (! Hash::check($request->old_password, $admin->password)) {
            $notify[] = ['error', 'Password Does not match'];

            return back()->withNotify($notify);
        }

        $admin->password = bcrypt($request->password);
        $admin->save();

        return back()->with('success', 'Password changed Successfully');
    }
}
