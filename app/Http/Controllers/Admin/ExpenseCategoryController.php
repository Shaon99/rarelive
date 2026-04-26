<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['pageTitle'] = 'Expense-category';
        $data['expenseManagement'] = 'active';
        $data['expenseCategory'] = 'active';
        $data['expenseCategories'] = ExpenseCategory::when($request->has('trashed'), function ($query) {
            $query->onlyTrashed();
        })
            ->latest()
            ->get();

        return view('backend.expense.expense_category')->with($data);
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
        $request->validate([
            'name' => 'required|unique:expense_categories',
        ]);
        try {
            $expense = ExpenseCategory::Create([
                'name' => $request->name,
            ]);

            return response()->json(['expenseCategory' => $expense]);
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
            'name' => 'required|unique:expense_categories,name,'.$id,
        ]);

        $expensecategory = ExpenseCategory::find($id);
        $expensecategory->name = $request->name;
        $expensecategory->save();

        return back()->with('success', 'Expense Category Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ExpenseCategory::destroy($id);

        return back()->with('success', 'Expense Category Deleted successfully');
    }

    public function expensecategoryRestore($id)
    {
        ExpenseCategory::withTrashed()->find($id)->restore();

        return back()->with('success', 'Expense Category Successfully Restore');
    }

    public function expensecategoryDelete($id)
    {
        ExpenseCategory::onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('success', 'Expense Category Deleted successfully');
    }
}
