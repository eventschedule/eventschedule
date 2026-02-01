<?php

namespace App\Http\Controllers;

use App\Models\NewsletterRecipient;
use App\Models\NewsletterUnsubscribe;
use Illuminate\Http\Request;

class NewsletterTrackingController extends Controller
{
    public function trackOpen(string $token)
    {
        $recipient = NewsletterRecipient::where('token', $token)->first();

        if ($recipient) {
            $recipient->recordOpen();

            // Update denormalized count on newsletter
            $newsletter = $recipient->newsletter;
            if ($newsletter) {
                $newsletter->update([
                    'open_count' => $newsletter->recipients()->where('open_count', '>', 0)->count(),
                ]);
            }
        }

        // Return 1x1 transparent PNG
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        return response($pixel, 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($pixel),
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function trackClick(string $token, string $encodedUrl)
    {
        $url = base64_decode($encodedUrl);

        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        $recipient = NewsletterRecipient::where('token', $token)->first();

        if (! $recipient) {
            abort(404);
        }

        $recipient->recordClick(
            $url,
            request()->ip(),
            request()->userAgent()
        );

        // Update denormalized count on newsletter
        $newsletter = $recipient->newsletter;
        if ($newsletter) {
            $newsletter->update([
                'click_count' => $newsletter->recipients()->where('click_count', '>', 0)->count(),
            ]);
        }

        return redirect($url, 302);
    }

    public function showUnsubscribe(string $token)
    {
        $recipient = NewsletterRecipient::where('token', $token)->with('newsletter.role')->first();

        if (! $recipient || ! $recipient->newsletter || ! $recipient->newsletter->role) {
            abort(404);
        }

        return view('newsletter.unsubscribe', [
            'recipient' => $recipient,
            'role' => $recipient->newsletter->role,
        ]);
    }

    public function unsubscribe(Request $request, string $token)
    {
        $recipient = NewsletterRecipient::where('token', $token)->with('newsletter.role')->first();

        if (! $recipient || ! $recipient->newsletter || ! $recipient->newsletter->role) {
            abort(404);
        }

        $role = $recipient->newsletter->role;

        NewsletterUnsubscribe::firstOrCreate(
            ['role_id' => $role->id, 'email' => strtolower($recipient->email)],
            ['unsubscribed_at' => now()]
        );

        return view('newsletter.unsubscribe', [
            'recipient' => $recipient,
            'role' => $role,
            'unsubscribed' => true,
        ]);
    }
}
