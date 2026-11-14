<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves files that must never have a public link. A 404 keeps other teams from
 * learning that a record exists.
 */
class PrivateFileController extends Controller
{
    public function chatPhoto(ChatMessage $message): Response
    {
        abort_unless(in_array((int) auth()->id(), [(int) $message->sender_id, (int) $message->receiver_id], true), 404);

        return $this->serve($message->attachment);
    }

    private function serve(?string $path): Response
    {
        abort_if(blank($path) || str_contains($path, '..') || str_starts_with($path, 'http'), 404);

        // Files uploaded before they became private stay on the public disk until
        // files:make-private has moved them.
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path, null, [
                    'Cache-Control' => 'private, no-store',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        abort(404);
    }
}
