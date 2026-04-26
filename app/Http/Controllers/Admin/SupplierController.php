<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['pageTitle'] = 'Suppliers';
        $data['supplierManagement'] = 'active';
        $data['supllier'] = 'active';
        $data['suppliers'] = Supplier::latest()->get();

        return view('backend.supplier.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['pageTitle'] = 'Supplier Create';
        $data['supplierManagement'] = 'active';
        $data['supllier'] = 'active';

        return view('backend.supplier.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:suppliers',
            'contact_person' => 'required',
            'phone' => 'required|unique:suppliers',
        ]);

        if ($request->ajax()) {

            Supplier::create([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'description' => $request->description,
            ]);

            $supplierReturn = Supplier::latest()->first();

            return response()->json([
                'supplierReturn' => $supplierReturn,
                'message' => 'supplier create successfully',
            ]);
        }

        Supplier::create([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.supplier.index')->with('success', 'supplier created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['pageTitle'] = 'Supplier Edit';
        $data['supplierManagement'] = 'active';
        $data['supllier'] = 'active';
        $data['supplier'] = Supplier::find($id);

        return view('backend.supplier.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:suppliers,name,'.$id,
            'contact_person' => 'required',
            'phone' => 'required|unique:suppliers,phone,'.$id,
        ]);

        $supplier = Supplier::find($id);
        $supplier->name = $request->name;
        $supplier->contact_person = $request->contact_person;
        $supplier->phone = $request->phone;
        $supplier->email = $request->email;
        $supplier->address = $request->address;
        $supplier->description = $request->description;
        $supplier->save();

        return redirect()->route('admin.supplier.index')->with('success', 'supplier updated successsfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Supplier::destroy($id);

        return back()->with('success', 'supplier deleted successfully');
    }

    public function supplierPurchases($id)
    {
        $data['suppliers'] = Supplier::with('purchases')->find($id);

        $data['pageTitle'] = 'Purchases from - '.$data['suppliers']->name;
        $data['supplierManagement'] = 'active';
        $data['supllier'] = 'active';

        return view('backend.supplier.purchases')->with($data);
    }
}
