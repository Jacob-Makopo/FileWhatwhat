<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view notifications');

        $notifications = Auth::user()->notifications()
            ->latest()
            ->paginate(20);

        // Mark as read if requested
        if ($request->mark_as_read) {
            Auth::user()->unreadNotifications->markAsRead();
        }

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }

    public function markAsRead($id)
    {
        $this->authorize('view notifications');

        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        $this->authorize('view notifications');

        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function destroy($id)
    {
        $this->authorize('view notifications');

        Auth::user()->notifications()->findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }

    public function clearAll()
    {
        $this->authorize('view notifications');

        Auth::user()->notifications()->delete();

        return redirect()->back()->with('success', 'All notifications cleared.');
    }
}
