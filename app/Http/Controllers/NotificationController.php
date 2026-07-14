<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Full paginated notifications history */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /** Mark a single notification as read and redirect to the EO (or notifications index) */
    public function markRead(Request $request, string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        $eoId = $notification->data['eo_id'] ?? null;

        if ($eoId) {
            // Only redirect to the EO show page if the EO still exists (not deleted)
            $eo = \App\Models\ExecutiveOrder::withTrashed()->find($eoId);
            if ($eo && ! $eo->trashed()) {
                return redirect()->route('executive-orders.show', $eoId);
            }
        }

        // Fallback: go to the notifications index
        return redirect()->route('notifications.index');
    }

    /** Mark all notifications as read */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
