<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activityLogActiveClass = 'active';
        $pageTitle = 'Activity logs';
        $activities_active = 'active';

        $query = Activity::query();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $activities = $query->latest()->paginate();

        $events = Activity::select('event')->distinct()->pluck('event');

        if ($request->ajax()) {
            return view('backend.activity.table', compact('activities'))->render();
        }

        return view('backend.activity.index', compact('activities_active', 'activities', 'events', 'activityLogActiveClass', 'pageTitle'));
    }

    public function show($id)
    {
        $data['activityLogActiveClass'] = 'active';
        $data['activities_active'] = 'active';

        $data['activity'] = Activity::find($id);

        $data['pageTitle'] = $data['activity']->event.' Activity Information';

        return view('backend.activity.show')->with($data);
    }
}
