<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Mark a single notification as read and redirect to the EO */
    public function markRead(Request $request, string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        $eoId = $notification->data['eo_id'] ?? null;

        if ($eoId) {
            return redirect()->route('executive-orders.show', $eoId);
        }

        return back();
    }

    /** Mark all notifications as read */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
