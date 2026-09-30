<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyAdminOfUnreadSupport;
use App\Jobs\SendSupportReplyEmail;
use App\Mail\SupportMessageNotification;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\OneSignalService;
use App\Utils\AdminReauthUtils;
use App\Utils\SupportPresence;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SupportChatController extends Controller
{
    /** How long a typing ping keeps the "is typing" dots on the other side. */
    public const TYPING_SECONDS = 6;

    /** The inbox polls every few seconds, so it shows the newest conversations only. */
    public const INBOX_LIMIT = 100;

    /** Presence keys expire within minutes, so older conversations cannot be online. */
    public const PRESENCE_LOOKBACK_MINUTES = 30;

    // ─── User-facing endpoints ───

    public function status()
    {
        $user = auth()->user();
        $conversation = $user->supportConversation;
        $unreadCount = $conversation
            ? $conversation->unreadForUser()->count()
            : 0;

        Cache::put("support_user_online_{$user->id}", true, now()->addMinutes(2));

        return response()->json([
            'available' => SupportPresence::isAvailable(),
            'unread_count' => $unreadCount,
        ]);
    }

    public function getMessages()
    {
        $user = auth()->user();
        $conversation = $user->supportConversation;

        Cache::put("support_user_online_{$user->id}", true, now()->addMinutes(2));

        if (! $conversation) {
            return response()->json([
                'messages' => [],
                'available' => SupportPresence::isAvailable(),
            ]);
        }

        $messages = $conversation->messages()
            ->reorder()
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'body' => $msg->body,
                'is_from_admin' => $msg->is_from_admin,
                'created_at' => $msg->created_at->toIso8601String(),
            ]);

        return response()->json([
            'messages' => $messages,
            'available' => SupportPresence::isAvailable(),
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $user = auth()->user();
        // Stored as typed: every place a message is shown escapes it, and strip_tags() cut
        // "under <50 guests" down to "under ".
        $body = trim((string) $request->input('body'));

        Cache::put("support_user_online_{$user->id}", true, now()->addMinutes(2));

        $conversation = SupportConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'open', 'last_message_at' => now()]
        );

        if ($conversation->status === 'closed') {
            $conversation->update(['status' => 'open']);
        }

        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => $body,
            'is_from_admin' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Email the admin, unless they are at the AP right now, where the support-presence alert
        // (toast, chime, tab title) has already told them. Then it is emailed only if still
        // unread five minutes later (NotifyAdminOfUnreadSupport).
        try {
            $admin = SupportPresence::agentUser();
            if ($admin) {
                $replyUrl = app_url('/admin/support?c='.UrlUtils::encodeId($conversation->id));
                if (SupportPresence::isAvailable()) {
                    NotifyAdminOfUnreadSupport::queueFor($conversation);
                } else {
                    Mail::to($admin->email)->queue(
                        new SupportMessageNotification($body, $user->name ?? $user->email, false, $replyUrl)
                    );
                }
                OneSignalService::pushToUser($admin, [
                    'title_key' => 'messages.push_support_message_title',
                    'body_key' => 'messages.push_support_message_body',
                    'url' => $replyUrl,
                ], null);
            }
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'is_from_admin' => false,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }

    public function markRead()
    {
        $user = auth()->user();
        $conversation = $user->supportConversation;

        if ($conversation) {
            $conversation->unreadForUser()->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    // ─── Admin presence (every AP page, not only /admin/support) ───
    //
    // Outside the `admin` middleware on purpose. These are called by a timer on every AP page,
    // so behind it they would 423 on /dashboard as soon as the password re-auth window lapsed,
    // and every tick would slide that idle window forward without the admin doing anything.
    // They return nothing but the admin's own presence and the newest unread message preview.

    public function presencePing(Request $request)
    {
        $this->ensureAdmin();

        // Only the admin visitors are told is there counts as being there.
        if (SupportPresence::isOnline() && SupportPresence::isAgent($request->user())) {
            SupportPresence::heartbeat($request->user());
        }

        return response()->json($this->presencePayload($request));
    }

    public function presenceConfirm(Request $request)
    {
        $this->ensureAdmin();

        if (SupportPresence::confirm($request->user())) {
            SupportPresence::heartbeat($request->user());
        }

        return response()->json($this->presencePayload($request));
    }

    public function presenceOnline(Request $request)
    {
        $this->ensureAdmin();

        SupportPresence::goOnline($request->user());

        return response()->json($this->presencePayload($request));
    }

    public function presenceOffline(Request $request)
    {
        $this->ensureAdmin();

        SupportPresence::goOffline();

        return response()->json($this->presencePayload($request));
    }

    /**
     * The same two checks EnsureUserIsAdmin makes before /admin: a fresh password confirmation,
     * and the browser it was made in. A stolen session cookie replayed from elsewhere fails the
     * second.
     */
    private function adminReauthIsCurrent(Request $request): bool
    {
        $session = $request->session();
        $boundAgent = $session->get(AdminReauthUtils::USER_AGENT_KEY);

        return AdminReauthUtils::isCurrent($session)
            && ($boundAgent === null || $boundAgent === (string) $request->userAgent());
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    private function presencePayload(Request $request): array
    {
        $unread = SupportMessage::query()
            ->where('is_from_admin', false)
            ->whereNull('read_at')
            ->orderByDesc('id');

        $latest = (clone $unread)->with('conversation.user')->first();

        $latestPayload = null;
        if ($latest && $latest->conversation) {
            $conversation = $latest->conversation;
            $presence = Cache::get($conversation->presenceKey());

            $latestPayload = [
                'id' => $latest->id,
                'conversation_id' => UrlUtils::encodeId($conversation->id),
                'is_guest' => $conversation->isGuest(),
                'created_at' => $latest->created_at->toIso8601String(),
            ];

            // These endpoints skip the admin password re-check (see above), so who wrote and
            // what they wrote - a visitor's email, the start of their message - is only handed
            // out while that check is still fresh. Otherwise the toast just says a message came in.
            if ($this->adminReauthIsCurrent($request)) {
                $latestPayload += [
                    'sender' => $conversation->displayName(),
                    'page' => is_array($presence) ? ($presence['page'] ?? null) : null,
                    'preview' => Str::limit($latest->body, 90),
                ];
            }
        }

        return [
            'presence' => SupportPresence::state($request->user()),
            'unread_count' => $unread->count(),
            'latest_unread' => $latestPayload,
        ];
    }

    // ─── Admin-facing endpoints ───

    public function adminIndex()
    {
        return view('admin.support');
    }

    public function adminConversations()
    {
        $conversations = SupportConversation::with(['user', 'latestMessage'])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('is_from_admin', false)->whereNull('read_at');
            }])
            ->orderByDesc('last_message_at')
            ->limit(self::INBOX_LIMIT)
            ->get()
            ->map(function ($conv) {
                // One cache read per recent row, not per row: this runs every few seconds.
                $recent = $conv->last_message_at?->gt(now()->subMinutes(self::PRESENCE_LOOKBACK_MINUTES));
                $presence = $recent ? Cache::get($conv->presenceKey()) : null;

                return [
                    'id' => UrlUtils::encodeId($conv->id),
                    'user_name' => $conv->isGuest() ? ($conv->guest_name ?? '') : ($conv->user->name ?? ''),
                    'user_email' => $conv->contactEmail() ?? '',
                    'display_name' => $conv->displayName(),
                    'is_guest' => $conv->isGuest(),
                    'guest_country' => $conv->guest_country,
                    'online' => (bool) $presence,
                    'status' => $conv->status,
                    'last_message_at' => $conv->last_message_at?->toIso8601String(),
                    'last_message_preview' => Str::limit(
                        $conv->latestMessage?->body ?? '', 80
                    ),
                    'unread_count' => $conv->unread_count,
                ];
            });

        return response()->json([
            'conversations' => $conversations,
            'available' => SupportPresence::isOnline(),
            'presence' => SupportPresence::state(auth()->user()),
        ]);
    }

    public function adminMessages($id)
    {
        $conversationId = UrlUtils::decodeIdOrFail($id);
        $conversation = SupportConversation::with('user.roles')->findOrFail($conversationId);

        $messages = $conversation->messages()
            ->with('user')
            ->get()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'body' => $msg->body,
                'is_from_admin' => $msg->is_from_admin,
                'sender_name' => $msg->is_from_admin
                    ? ($msg->user?->name ?? 'Admin')
                    : ($msg->user?->name ?? $conversation->displayName()),
                'read' => $msg->read_at !== null,
                'created_at' => $msg->created_at->toIso8601String(),
            ]);

        $presence = Cache::get($conversation->presenceKey());

        if ($conversation->isGuest()) {
            $user = [
                'name' => $conversation->guest_name ?? '',
                'email' => $conversation->guest_email ?? '',
                'is_guest' => true,
                'country' => $conversation->guest_country,
                'started_on' => $conversation->guest_page,
                'current_page' => is_array($presence) ? ($presence['page'] ?? null) : null,
                'has_account' => $conversation->guest_email
                    ? User::where('email', $conversation->guest_email)->exists()
                    : false,
                'roles' => [],
            ];
        } else {
            $user = [
                'name' => $conversation->user->name ?? '',
                'email' => $conversation->user->email ?? '',
                'is_guest' => false,
                'roles' => $conversation->user?->roles->map(fn ($role) => [
                    'name' => $role->name,
                    'type' => $role->type,
                    'subdomain' => $role->subdomain,
                ]) ?? [],
            ];
        }

        $user['online'] = (bool) $presence;

        return response()->json([
            'messages' => $messages,
            'user' => $user,
            'status' => $conversation->status,
        ]);
    }

    public function adminReply(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $conversationId = UrlUtils::decodeIdOrFail($id);
        $conversation = SupportConversation::with('user')->findOrFail($conversationId);
        $body = trim((string) $request->input('body'));

        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $body,
            'is_from_admin' => true,
        ]);

        $conversation->update(['last_message_at' => now()]);
        Cache::forget($conversation->typingKey());

        // Queued whether or not they are in the chat right now: SendSupportReplyEmail decides
        // when it runs, from what is still unread and whether they are still there. Deciding
        // here instead would never email a visitor who had the page open with the chat closed
        // and then left without reading the reply. Unique per conversation, so a run of
        // replies arrives as one email.
        try {
            // Answering someone is the clearest proof the admin is at the keyboard. Inside the
            // try: the reply is already saved, and an error here would make the admin send it
            // again.
            if (SupportPresence::confirm($request->user())) {
                SupportPresence::heartbeat($request->user());
            }

            if ($conversation->contactEmail()) {
                SendSupportReplyEmail::dispatch($conversation->id)
                    ->delay(now()->addMinutes(SendSupportReplyEmail::DELAY_MINUTES));
            }

            // A push is only worth anything while it is immediate.
            if ($conversation->user && ! Cache::has($conversation->presenceKey())) {
                OneSignalService::pushToUser($conversation->user, [
                    'title_key' => 'messages.push_support_message_title',
                    'body_key' => 'messages.push_support_message_body',
                    'url' => app_url('/dashboard'),
                ], null);
            }
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'is_from_admin' => true,
                'sender_name' => auth()->user()->name ?? 'Admin',
                'read' => false,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }

    public function adminTyping($id)
    {
        $conversationId = UrlUtils::decodeIdOrFail($id);

        Cache::put(SupportConversation::typingKeyFor($conversationId), true, now()->addSeconds(self::TYPING_SECONDS));

        return response()->json(['success' => true]);
    }

    public function adminMarkRead($id)
    {
        $conversationId = UrlUtils::decodeIdOrFail($id);
        $conversation = SupportConversation::findOrFail($conversationId);
        $conversation->unreadForAdmin()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Sets the state the checkbox shows rather than flipping whatever the server holds: the
     * server side can change underneath an open page (another tab, the hourly lapse), and a
     * flip would then turn chat off for an admin who just switched it on.
     */
    public function adminToggleAvailability(Request $request)
    {
        $request->validate(['available' => 'required|boolean']);

        if ($request->boolean('available')) {
            SupportPresence::goOnline(auth()->user());
        } else {
            SupportPresence::goOffline();
        }

        return response()->json([
            'available' => SupportPresence::isOnline(),
            'presence' => SupportPresence::state($request->user()),
        ]);
    }

    public function adminCloseConversation($id)
    {
        $conversationId = UrlUtils::decodeIdOrFail($id);
        $conversation = SupportConversation::findOrFail($conversationId);
        $conversation->update(['status' => 'closed']);

        return response()->json(['success' => true]);
    }
}
