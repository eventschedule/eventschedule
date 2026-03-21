<?php

namespace App\Http\Controllers;

use App\Mail\SupportMessageNotification;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SupportChatController extends Controller
{
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
            'available' => (bool) Cache::get('support_available', false),
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
                'available' => (bool) Cache::get('support_available', false),
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
            'available' => (bool) Cache::get('support_available', false),
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $user = auth()->user();
        $body = strip_tags($request->input('body'));

        Cache::put("support_user_online_{$user->id}", true, now()->addMinutes(2));

        $conversation = $user->supportConversation;
        if (! $conversation) {
            $conversation = SupportConversation::create([
                'user_id' => $user->id,
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

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

        // Email admin
        try {
            $admin = User::where('is_admin', true)->first();
            if ($admin) {
                $replyUrl = url('/admin/support');
                Mail::to($admin->email)->queue(
                    new SupportMessageNotification($body, $user->name ?? $user->email, false, $replyUrl)
                );
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

    // ─── Admin-facing endpoints ───

    public function adminIndex()
    {
        return view('admin.support');
    }

    public function adminConversations()
    {
        $conversations = SupportConversation::with('user')
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('is_from_admin', false)->whereNull('read_at');
            }])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn ($conv) => [
                'id' => UrlUtils::encodeId($conv->id),
                'user_name' => $conv->user->name ?? '',
                'user_email' => $conv->user->email ?? '',
                'status' => $conv->status,
                'last_message_at' => $conv->last_message_at?->toIso8601String(),
                'last_message_preview' => \Illuminate\Support\Str::limit(
                    $conv->messages()->reorder()->latest()->first()?->body ?? '', 80
                ),
                'unread_count' => $conv->unread_count,
            ]);

        return response()->json([
            'conversations' => $conversations,
            'available' => (bool) Cache::get('support_available', false),
        ]);
    }

    public function adminMessages($id)
    {
        $conversationId = UrlUtils::decodeId($id);
        $conversation = SupportConversation::with('user.roles')->findOrFail($conversationId);

        $messages = $conversation->messages()
            ->with('user')
            ->get()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'body' => $msg->body,
                'is_from_admin' => $msg->is_from_admin,
                'sender_name' => $msg->user?->name ?? ($msg->is_from_admin ? 'Admin' : 'User'),
                'created_at' => $msg->created_at->toIso8601String(),
            ]);

        return response()->json([
            'messages' => $messages,
            'user' => [
                'name' => $conversation->user->name ?? '',
                'email' => $conversation->user->email ?? '',
                'roles' => $conversation->user->roles->map(fn ($role) => [
                    'name' => $role->name,
                    'type' => $role->type,
                    'subdomain' => $role->subdomain,
                ]),
            ],
            'status' => $conversation->status,
        ]);
    }

    public function adminReply(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $conversationId = UrlUtils::decodeId($id);
        $conversation = SupportConversation::findOrFail($conversationId);
        $body = strip_tags($request->input('body'));

        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $body,
            'is_from_admin' => true,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Email user if not online
        try {
            $userId = $conversation->user_id;
            if (! Cache::has("support_user_online_{$userId}")) {
                $user = User::find($userId);
                if ($user) {
                    $replyUrl = url('/dashboard');
                    Mail::to($user->email)->queue(
                        new SupportMessageNotification($body, 'Event Schedule Support', true, $replyUrl)
                    );
                }
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
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }

    public function adminMarkRead($id)
    {
        $conversationId = UrlUtils::decodeId($id);
        $conversation = SupportConversation::findOrFail($conversationId);
        $conversation->unreadForAdmin()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function adminToggleAvailability()
    {
        $current = (bool) Cache::get('support_available', false);
        Cache::forever('support_available', ! $current);

        return response()->json(['available' => ! $current]);
    }

    public function adminCloseConversation($id)
    {
        $conversationId = UrlUtils::decodeId($id);
        $conversation = SupportConversation::findOrFail($conversationId);
        $conversation->update(['status' => 'closed']);

        return response()->json(['success' => true]);
    }
}
