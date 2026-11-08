<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\AIService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(private AIService $ai) {}

    public function index(): View
    {
        $messages = ChatMessage::aiConversationFor(auth()->id())->oldest('id')->get();

        return view('pages.chat.index', compact('messages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['message' => 'required|string|max:1000']);

        // Earlier turns, read before this message is saved.
        $history = ChatMessage::aiHistoryFor(auth()->id());

        ChatMessage::create([
            'sender_id'   => auth()->id(),
            'message'     => $data['message'],
            'sender_type' => 'user',
        ]);

        $aiReply = $this->ai->chat($data['message'], auth()->id(), 'agent', $history);

        ChatMessage::create([
            'sender_id'   => null,
            'receiver_id' => auth()->id(),
            'message'     => $aiReply,
            'sender_type' => 'ai',
        ]);

        return redirect()->route('agent.chat.index');
    }
}
