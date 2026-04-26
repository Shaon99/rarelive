<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UnitsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['pageTitle'] = 'Units';
        $data['other'] = 'active';
        $data['unit'] = 'active';
        $data['units'] = Unit::latest()->get();

        return view('backend.products.unit.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|unique:units',
            ]);

            Unit::create([
                'name' => $validatedData['name'],
            ]);

            $unit = Unit::latest()->first();

            return response()->json(['unit' => $unit]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
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
        //
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
            'name' => 'required|unique:units,name,'.$id,
        ]);

        $unit = Unit::find($id);
        $unit->name = $request->name;
        $unit->save();

        return back()->with('success', 'Unit Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unit = Unit::find($id);

        activity()
            ->performedOn($unit)
            ->causedBy(auth()->guard('admin')->user())
            ->event('unit_deleted')
            ->withProperties([
                'unit' => $unit,
            ])
            ->log($unit->name.' unit successfully deleted');

        $unit->delete();

        return back()->with('success', 'Unit Deleted successfully');
    }
}
