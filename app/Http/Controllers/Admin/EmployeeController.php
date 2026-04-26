<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $data['pageTitle'] = 'Employee Management';
        $data['employeeManagementActiveClass'] = 'active';

        $data['employees'] = Employee::all();

        return view('backend.employee.index')->with($data);
    }

    public function create()
    {
        $data['pageTitle'] = 'Employee Create';
        $data['employeeManagementActiveClass'] = 'active';

        return view('backend.employee.create')->with($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'phone' => 'required|unique:employees',
            'basic_salary' => 'required',
            'joining_date' => 'required',
            'documents' => 'nullable|array',
            'documents.*' => 'max:2048',
            'image' => 'nullable|max:2048',
        ]);

        $path = filePath('employee');

        $documents = [];
        if ($request->has('documents')) {
            $documents = array_map(function ($document) use ($path) {
                return uploadImage($document, $path, null, null);
            }, $request->file('documents'));
        }

        $image = null;

        if ($request->has('image')) {
            $path = filePath('employee');
            $size = '360x360';
            $filename = uploadImage($request->image, $path, $size, null);
            $image = $filename;
        }

        Employee::create([
            'employee_name' => $request->employee_name,
            'designation' => $request->designation,
            'phone' => $request->phone,
            'email' => $request->email,
            'basic_salary' => $request->basic_salary,
            'joining_date' => $request->joining_date,
            'image' => $image,
            'documents' => json_encode($documents),
        ]);

        return redirect()->route('admin.employee.index')->with('success', 'Employee added successfully.');
    }

    public function edit($id)
    {
        $data['employee'] = Employee::findOrFail($id);

        $data['documents'] = json_decode($data['employee']->documents, true);

        $data['pageTitle'] = $data['employee']->employee_name.' Details';
        $data['employeeManagementActiveClass'] = 'active';

        return view('backend.employee.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'employee_name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'phone' => 'required|unique:employees,phone,'.$id,
            'basic_salary' => 'required',
            'joining_date' => 'required|date',
            'documents' => 'nullable|array',
            'documents.*' => 'max:2048',
            'image' => 'nullable|max:2048',
        ]);

        $image = $employee->image;

        if ($request->hasFile('image')) {
            $path = filePath('employee');
            $size = '360x360';
            $filename = uploadImage($request->image, $path, $size, $image);
            $image = $filename;
        }

        $documents = json_decode($employee->documents, true) ?: [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                if ($document) {
                    $path = filePath('employee');
                    $filename = uploadImage($document, $path, null, null);
                    $documents[] = $filename;
                }
            }
        }

        $employee->update([
            'employee_name' => $request->employee_name,
            'designation' => $request->designation,
            'phone' => $request->phone,
            'email' => $request->email,
            'basic_salary' => $request->basic_salary,
            'joining_date' => $request->joining_date,
            'image' => $image,
            'documents' => json_encode($documents),
        ]);

        return redirect()->route('admin.employee.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        $employee->delete();

        return back()->with('success', 'Employee deleted successfully.');
    }
}
