<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterSegmentUser;
use App\Models\NewsletterTemplate;
use App\Models\User;
use App\Services\NewsletterService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminNewsletterController extends Controller
{
    use Traits\SanitizesNewsletterContent;

    public function index()
    {
        $newsletters = Newsletter::admin()
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.newsletters.index', compact('newsletters'));
    }

    public function create(Request $request)
    {
        $segments = NewsletterSegment::whereNull('role_id')->get();
        foreach ($segments as $segment) {
            $segment->recipient_count = $segment->recipientCount();
        }

        $defaultBlocks = [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'heading',
                'data' => ['text' => '', 'level' => 'h1', 'align' => 'center'],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'data' => ['content' => ''],
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'data' => ['content' => ''],
            ],
        ];

        $lastNewsletter = Newsletter::admin()
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->first();

        $defaultTemplate = $lastNewsletter ? $lastNewsletter->template : 'modern';
        $defaultStyleSettings = $lastNewsletter ? $lastNewsletter->style_settings : Newsletter::defaultStyleSettings();
        $defaultSegmentIds = $lastNewsletter ? ($lastNewsletter->segment_ids ?? []) : [];

        // Load saved templates for the template picker
        $savedTemplates = NewsletterTemplate::whereNull('role_id')
            ->where('is_system', false)
            ->orderBy('name')
            ->get();

        // If a template_id is provided, use that template's design
        if ($request->has('template_id')) {
            $decoded = UrlUtils::decodeId($request->template_id);
            if ($decoded) {
                $fromTemplate = NewsletterTemplate::whereNull('role_id')
                    ->where('id', $decoded)
                    ->first();
                if ($fromTemplate) {
                    $defaultBlocks = ! empty($fromTemplate->blocks) ? $fromTemplate->blocks : $defaultBlocks;
                    $defaultTemplate = $fromTemplate->template ?? 'modern';
                    $defaultStyleSettings = $fromTemplate->style_settings ?? $defaultStyleSettings;
                }
            }
        }

        return view('admin.newsletters.create', compact('segments', 'defaultBlocks', 'defaultTemplate', 'defaultStyleSettings', 'defaultSegmentIds', 'savedTemplates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|in:modern,classic,minimal,bold,compact',
            'style_settings' => 'nullable|array',
            'segment_ids' => 'nullable|array',
            'segment_ids.*' => 'integer',
        ]);

        $blocks = $this->parseBlocks($request, ['offer']);

        $newsletter = Newsletter::create([
            'role_id' => null,
            'user_id' => auth()->id(),
            'type' => 'admin',
            'subject' => $validated['subject'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
            'segment_ids' => ! empty($validated['segment_ids']) ? array_map('intval', $validated['segment_ids']) : null,
            'status' => 'draft',
        ]);

        return redirect()->route('admin.newsletters.edit', [
            'hash' => UrlUtils::encodeId($newsletter->id),
        ])->with('status', __('messages.newsletter_saved'));
    }

    public function edit(string $hash)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $segments = NewsletterSegment::whereNull('role_id')->get();
        foreach ($segments as $segment) {
            $segment->recipient_count = $segment->recipientCount();
        }

        return view('admin.newsletters.edit', compact('newsletter', 'segments'));
    }

    public function update(Request $request, string $hash)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (in_array($newsletter->status, ['sending', 'sent'])) {
            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|in:modern,classic,minimal,bold,compact',
            'style_settings' => 'nullable|array',
            'segment_ids' => 'nullable|array',
            'segment_ids.*' => 'integer',
        ]);

        $blocks = $this->parseBlocks($request, ['offer']);

        $newsletter->update([
            'subject' => $validated['subject'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
            'segment_ids' => ! empty($validated['segment_ids']) ? array_map('intval', $validated['segment_ids']) : null,
        ]);

        if ($request->header('X-Save-Before-Action')) {
            return response()->json(['saved' => true]);
        }

        return back()->with('status', __('messages.newsletter_saved'));
    }

    public function delete(string $hash)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletter->status === 'sending') {
            return back()->with('error', __('messages.newsletter_is_sending'));
        }

        $newsletter->update(['status' => 'cancelled']);

        return redirect()->route('admin.newsletters.index')
            ->with('status', __('messages.newsletter_deleted'));
    }

    public function send(string $hash, NewsletterService $service)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (! in_array($newsletter->status, ['draft', 'scheduled'])) {
            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        // On sync queue, large sends would timeout the HTTP request - advise scheduling instead
        if (config('queue.default') === 'sync') {
            $estimatedCount = $service->resolveAdminRecipients($newsletter->segment_ids ?? [])->count();

            if ($estimatedCount > 50) {
                return back()->with('error', __('messages.newsletter_sync_queue_limit'));
            }
        }

        $result = $service->send($newsletter);

        if ($result === false) {
            return back()->with('error', __('messages.newsletter_send_failed'));
        }

        if (is_array($result) && $result[0] === 'no_recipients') {
            return back()->with('error', __('messages.newsletter_no_recipients'));
        }

        return redirect()->route('admin.newsletters.index')
            ->with('status', __('messages.newsletter_sending'));
    }

    public function schedule(Request $request, string $hash)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (! in_array($newsletter->status, ['draft', 'scheduled'])) {
            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        $timezone = auth()->user()->timezone ?? 'UTC';
        $scheduledAtUtc = Carbon::parse($request->scheduled_at, $timezone)->setTimezone('UTC');

        if ($scheduledAtUtc->lte(now())) {
            return back()->withErrors(['scheduled_at' => __('validation.after', ['attribute' => 'scheduled at', 'date' => 'now'])]);
        }

        $newsletter->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAtUtc,
        ]);

        return back()->with('status', __('messages.newsletter_scheduled'));
    }

    public function cancel(string $hash)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletter->status !== 'scheduled') {
            return back()->with('error', __('messages.newsletter_not_scheduled'));
        }

        $newsletter->update([
            'status' => 'draft',
            'scheduled_at' => null,
        ]);

        return back()->with('status', __('messages.newsletter_cancelled'));
    }

    public function cloneNewsletter(string $hash)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $copy = $newsletter->replicate();
        $copy->user_id = auth()->id();
        $copy->status = 'draft';
        $copy->scheduled_at = null;
        $copy->sent_at = null;
        $copy->sent_count = 0;
        $copy->open_count = 0;
        $copy->click_count = 0;
        $copy->ab_test_id = null;
        $copy->ab_variant = null;
        $copy->send_token = null;
        $copy->save();

        return redirect()->route('admin.newsletters.edit', [
            'hash' => UrlUtils::encodeId($copy->id),
        ])->with('status', __('messages.newsletter_cloned'));
    }

    public function previewDraft(Request $request, NewsletterService $service)
    {
        $blocks = $this->parseBlocks($request, ['offer']);

        $newsletter = new Newsletter([
            'role_id' => null,
            'type' => 'admin',
            'subject' => $request->input('subject', ''),
            'blocks' => $blocks,
            'template' => $request->input('template', 'modern'),
            'style_settings' => $this->sanitizeStyleSettings($request->input('style_settings')),
        ]);

        $html = $service->renderPreview($newsletter);

        return response()->json(['html' => $html]);
    }

    public function preview(Request $request, string $hash, NewsletterService $service)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($request->has('subject')) {
            $newsletter->subject = $request->input('subject');
            $newsletter->blocks = $this->parseBlocks($request, ['offer']);
            $newsletter->template = $request->input('template', 'modern');
            $newsletter->style_settings = $this->sanitizeStyleSettings($request->input('style_settings'));
        }

        $html = $service->renderPreview($newsletter);

        return response()->json(['html' => $html]);
    }

    public function testSend(string $hash, NewsletterService $service)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $adminEmail = auth()->user()->email;
        if (empty($adminEmail)) {
            return back()->with('error', __('messages.email_required'));
        }

        $rateLimitKey = 'test-admin-newsletter-'.auth()->id();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return back()->with('error', __('messages.please_wait').'...');
        }

        $recipient = new NewsletterRecipient([
            'newsletter_id' => $newsletter->id,
            'user_id' => auth()->id(),
            'email' => $adminEmail,
            'name' => auth()->user()->name,
            'token' => Str::random(64),
            'status' => 'pending',
        ]);
        $recipient->save();

        $result = $service->sendToRecipient($newsletter, $recipient, isTest: true);

        if (! $result) {
            $recipient->delete();
            RateLimiter::hit($rateLimitKey, 60);

            return back()->with('error', __('messages.test_email_failed'));
        }

        $recipient->update(['status' => 'test']);
        RateLimiter::hit($rateLimitKey, 60);

        return back()->with('status', __('messages.test_email_sent_to', ['email' => $adminEmail]));
    }

    public function stats(string $hash)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $recipients = $newsletter->recipients()
            ->where('status', '!=', 'test')
            ->orderBy('opened_at', 'desc')
            ->paginate(50);

        $topLinks = \App\Models\NewsletterClick::whereHas('recipient', function ($q) use ($newsletter) {
            $q->where('newsletter_id', $newsletter->id)->where('status', '!=', 'test');
        })
            ->selectRaw('url, COUNT(*) as click_count')
            ->groupBy('url')
            ->orderByDesc('click_count')
            ->limit(10)
            ->get();

        $openTimeline = $newsletter->recipients()
            ->where('status', '!=', 'test')
            ->whereNotNull('opened_at')
            ->selectRaw('DATE(opened_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $clickTimeline = \App\Models\NewsletterClick::whereHas('recipient', function ($q) use ($newsletter) {
            $q->where('newsletter_id', $newsletter->id)->where('status', '!=', 'test');
        })
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.newsletters.stats', compact(
            'newsletter',
            'recipients',
            'topLinks',
            'openTimeline',
            'clickTimeline'
        ));
    }

    public function segments()
    {
        $segments = NewsletterSegment::whereNull('role_id')
            ->get();

        foreach ($segments as $segment) {
            $segment->recipient_count = $segment->recipientCount();
        }

        return view('admin.newsletters.segments', compact('segments'));
    }

    public function storeSegment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:all_users,plan_tier,signup_date,admins,manual',
            'filter_criteria' => 'nullable|array',
            'filter_criteria.plan_type' => 'nullable|string|in:free,pro,enterprise',
            'filter_criteria.date_from' => 'nullable|date',
            'filter_criteria.date_to' => 'nullable|date|after_or_equal:filter_criteria.date_from',
        ]);

        NewsletterSegment::create([
            'role_id' => null,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'filter_criteria' => $validated['filter_criteria'] ?? null,
        ]);

        return back()->with('status', __('messages.segment_created'));
    }

    public function deleteSegment(string $hash)
    {
        $segment = NewsletterSegment::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $inUse = Newsletter::admin()
            ->whereIn('status', ['draft', 'scheduled'])
            ->whereJsonContains('segment_ids', $segment->id)
            ->exists();

        if ($inUse) {
            return back()->with('error', __('messages.segment_in_use'));
        }

        $segment->delete();

        return back()->with('status', __('messages.segment_deleted'));
    }

    public function updateSegment(Request $request, string $hash)
    {
        $segment = NewsletterSegment::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $segment->update(['name' => $validated['name']]);

        return back()->with('status', __('messages.segment_updated'));
    }

    public function editSegment(string $hash)
    {
        $segment = NewsletterSegment::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($segment->type === 'manual') {
            $subscribers = $segment->segmentUsers()->orderBy('created_at', 'desc')->paginate(50);
            $recipientCount = $segment->segmentUsers()->count();
        } else {
            $recipients = $segment->resolveRecipients();
            $recipientCount = $recipients->unique('email')->count();
            $subscribers = $recipients->take(50);
        }

        return view('admin.newsletters.segment-edit', compact('segment', 'subscribers', 'recipientCount'));
    }

    public function storeSegmentUser(Request $request, string $hash)
    {
        $segment = NewsletterSegment::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->where('type', 'manual')
            ->firstOrFail();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $exists = $segment->segmentUsers()
            ->where('email', strtolower($user->email))
            ->exists();

        if ($exists) {
            return back()->with('error', __('messages.email_already_in_segment'));
        }

        NewsletterSegmentUser::create([
            'newsletter_segment_id' => $segment->id,
            'email' => strtolower($user->email),
            'name' => $user->name,
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        return back()->with('status', __('messages.subscriber_added'));
    }

    public function deleteSegmentUser(Request $request, string $hash, string $userHash)
    {
        $segment = NewsletterSegment::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->where('type', 'manual')
            ->firstOrFail();

        $segmentUser = $segment->segmentUsers()
            ->where('id', UrlUtils::decodeId($userHash))
            ->firstOrFail();

        $segmentUser->delete();

        return back()->with('status', __('messages.subscriber_removed'));
    }

    public function searchUsers(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $q = str_replace(['%', '_'], ['\\%', '\\_'], $request->input('q'));

        $users = User::where(function ($query) use ($q) {
            $query->where('name', 'LIKE', "%{$q}%")
                ->orWhere('email', 'LIKE', "%{$q}%");
        })
            ->select('id', 'name', 'email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    // ── Newsletter Templates ────────────────────────────────────────

    public function templates()
    {
        $userTemplates = NewsletterTemplate::whereNull('role_id')
            ->where('is_system', false)
            ->orderBy('name')
            ->get();

        return view('admin.newsletters.templates', compact('userTemplates'));
    }

    public function createTemplate()
    {
        $defaultBlocks = [
            ['id' => Str::uuid()->toString(), 'type' => 'heading', 'data' => ['text' => '', 'level' => 'h1', 'align' => 'center']],
            ['id' => Str::uuid()->toString(), 'type' => 'text', 'data' => ['content' => '']],
            ['id' => Str::uuid()->toString(), 'type' => 'text', 'data' => ['content' => '']],
        ];

        $segments = collect();

        return view('admin.newsletters.template-edit', [
            'newsletterTemplate' => null,
            'defaultBlocks' => $defaultBlocks,
            'segments' => $segments,
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|in:modern,classic,minimal,bold,compact',
            'style_settings' => 'nullable|array',
        ]);

        $blocks = $this->parseBlocks($request, ['offer']);

        NewsletterTemplate::create([
            'role_id' => null,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
        ]);

        return redirect()->route('admin.newsletters.templates')
            ->with('status', __('messages.template_saved'));
    }

    public function editTemplate(string $hash)
    {
        $newsletterTemplate = NewsletterTemplate::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletterTemplate->is_system) {
            abort(403);
        }

        $segments = collect();

        return view('admin.newsletters.template-edit', [
            'newsletterTemplate' => $newsletterTemplate,
            'segments' => $segments,
        ]);
    }

    public function updateTemplate(Request $request, string $hash)
    {
        $newsletterTemplate = NewsletterTemplate::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletterTemplate->is_system) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|in:modern,classic,minimal,bold,compact',
            'style_settings' => 'nullable|array',
        ]);

        $blocks = $this->parseBlocks($request, ['offer']);

        $newsletterTemplate->update([
            'name' => $validated['name'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
        ]);

        return back()->with('status', __('messages.template_updated'));
    }

    public function deleteTemplate(string $hash)
    {
        $newsletterTemplate = NewsletterTemplate::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletterTemplate->is_system) {
            abort(403);
        }

        $newsletterTemplate->delete();

        return back()->with('status', __('messages.template_deleted'));
    }

    public function saveAsTemplate(Request $request, string $hash)
    {
        $newsletter = Newsletter::admin()
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
        ]);

        NewsletterTemplate::create([
            'role_id' => null,
            'user_id' => auth()->id(),
            'name' => $validated['template_name'],
            'blocks' => $newsletter->blocks,
            'template' => $newsletter->template,
            'style_settings' => $newsletter->style_settings,
        ]);

        return back()->with('status', __('messages.template_saved'));
    }

    public function previewTemplate(string $hash, NewsletterService $service)
    {
        $newsletterTemplate = NewsletterTemplate::whereNull('role_id')
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $newsletter = new Newsletter([
            'role_id' => null,
            'type' => 'admin',
            'subject' => '',
            'blocks' => $newsletterTemplate->blocks,
            'template' => $newsletterTemplate->template,
            'style_settings' => $newsletterTemplate->style_settings,
        ]);

        $html = $service->renderPreview($newsletter);

        return response()->json(['html' => $html]);
    }

    public function uploadImage(Request $request)
    {
        return $this->handleNewsletterImageUpload($request);
    }
}
