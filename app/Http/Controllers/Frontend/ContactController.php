<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        // Save to DB so admin can see it in dashboard
        Contact::create([
            ...$validated,
            'ip'     => $request->ip(),
            'status' => 'unread',
        ]);

        return redirect()->route('home')
            ->with('success', 'Thanks! Your message has been sent. We\'ll be in touch shortly.');
    }
}