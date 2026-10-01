<?php

namespace App\Http\Controllers;

use App\Jobs\SendSupportReplyEmail;
use App\Models\PageView;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\OneSignalService;
use App\Utils\HoneypotUtils;
use App\Utils\SupportPresence;
use App\Utils\UrlUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The support chat for signed-out visitors on the marketing site (resources/js/components/
 * SupportChatWidget.vue). The account-holder chat lives in SupportChatController.
 *
 * A visitor has no session - these routes are CacheableMarketingResponse::STATELESS_ROUTES, so
 * they never hand out the laravel_session cookie that takes a visitor off the edge cache. They
 * are identified instead by a random token the widget keeps in localStorage and sends in the
 * TOKEN_HEADER. That is also why CSRF is off on the POSTs: there is no ambient credential for a
 * forged request to ride on, and a custom header cannot be sent cross-site without a preflight.
 *
 * Every response is `private, no-store`. The route names are not `marketing.*`, so the edge-cache
 * middleware can never mark one public - a shared cache keys on the URL alone and would hand one
 * visitor's transcript to the next.
 */
class SupportChatGuestController extends Controller
{
    public const TOKEN_HEADER = 'X-Support-Chat-Token';

    /** How long one visible poll counts as "looking at the chat". */
    public const PRESENCE_MINUTES = 2;

    /**
     * New conversations one IP may open per hour. Replies inside an existing conversation are
     * only held to the route throttle: this is about a script opening a fresh thread (and so a
     * fresh email debounce and a push) per message.
     */
    public const NEW_CONVERSATIONS_PER_HOUR = 5;

    /** At most one admin push per conversation per this many minutes. */
    public const PUSH_DEBOUNCE_MINUTES = 2;

    /**
     * What layouts/marketing.blade.php hands the widget. Deliberately nothing about the visitor
     * or the admin's presence: the page it is rendered into is shared by everyone at the edge.
     *
     * A method rather than an array in the layout: that file already uses inline @php(...),
     * which a later @php block would swallow, and @json() splits its argument on every comma.
     */
    public static function widgetConfig(): array
    {
        return [
            'statusUrl' => route('support-chat.guest.status', [], false),
            'messagesUrl' => route('support-chat.guest.messages', [], false),
            'contactUrl' => route('support-chat.guest.contact', [], false),
            'readUrl' => route('support-chat.guest.read', [], false),
            'honeypotField' => HoneypotUtils::FIELD,
            'privacyUrl' => policy_url('privacy'),
            // Docs readers are mid-task: the launcher is there, the unprompted greeting is not.
            'greet' => ! str_starts_with((string) Route::currentRouteName(), 'marketing.docs'),
        ];
    }

    public function status()
    {
        abort_unless(config('app.is_nexus'), 404);

        return $this->json([
            'available' => SupportPresence::isAvailable(),
            'agent' => SupportPresence::agent(),
        ]);
    }

    public function messages(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $conversation = $this->conversationFromToken($request);

        if (! $conversation) {
            return $this->json(['error' => 'not_found'], 404);
        }

        // Only a poll from a tab the visitor can see counts as them watching the chat. A
        // background tab keeps polling (so its title can announce a reply), but must not stop
        // SendSupportReplyEmail from emailing someone who has walked away.
        if ($request->boolean('visible')) {
            Cache::put($conversation->presenceKey(), [
                'page' => $this->normalizePage($request->input('page')),
            ], now()->addMinutes(self::PRESENCE_MINUTES));
        }

        return $this->json($this->conversationPayload($conversation));
    }

    public function send(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $this->requireJson($request);
        $this->rejectHoneypot($request);

        $request->validate([
            'body' => 'required|string|max:2000',
            'email' => 'nullable|email|max:255',
            'name' => 'nullable|string|max:100',
            'page' => 'nullable|string|max:2000',
        ]);

        $conversation = $this->conversationFromToken($request);
        $email = $request->input('email') ?: $conversation?->guest_email;

        // Nobody is there to answer live, so the reply has to go somewhere.
        if (! SupportPresence::isAvailable() && ! $email) {
            throw ValidationException::withMessages([
                'email' => 'Leave your email so we can get back to you.',
            ]);
        }

        // Stored as typed: every place a message is shown escapes it, and strip_tags() turned
        // "We run <50 events a month" into "We run ".
        $body = trim((string) $request->input('body'));

        if ($body === '') {
            throw ValidationException::withMessages(['body' => 'Type a message first.']);
        }

        $isNew = ! $conversation;

        if ($isNew) {
            // The Cloudflare-aware IP: behind the proxy a bare ip() can resolve to an edge
            // address, and every visitor arriving through it would share one cap.
            $limiterKey = 'support-guest-new:'.PageView::clientIp($request);

            if (RateLimiter::tooManyAttempts($limiterKey, self::NEW_CONVERSATIONS_PER_HOUR)) {
                return $this->json([
                    'error' => 'too_many_conversations',
                    'message' => 'Too many new chats from here. Please try again later.',
                ], 429);
            }

            RateLimiter::hit($limiterKey, 3600);

            $conversation = SupportConversation::create([
                'user_id' => null,
                'guest_token' => Str::random(48),
                'guest_email' => $email,
                'guest_name' => $request->input('name') ?: null,
                'guest_page' => $this->normalizePage($request->input('page')),
                'guest_country' => $this->country($request),
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        } else {
            $conversation->fill(array_filter([
                'guest_email' => $request->input('email') ?: null,
                'guest_name' => $request->input('name') ?: null,
            ]));
            if ($conversation->status === 'closed') {
                $conversation->status = 'open';
            }
            $conversation->last_message_at = now();
            $conversation->save();
        }

        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => null,
            'body' => $body,
            'is_from_admin' => false,
        ]);

        Cache::put($conversation->presenceKey(), [
            'page' => $this->normalizePage($request->input('page')),
        ], now()->addMinutes(self::PRESENCE_MINUTES));

        $this->notifyAdmin($conversation, $body);

        return $this->json([
            'token' => $isNew ? $conversation->guest_token : null,
            'message' => $this->messagePayload($message),
            'has_email' => (bool) $conversation->guest_email,
        ]);
    }

