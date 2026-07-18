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

    /** Mark a single notification as read and redirect to the document (or notifications index) */
    public function markRead(Request $request, string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        $docId = $notification->data['doc_id'] ?? null;

        if ($docId) {
            // Only redirect to the document show page if it still exists (not deleted)
            $doc = \App\Models\Document::withTrashed()->find($docId);
            if ($doc && ! $doc->trashed()) {
                return redirect()->route('documents.show', $docId);
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
