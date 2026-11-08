<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use App\Support\ChatFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The chat bubble on the public site. Guests keep their short history in the
 * session; signed-in buyers also get answers about their own records.
 */
class ChatbotController extends Controller
{
    public const HISTORY_LIMIT = 10;

    public function __invoke(Request $request, AIService $ai): JsonResponse
    {
        $data = $request->validate(['message' => 'required|string|max:1000']);

        $user = $request->user();
        [$userId, $role] = $user && $user->role === 'client' ? [$user->id, 'client'] : [0, 'guest'];

        $history = $request->session()->get('chatbot.history', []);
        $reply = $ai->chat($data['message'], $userId, $role, $history);

        $history[] = ['role' => 'user', 'content' => $data['message']];
        $history[] = ['role' => 'assistant', 'content' => $reply];
        $request->session()->put('chatbot.history', array_slice($history, -self::HISTORY_LIMIT));

        return response()->json([
            'message' => $reply,
            'html' => ChatFormatter::toHtml($reply),
        ]);
    }
}
