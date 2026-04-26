<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecycleBin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecycleBinController extends Controller
{
    public function index(Request $request)
    {
        $recycleBinActiveClass = 'active';
        $pageTitle = 'Recycle Bin';
        $activities_active = 'active';

        $query = RecycleBin::query();

        if ($request->has('model') && $request->model) {
            $query->where('model', $request->model);
        }

        // Filter by date range
        if ($request->has('date_range') && $request->date_range) {
            $dates = explode(' - ', $request->date_range);

            if (count($dates) == 2) {
                $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
                $query->whereBetween('deleted_at', [$startDate, $endDate]);
            }
        }

        // Paginate the filtered results
        $recycleBins = $query->latest()->paginate(20);

        $models = RecycleBin::select('model')->distinct()->pluck('model');

        if ($request->ajax()) {
            return view('backend.recycle-bin.table', compact('recycleBins'));
        }

        return view('backend.recycle-bin.index', compact('activities_active', 'recycleBins', 'models', 'recycleBinActiveClass', 'pageTitle'));
    }

    // Restore an item from the Recycle Bin
    public function restore($id)
    {
        $item = RecycleBin::findOrFail($id);
        $data = json_decode($item->data, true);
        $modelClass = $item->model;

        if (! class_exists($modelClass)) {
            return redirect()->back()->with('error', 'Model class not found.');
        }

        // Extract related data (example: 'purchaseProducts') from main data
        $relatedData = [];
        $mainData = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                // Likely a related table (e.g., purchaseProducts)
                $relatedData[$key] = $value;
            } else {
                $mainData[$key] = $value;
            }
        }

        // Recreate the main model
        $restoredModel = $modelClass::create($mainData);

        // Restore related models
        foreach ($relatedData as $relation => $items) {
            if (method_exists($restoredModel, $relation)) {
                foreach ($items as $relatedItem) {
                    // Add the foreign key manually (if missing)
                    $restoredModel->$relation()->create($relatedItem);
                }
            }
        }
        // Delete from recycle bin
        $item->delete();

        return redirect()->back()->with('success', 'Deleted data restored successfully.');
    }

    // Permanently delete an item from the Recycle Bin
    public function deleteForever($id)
    {
        $item = RecycleBin::findOrFail($id);

        $item->delete();

        return redirect()->back()->with('success', 'Item permanently deleted.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No items selected.');
        }

        RecycleBin::whereIn('id', $ids)->forceDelete();

        return back()->with('success', 'Selected items have been permanently deleted.');
    }
}
