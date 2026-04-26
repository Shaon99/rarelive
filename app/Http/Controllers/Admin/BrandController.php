<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['pageTitle'] = 'Brands';
        $data['other'] = 'active';
        $data['brand'] = 'active';
        $data['brands'] = Brand::latest()->get();

        return view('backend.products.brand.index')->with($data);
    }

    public function productAttribute()
    {
        $data['pageTitle'] = 'Product Attribute';
        $data['other'] = 'active';
        $data['attribute_list'] = 'active';
        $data['attributes'] = ProductAttribute::with('values')->latest()->get();

        return view('backend.products.attribute.index')->with($data);
    }

    public function productAttributeStore(Request $request)
    {
        $request->validate([
            'attribute_name' => 'required|unique:product_attributes,name|string|max:255',
            'attribute_values' => 'required|array|min:1',
            'attribute_values.*' => 'required|string|max:255',
        ]);

        $attribute = ProductAttribute::create([
            'name' => $request->attribute_name,
        ]);

        foreach ($request->attribute_values as $value) {
            $attribute->values()->create(['value' => $value]);
        }

        return redirect()->back()->with('success', 'Product Attribute and values added successfully!');
    }

    public function productAttributeUpdate(Request $request, $id)
    {
        $request->validate([
            'attribute_id' => 'required|exists:product_attributes,id',
            'attribute_values' => 'required|array',
            'attribute_values.*' => 'required|string|max:255',
            'attribute_name' => 'required|string|max:255',
        ]);

        $attribute = ProductAttribute::findOrFail($id);

        $attribute->update(['name' => $request->attribute_name]);

        // Remove old values
        $attribute->values()->delete();

        // Add new values
        foreach ($request->attribute_values as $value) {
            $attribute->values()->create(['value' => $value]);
        }

        return redirect()->back()->with('success', 'Product Attribute updated successfully!');
    }

    public function productAttributeDelete($id)
    {
        $attribute = ProductAttribute::findOrFail($id);

        // Delete related attribute values
        $attribute->values()->delete();

        // Delete the attribute itself
        $attribute->delete();

        return redirect()->back()->with('success', 'Product Attribute deleted successfully!');
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
        $validatedData = $request->validate([
            'name' => 'required|unique:brands',
        ]);

        try {
            $brand = Brand::create([
                'name' => $validatedData['name'],
            ]);

            return response()->json(['brand' => $brand]);
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
            'name' => 'required|unique:brands,name,'.$id,
        ]);

        $brnad = Brand::find($id);
        $brnad->name = $request->name;
        $brnad->save();

        return back()->with('success', 'Brand Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Brand::destroy($id);

        return back()->with('success', 'Brand Deleted successfully');
    }
}
