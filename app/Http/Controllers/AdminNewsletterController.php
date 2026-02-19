<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Services\NewsletterService;
use App\Utils\UrlUtils;
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

    public function create()
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

        return view('admin.newsletters.create', compact('segments', 'defaultBlocks', 'defaultTemplate', 'defaultStyleSettings', 'defaultSegmentIds'));
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
            'segment_ids' => !empty($validated['segment_ids']) ? array_map('intval', $validated['segment_ids']) : null,
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
            'segment_ids' => !empty($validated['segment_ids']) ? array_map('intval', $validated['segment_ids']) : null,
        ]);

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

        $service->send($newsletter);

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

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $newsletter->update([
            'status' => 'scheduled',
            'scheduled_at' => $validated['scheduled_at'],
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
            'type' => 'required|in:all_users,plan_tier,signup_date',
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
}
