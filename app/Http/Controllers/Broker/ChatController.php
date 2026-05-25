<?php

namespace App\Http\Controllers\Broker;

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

        return view('pages.chat.index', compact('messages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'message' => 'required|string',
            'receiver_id' => 'nullable|exists:users,id',
        ]);

        ChatMessage::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $data['receiver_id'] ?? null,
            'message'     => $data['message'],
            'sender_type' => $request->boolean('is_ai') ? 'ai' : 'user',
        ]);

        return redirect()->route('broker.chat.index')->with('success', 'Message sent.');
    }
}