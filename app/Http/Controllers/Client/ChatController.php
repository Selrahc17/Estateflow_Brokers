<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $messages = ChatMessage::where('sender_id', auth()->id())
            ->orWhere('receiver_id', auth()->id())
            ->with(['sender', 'receiver'])
            ->latest()
            ->paginate(50);

        return view('pages.client.chat.index', compact('messages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['message' => 'required|string']);

        ChatMessage::create([
            'sender_id'   => auth()->id(),
            'message'     => $data['message'],
            'sender_type' => 'user',
        ]);

        return redirect()->route('client.account.chat')->with('success', 'Message sent.');
    }
}