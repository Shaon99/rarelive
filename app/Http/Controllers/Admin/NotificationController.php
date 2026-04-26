<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $data['notificationCenterActiveClass'] = 'active';
        $data['pageTitle'] = 'Notification Center';
        $data['activities_active'] = 'active';

        $data['notifications'] = auth()->guard('admin')->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('backend.notifications.index')->with($data);
    }

    // Mark all specific notification as read
    public function markNotification()
    {
        auth()->guard('admin')->user()
            ->unreadNotifications
            ->markAsRead();

        return redirect()->back()->with('success', 'All notifications are marked as reed');
    }

    // Mark a specific notification as read
    public function markAsRead($notificationId)
    {
        $notification = auth()->guard('admin')->user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }

        return redirect()->back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead(Request $request)
    {
        auth()->guard('admin')->user()->unreadNotifications->markAsRead();

        return response()->json(['status' => 'success']);
    }
}
