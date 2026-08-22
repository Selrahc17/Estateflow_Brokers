<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|in:Property Inquiry,Reservation Question,Document Submission,General Inquiry,Complaint / Feedback',
            'message' => 'required|string|max:5000',
        ]);

        $data['status'] = 'new';
        ContactMessage::create($data);

        return back()->with('success', 'Your message has been sent successfully.');
    }
}
