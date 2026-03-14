<?php

namespace App\Http\Controllers\AdminBackend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class AdminContactsController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $contacts     = $query->paginate(20);
        $unreadCount  = Contact::unread()->count();

        return view('admin-dashboard.contacts-view', compact('contacts', 'unreadCount'));
    }

    public function show(Contact $contact)
    {
        if ($contact->isUnread()) {
            $contact->markAsRead();
        }
        return view('admin-dashboard.contacts-detail', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'status'     => 'required|in:unread,read,replied,archived',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if ($data['status'] === 'replied' && !$contact->replied_at) {
            $data['replied_at'] = now();
        }

        $contact->update($data);

        return response()->json(['ok' => true, 'message' => 'Contact updated.']);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(['ok' => true, 'message' => 'Contact deleted.']);
    }
}