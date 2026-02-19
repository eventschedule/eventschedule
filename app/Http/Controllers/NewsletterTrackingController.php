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
            $isFirstOpen = $recipient->recordOpen();

            if ($isFirstOpen && $recipient->status !== 'test') {
                $newsletter = $recipient->newsletter;
                if ($newsletter) {
                    $newsletter->increment('open_count');
                }
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
        $url = base64_decode(strtr($encodedUrl, '-_', '+/'));

        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            \Log::warning('Newsletter click: invalid URL', ['encodedUrl' => $encodedUrl, 'decoded' => $url]);
            abort(404);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array($scheme, ['http', 'https'])) {
            \Log::warning('Newsletter click: invalid scheme', ['url' => $url, 'scheme' => $scheme]);
            abort(404);
        }

        $recipient = NewsletterRecipient::where('token', $token)->first();

        if (! $recipient) {
            abort(404);
        }

        $isFirstClick = $recipient->recordClick(
            $url,
            request()->ip(),
            request()->userAgent()
        );

        if ($isFirstClick && $recipient->status !== 'test') {
            $newsletter = $recipient->newsletter;
            if ($newsletter) {
                $newsletter->increment('click_count');
            }
        }

        return redirect($url, 302);
    }

    public function showUnsubscribe(string $token)
    {
        $recipient = NewsletterRecipient::where('token', $token)->with('newsletter.role')->first();

        if (! $recipient || ! $recipient->newsletter) {
            abort(404);
        }

        $newsletter = $recipient->newsletter;
        $isAdminNewsletter = $newsletter->isAdmin();

        if (! $isAdminNewsletter && ! $newsletter->role) {
            abort(404);
        }

        $role = $newsletter->role;

        if ($role && is_valid_language_code($role->language_code)) {
            app()->setLocale($role->language_code);
        }

        return view('newsletter.unsubscribe', [
            'recipient' => $recipient,
            'role' => $role,
            'isAdminNewsletter' => $isAdminNewsletter,
        ]);
    }

    public function unsubscribe(Request $request, string $token)
    {
        $recipient = NewsletterRecipient::where('token', $token)->with('newsletter.role')->first();

        if (! $recipient || ! $recipient->newsletter) {
            abort(404);
        }

        $newsletter = $recipient->newsletter;
        $isAdminNewsletter = $newsletter->isAdmin();

        if (! $isAdminNewsletter && ! $newsletter->role) {
            abort(404);
        }

        $role = $newsletter->role;

        if ($role && is_valid_language_code($role->language_code)) {
            app()->setLocale($role->language_code);
        }

        $unsubscribed = false;

        if ($isAdminNewsletter) {
            $user = $recipient->user_id
                ? \App\Models\User::find($recipient->user_id)
                : \App\Models\User::where('email', strtolower($recipient->email))->first();
            if ($user) {
                $user->update(['admin_newsletter_unsubscribed_at' => now()]);
                $unsubscribed = true;
            }
        } else {
            NewsletterUnsubscribe::firstOrCreate(
                ['role_id' => $role->id, 'email' => strtolower($recipient->email)],
                ['unsubscribed_at' => now()]
            );
            $unsubscribed = true;
        }

        return view('newsletter.unsubscribe', [
            'recipient' => $recipient,
            'role' => $role,
            'isAdminNewsletter' => $isAdminNewsletter,
            'unsubscribed' => $unsubscribed,
        ]);
    }
}
