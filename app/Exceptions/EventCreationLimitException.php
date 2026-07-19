<?php

namespace App\Exceptions;

use Illuminate\Http\Request;

/**
 * Thrown when a schedule or user has hit its daily event-creation cap (anti-abuse).
 * Renders a 422 for JSON clients (AI import loop, API) and a redirect-back with a
 * flash error for native form submissions (manual event form, guest booking form).
 */
class EventCreationLimitException extends \Exception
{
    public function render(Request $request)
    {
        $message = $this->getMessage() ?: __('messages.event_create_daily_limit_reached');

        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 422);
        }

        return back()->withInput()->with('error', $message)->withErrors(['event' => $message]);
    }
}
