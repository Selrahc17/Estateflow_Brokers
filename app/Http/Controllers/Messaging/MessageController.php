<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $contacts = $this->contacts($user);
        $contactIds = $contacts->pluck('id');

        ChatMessage::where('receiver_id', $user->id)
            ->whereIn('sender_id', $contactIds)
            ->where('sender_type', 'user')
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        ChatMessage::where('receiver_id', $user->id)
            ->whereIn('sender_id', $contactIds)
            ->where('sender_type', 'user')
            ->whereNull('seen_at')
            ->update(['seen_at' => now(), 'is_read' => true]);

        $messages = ChatMessage::where('sender_type', 'user')
            ->where(function ($query) use ($user, $contactIds) {
                $query->where(function ($query) use ($user, $contactIds) {
                    $query->where('sender_id', $user->id)->whereIn('receiver_id', $contactIds);
                })->orWhere(function ($query) use ($user, $contactIds) {
                    $query->whereIn('sender_id', $contactIds)->where('receiver_id', $user->id);
                });
            })
            ->with(['sender', 'receiver'])
            ->oldest()
            ->get();

        $messagesByContact = $messages->groupBy(function (ChatMessage $message) use ($user) {
            return $message->sender_id === $user->id ? $message->receiver_id : $message->sender_id;
        });

        return view('pages.messages.index', compact('contacts', 'messagesByContact'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validate([
            'receiver_id' => ['required', 'integer'],
            'message' => ['nullable', 'string', 'max:2000', 'required_without:photo'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $contactIds = $this->contacts($user)->pluck('id');
        abort_unless(in_array((int) $data['receiver_id'], $contactIds, true), 403);

        $attachment = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('chat', 'public');
            $attachment = Storage::disk('public')->url($path);
        }

        ChatMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $data['receiver_id'],
            'message' => $data['message'] ?? '',
            'attachment' => $attachment,
            'sender_type' => 'user',
        ]);

        return redirect()->route($user->role === 'broker' ? 'broker.messages.index' : 'agent.messages.index', ['contact' => $data['receiver_id']]);
    }

    private function contacts(User $user)
    {
        if ($user->role === 'broker') {
            return $user->agents()->orderBy('name')->get();
        }

        $broker = $user->broker ? collect([$user->broker]) : collect();
        $clients = Client::where('broker_id', $user->id)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        return $broker->merge($clients)->unique('id')->values();
    }
}
