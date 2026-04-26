<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PruneOldLogsController extends Controller
{
    public function __invoke(Request $request)
    {
        $days = max(0, min(3650, (int) $request->input('days', 3)));

        $exitCode = Artisan::call('admin:prune-old-logs', [
            '--days' => (string) $days,
        ]);

        if ($exitCode !== 0) {
            return redirect()->back()->with('error', __('The cleanup command did not complete successfully.'));
        }

        $message = trim(str_replace(["\r\n", "\n", "\r"], ' ', Artisan::output()));

        return redirect()->back()->with('success', $message !== '' ? $message : __('Old notifications and activity logs were pruned.'));
    }
}
