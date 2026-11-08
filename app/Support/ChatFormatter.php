<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Turns an AI reply into safe HTML. Raw HTML in the reply is escaped and
 * javascript: style links are dropped, so the output can be shown as-is.
 */
final class ChatFormatter
{
    public static function toHtml(?string $text): string
    {
        return trim((string) Str::markdown((string) $text, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
        ]));
    }
}