    public function contact(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $this->requireJson($request);
        $this->rejectHoneypot($request);

        $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:100',
        ]);

        $conversation = $this->conversationFromToken($request);

        if (! $conversation) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $conversation->guest_email = $request->input('email');
        if ($request->filled('name')) {
            $conversation->guest_name = $request->input('name');
        }
        $conversation->save();

        // Replies were already waiting when they left an address: they are queued nowhere else,
        // because adminReply only queues the email when there is somewhere to send it.
        if ($conversation->unreadForUser()->exists()) {
            SendSupportReplyEmail::dispatch($conversation->id)
                ->delay(now()->addMinutes(SendSupportReplyEmail::DELAY_MINUTES));
        }

        return $this->json(['has_email' => true]);
    }

    public function read(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $conversation = $this->conversationFromToken($request);

        if (! $conversation) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $conversation->unreadForUser()->update(['read_at' => now()]);

        return $this->json(['success' => true]);
    }

    private function conversationFromToken(Request $request): ?SupportConversation
    {
        $token = $request->header(self::TOKEN_HEADER);

        if (! is_string($token) || strlen($token) < 32 || strlen($token) > 64) {
            return null;
        }

        return SupportConversation::whereNull('user_id')->where('guest_token', $token)->first();
    }

    private function conversationPayload(SupportConversation $conversation): array
    {
        $messages = $conversation->messages()
            ->reorder()
            ->latest('id')
            ->take(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($msg) => $this->messagePayload($msg));

        return [
            'messages' => $messages,
            'available' => SupportPresence::isAvailable(),
            'agent' => SupportPresence::agent(),
            'agent_typing' => Cache::has($conversation->typingKey()),
            'unread_count' => $conversation->unreadForUser()->count(),
            'has_email' => (bool) $conversation->guest_email,
            'status' => $conversation->status,
        ];
    }

    private function messagePayload(SupportMessage $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'is_from_admin' => (bool) $message->is_from_admin,
            // Visitor messages only: "Seen" once the admin has opened the thread.
            'read' => ! $message->is_from_admin && $message->read_at !== null,
            'created_at' => $message->created_at->toIso8601String(),
        ];
    }

    private function notifyAdmin(SupportConversation $conversation, string $body): void
    {
        try {
            // Online or not: while available the AP alert (toast, chime, tab title) fires too.
            $conversation->emailPrimaryAdmin($body);

            $admin = SupportPresence::agentUser();

            // One push per burst of messages, available or not: a visitor typing line by line
            // would otherwise buzz the admin's phone for every line.
            if (! $admin || ! Cache::add("support_guest_push_{$conversation->id}", true, now()->addMinutes(self::PUSH_DEBOUNCE_MINUTES))) {
                return;
            }

            OneSignalService::pushToUser($admin, [
                'title_key' => 'messages.push_support_message_title',
                'body_key' => 'messages.push_support_message_body',
                'url' => app_url('/admin/support?c='.UrlUtils::encodeId($conversation->id)),
            ], null);
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * The widget always posts JSON. A form-encoded POST can only be a cross-site form or a
     * script: a page on another site could otherwise auto-submit a hidden form here from each of
     * its own visitors' browsers, sidestepping the per-IP limits, since this route has no CSRF
     * token to stop it. A cross-site request with a JSON body needs a CORS preflight, which
     * config/cors.php (api/* only) never grants.
     */
    private function requireJson(Request $request): void
    {
        if (! $request->isJson()) {
            abort(415);
        }
    }

    /**
     * Honeypot bail shaped for the widget: a JSON 422 it can show, rather than the redirect a
     * middleware would answer a fetch() with.
     */
    private function rejectHoneypot(Request $request): void
    {
        if (HoneypotUtils::isTripped($request)) {
            throw ValidationException::withMessages([
                'body' => __('messages.invalid_request'),
            ]);
        }
    }

    /**
     * The page the visitor is on, reduced to a path. It is shown to the admin as a link, and
     * a path can carry neither a foreign host nor a javascript: scheme.
     */
    private function normalizePage($page): ?string
    {
        if (! is_string($page) || $page === '') {
            return null;
        }

        $path = parse_url($page, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        return Str::limit($path, 250, '');
    }

    /**
     * Cloudflare's two-letter guess at the visitor's country. XX is "unknown" and T1 is Tor.
     */
    private function country(Request $request): ?string
    {
        $country = strtoupper((string) $request->header('CF-IPCountry'));

        return preg_match('/^[A-Z]{2}$/', $country) && ! in_array($country, ['XX', 'T1'], true)
            ? $country
            : null;
    }

    private function json(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status)->header('Cache-Control', 'private, no-store');
    }
}
