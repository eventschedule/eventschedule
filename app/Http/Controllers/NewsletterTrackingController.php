<?php

namespace App\Http\Controllers;

use App\Models\NewsletterRecipient;
use App\Models\NewsletterUnsubscribe;
use App\Utils\UrlUtils;
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

    /** The link as mail has carried it since 2026-10: signed for its own address. */
    public function trackSignedClick(string $token, string $signature, string $encodedUrl)
    {
        return $this->click($token, $encodedUrl, $signature);
    }

    /** The link as older mail carries it, with nothing to say the address is the mail's own. */
    public function trackClick(string $token, string $encodedUrl)
    {
        return $this->click($token, $encodedUrl, null);
    }

    /**
     * An unsigned link is followed only to an address the mail could have held: one of this
     * install's own pages, or an address written in the newsletter. Anything else goes to the
     * schedule that sent the mail, so a link in an old mail never ends on an error page and never
     * on a page somebody else chose.
     */
    private function mailCouldHold(string $url, $newsletter): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $ours = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if ($host !== '' && $ours !== '' && ($host === $ours || str_ends_with($host, '.'.$ours))) {
            return true;
        }

        if ($host !== '' && \App\Models\Role::where('custom_domain_host', $host)->where('custom_domain_mode', 'direct')->where('custom_domain_status', 'active')->exists()) {
            return true;
        }

        if (! $newsletter) {
            return false;
        }

        // With or without its scheme: the views add "https://" to an address typed without one.
        $written = (string) json_encode($newsletter->blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $address = preg_replace('#^https?://#i', '', $url);

        // Written there as an address of its own. A plain "contains" also followed a link to any
        // host that is the TAIL of one the newsletter named.
        return $address !== '' && preg_match('#(?<![A-Za-z0-9.\-])'.preg_quote($address, '#').'#', $written) === 1;
    }

    private function click(string $token, string $encodedUrl, ?string $signature)
    {
        $url = base64_decode(strtr($encodedUrl, '-_', '+/'));

        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            \Log::warning('Newsletter click: invalid URL', ['encodedUrl' => $encodedUrl, 'decoded' => $url]);
            abort(404);
        }

        // In any case: a phone's keyboard writes "Https://", and a browser follows it.
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            \Log::warning('Newsletter click: invalid scheme', ['url' => $url, 'scheme' => $scheme]);
            abort(404);
        }

        $recipient = NewsletterRecipient::where('token', $token)->first();

        if (! $recipient) {
            abort(404);
        }

        $newsletter = $recipient->newsletter;

        $signed = $signature !== null && hash_equals(\App\Services\NewsletterService::clickSignature($token, $url), $signature);

        if (! $signed && ! $this->mailCouldHold($url, $newsletter)) {
            return redirect($newsletter?->role?->getGuestUrl() ?: url('/'), 302);
        }

        $isFirstClick = $recipient->recordClick($url);

        if ($isFirstClick && $recipient->status !== 'test' && $newsletter) {
            $newsletter->increment('click_count');
        }

        // Append UTM params for analytics attribution
        if ($newsletter && ! str_contains($url, 'utm_source=')) {
            $utmParams = http_build_query([
                'utm_source' => 'newsletter',
                'utm_medium' => 'email',
                'utm_campaign' => UrlUtils::encodeId($newsletter->id),
            ]);
            $fragment = parse_url($url, PHP_URL_FRAGMENT);
            if ($fragment !== null) {
                $url = preg_replace('/#.*$/', '', $url);
            }
            $url .= (str_contains($url, '?') ? '&' : '?').$utmParams;
            if ($fragment !== null) {
                $url .= '#'.$fragment;
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
