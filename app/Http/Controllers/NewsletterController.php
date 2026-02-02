<?php

namespace App\Http\Controllers;

use App\Jobs\EvaluateAbTestWinner;
use App\Models\Newsletter;
use App\Models\NewsletterAbTest;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterSegmentUser;
use App\Models\User;
use App\Services\NewsletterService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    protected function authorize()
    {
        if (auth()->user()->isAdmin()) {
            return;
        }

        // Allow owners/members of enterprise roles
        $hasEnterpriseRole = auth()->user()->roles()
            ->wherePivot('level', '!=', 'follower')
            ->get()
            ->contains(fn ($role) => $role->isEnterprise());

        if (! $hasEnterpriseRole) {
            abort(403, __('messages.not_authorized'));
        }
    }

    protected function getRoles()
    {
        $roles = auth()->user()->roles()->wherePivot('level', '!=', 'follower')->get();

        if (! auth()->user()->isAdmin()) {
            $roles = $roles->filter(fn ($role) => $role->isEnterprise());
        }

        return $roles;
    }

    protected function getRole(Request $request)
    {
        $roleId = $request->role_id ? UrlUtils::decodeId($request->role_id) : null;
        if (! $roleId) {
            abort(404);
        }

        $role = auth()->user()->roles()->where('roles.id', $roleId)->first();
        if (! $role) {
            abort(404);
        }

        if (! auth()->user()->isAdmin()) {
            if ($role->pivot->level === 'follower' || ! $role->isEnterprise()) {
                abort(403);
            }
        }

        return $role;
    }

    protected function roleIdParam($role)
    {
        return ['role_id' => UrlUtils::encodeId($role->id)];
    }

    protected function sanitizeStyleSettings(?array $settings): array
    {
        $defaults = Newsletter::defaultStyleSettings();
        if (! $settings) {
            return $defaults;
        }

        $allowedFonts = ['Arial', 'Georgia', 'Verdana', 'Trebuchet MS', 'Times New Roman', 'Courier New', 'Helvetica', 'Tahoma'];
        $allowedRadii = ['rounded', 'square'];
        $allowedLayouts = ['cards', 'list'];

        $sanitized = [];
        $sanitized['backgroundColor'] = $this->sanitizeHexColor($settings['backgroundColor'] ?? null, $defaults['backgroundColor']);
        $sanitized['accentColor'] = $this->sanitizeHexColor($settings['accentColor'] ?? null, $defaults['accentColor']);
        $sanitized['textColor'] = $this->sanitizeHexColor($settings['textColor'] ?? null, $defaults['textColor']);
        $sanitized['fontFamily'] = in_array($settings['fontFamily'] ?? '', $allowedFonts) ? $settings['fontFamily'] : $defaults['fontFamily'];
        $sanitized['buttonRadius'] = in_array($settings['buttonRadius'] ?? '', $allowedRadii) ? $settings['buttonRadius'] : $defaults['buttonRadius'];
        $sanitized['eventLayout'] = in_array($settings['eventLayout'] ?? '', $allowedLayouts) ? $settings['eventLayout'] : $defaults['eventLayout'];
        $sanitized['footerText'] = mb_substr(strip_tags(trim($settings['footerText'] ?? '')), 0, 500);

        return $sanitized;
    }

    protected function sanitizeHexColor(?string $value, string $default): string
    {
        if ($value && preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
            return $value;
        }

        return $default;
    }

    protected function parseBlocks(Request $request): ?array
    {
        $blocksJson = $request->input('blocks');
        if (! $blocksJson) {
            return null;
        }

        if (is_string($blocksJson)) {
            $blocks = json_decode($blocksJson, true);
        } else {
            $blocks = $blocksJson;
        }

        if (! is_array($blocks)) {
            return null;
        }

        // Sanitize blocks
        $allowed = ['profile_image', 'header_banner', 'heading', 'text', 'events', 'button', 'divider', 'spacer', 'image', 'social_links'];
        $dangerousSchemes = ['javascript:', 'data:', 'vbscript:'];

        $blocks = array_values(array_filter($blocks, function ($block) use ($allowed) {
            return isset($block['type']) && in_array($block['type'], $allowed) && isset($block['id']);
        }));

        // Sanitize URLs in blocks to prevent javascript: and other dangerous URI schemes
        foreach ($blocks as &$block) {
            if (isset($block['data']['url'])) {
                $urlLower = strtolower(trim($block['data']['url']));
                foreach ($dangerousSchemes as $scheme) {
                    if (str_starts_with($urlLower, $scheme)) {
                        $block['data']['url'] = '#';
                        break;
                    }
                }
            }
            if (isset($block['data']['links']) && is_array($block['data']['links'])) {
                foreach ($block['data']['links'] as &$link) {
                    if (isset($link['url'])) {
                        $urlLower = strtolower(trim($link['url']));
                        foreach ($dangerousSchemes as $scheme) {
                            if (str_starts_with($urlLower, $scheme)) {
                                $link['url'] = '#';
                                break;
                            }
                        }
                    }
                }
                unset($link);
            }
        }
        unset($block);

        return $blocks;
    }

    public function index(Request $request)
    {
        $this->authorize();
        $roles = $this->getRoles();

        $selectedRoleId = $request->role_id ? UrlUtils::decodeId($request->role_id) : null;

        // Validate selected role belongs to user
        if ($selectedRoleId && ! $roles->contains('id', $selectedRoleId)) {
            abort(403);
        }

        // Default to first role if none selected
        if (! $selectedRoleId && $roles->isNotEmpty()) {
            $selectedRoleId = $roles->first()->id;
        }

        $newsletters = collect();
        $role = null;
        if ($selectedRoleId) {
            $role = $roles->firstWhere('id', $selectedRoleId);
            $newsletters = Newsletter::where('role_id', $selectedRoleId)
                ->where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('newsletter.index', compact('roles', 'role', 'selectedRoleId', 'newsletters'));
    }

    public function create(Request $request)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $segments = NewsletterSegment::where('role_id', $role->id)->get();
        $events = $role->events()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        $defaultBlocks = Newsletter::defaultBlocks($role);

        $lastNewsletter = Newsletter::where('role_id', $role->id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->first();

        $defaultTemplate = $lastNewsletter ? $lastNewsletter->template : 'modern';
        $defaultStyleSettings = $lastNewsletter ? $lastNewsletter->style_settings : Newsletter::defaultStyleSettingsForRole($role);
        $defaultSegmentIds = $lastNewsletter ? ($lastNewsletter->segment_ids ?? []) : [];

        return view('newsletter.create', compact('role', 'segments', 'events', 'defaultBlocks', 'defaultTemplate', 'defaultStyleSettings', 'defaultSegmentIds'));
    }

    public function store(Request $request)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|string|max:50',
            'style_settings' => 'nullable|array',
            'segment_ids' => 'nullable|array',
        ]);

        $blocks = $this->parseBlocks($request);
        $service = app(NewsletterService::class);

        $newsletter = Newsletter::create([
            'role_id' => $role->id,
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
            'segment_ids' => $validated['segment_ids'] ?? null,
            'status' => 'draft',
        ]);

        // Derive event_ids from blocks for the send pipeline
        $newsletter->event_ids = $service->deriveEventIds($newsletter);
        $newsletter->save();

        return redirect()->route('newsletter.edit', [
            'hash' => UrlUtils::encodeId($newsletter->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ])->with('status', __('messages.newsletter_saved'));
    }

    public function edit(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $segments = NewsletterSegment::where('role_id', $role->id)->get();
        $events = $role->events()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        return view('newsletter.edit', compact('role', 'newsletter', 'segments', 'events'));
    }

    public function update(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (in_array($newsletter->status, ['sending', 'sent'])) {
            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|string|max:50',
            'style_settings' => 'nullable|array',
            'segment_ids' => 'nullable|array',
        ]);

        $blocks = $this->parseBlocks($request);
        $service = app(NewsletterService::class);

        $newsletter->update([
            'subject' => $validated['subject'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
            'segment_ids' => $validated['segment_ids'] ?? null,
        ]);

        // Derive event_ids from blocks
        $newsletter->event_ids = $service->deriveEventIds($newsletter);
        $newsletter->save();

        return back()->with('status', __('messages.newsletter_saved'));
    }

    public function delete(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletter->status === 'sending') {
            return back()->with('error', __('messages.newsletter_is_sending'));
        }

        $newsletter->update(['status' => 'cancelled']);

        return redirect()->route('newsletter.index', $this->roleIdParam($role))
            ->with('status', __('messages.newsletter_deleted'));
    }

    public function send(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (! in_array($newsletter->status, ['draft', 'scheduled'])) {
            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        if (! $role->canSendNewsletter()) {
            $limit = $role->newsletterLimit();
            $used = $role->newslettersSentThisMonth();

            return back()->with('error', __('messages.newsletter_limit_reached', ['used' => $used, 'limit' => $limit]));
        }

        $service->send($newsletter);

        return redirect()->route('newsletter.index', $this->roleIdParam($role))
            ->with('status', __('messages.newsletter_sending'));
    }

    public function schedule(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (! $role->canSendNewsletter()) {
            $limit = $role->newsletterLimit();
            $used = $role->newslettersSentThisMonth();

            return back()->with('error', __('messages.newsletter_limit_reached', ['used' => $used, 'limit' => $limit]));
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

    public function cancel(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
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

    public function cloneNewsletter(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $copy = $newsletter->replicate();
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

        return redirect()->route('newsletter.edit', [
            'hash' => UrlUtils::encodeId($copy->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ])->with('status', __('messages.newsletter_cloned'));
    }

    public function previewDraft(Request $request, NewsletterService $service)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $blocks = $this->parseBlocks($request);

        $newsletter = new Newsletter([
            'role_id' => $role->id,
            'subject' => $request->input('subject', ''),
            'blocks' => $blocks,
            'template' => $request->input('template', 'modern'),
            'style_settings' => $this->sanitizeStyleSettings($request->input('style_settings')),
        ]);
        $newsletter->setRelation('role', $role);

        $html = $service->renderPreview($newsletter);

        return response()->json(['html' => $html]);
    }

    public function preview(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        // If form data was submitted, update the newsletter temporarily for preview
        if ($request->has('subject')) {
            $newsletter->subject = $request->input('subject');
            $newsletter->blocks = $this->parseBlocks($request);
            $newsletter->template = $request->input('template', 'modern');
            $newsletter->style_settings = $this->sanitizeStyleSettings($request->input('style_settings'));
        }

        $html = $service->renderPreview($newsletter);

        return response()->json(['html' => $html]);
    }

    public function testSend(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorize();
        $role = $this->getRole($request);

        if (empty($role->email)) {
            return back()->with('error', __('messages.email_required'));
        }

        $rateLimitKey = 'test-newsletter-' . $role->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return back()->with('error', __('messages.please_wait') . '...');
        }

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        // Create a temporary recipient for the test
        $recipient = new NewsletterRecipient([
            'newsletter_id' => $newsletter->id,
            'email' => $role->email,
            'name' => $role->name,
            'token' => Str::random(64),
            'status' => 'pending',
        ]);
        $recipient->save();

        $result = $service->sendToRecipient($newsletter, $recipient);
        $recipient->update(['status' => 'test']);

        RateLimiter::hit($rateLimitKey, 60);

        if (! $result) {
            return back()->with('error', __('messages.test_email_failed'));
        }

        return back()->with('status', __('messages.test_email_sent_to', ['email' => $role->email]));
    }

    public function stats(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $recipients = $newsletter->recipients()
            ->orderBy('opened_at', 'desc')
            ->paginate(50);

        // Top clicked links
        $topLinks = \App\Models\NewsletterClick::whereHas('recipient', function ($q) use ($newsletter) {
            $q->where('newsletter_id', $newsletter->id);
        })
            ->selectRaw('url, COUNT(*) as click_count')
            ->groupBy('url')
            ->orderByDesc('click_count')
            ->limit(10)
            ->get();

        // Open timeline data (group by hour for first 48h, then by day)
        $openTimeline = $newsletter->recipients()
            ->whereNotNull('opened_at')
            ->selectRaw('DATE(opened_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Click timeline data
        $clickTimeline = \App\Models\NewsletterClick::whereHas('recipient', function ($q) use ($newsletter) {
            $q->where('newsletter_id', $newsletter->id);
        })
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // A/B test comparison
        $abTest = $newsletter->abTest;

        return view('newsletter.stats', compact(
            'role',
            'newsletter',
            'recipients',
            'topLinks',
            'openTimeline',
            'clickTimeline',
            'abTest'
        ));
    }

    public function getEvents(Request $request)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $events = $role->events()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get(['events.id', 'events.name', 'events.starts_at'])
            ->map(fn ($e) => [
                'id' => $e->id,
                'hash' => UrlUtils::encodeId($e->id),
                'name' => $e->name,
                'date' => $e->starts_at ? \Carbon\Carbon::parse($e->starts_at)->format('M j, Y') : '',
            ]);

        return response()->json($events);
    }

    public function segments(Request $request)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $segments = NewsletterSegment::where('role_id', $role->id)
            ->withCount('segmentUsers')
            ->get();

        // Add recipient counts
        foreach ($segments as $segment) {
            $segment->recipient_count = $segment->recipientCount();
        }

        return view('newsletter.segments', compact('role', 'segments'));
    }

    public function storeSegment(Request $request)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:all_followers,ticket_buyers,manual,group',
            'filter_criteria' => 'nullable|array',
            'emails' => 'nullable|string',
        ]);

        $segment = NewsletterSegment::create([
            'role_id' => $role->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'filter_criteria' => $validated['filter_criteria'] ?? null,
        ]);

        // Add manual users if provided
        if ($validated['type'] === 'manual' && ! empty($validated['emails'])) {
            $emails = array_filter(array_map('trim', explode("\n", $validated['emails'])));
            foreach ($emails as $line) {
                $parts = array_map('trim', explode(',', $line));
                $email = $parts[0] ?? '';
                $name = $parts[1] ?? null;
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    NewsletterSegmentUser::create([
                        'newsletter_segment_id' => $segment->id,
                        'email' => strtolower($email),
                        'name' => $name,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        return back()->with('status', __('messages.segment_created'));
    }

    public function updateSegment(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $segment = NewsletterSegment::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'filter_criteria' => 'nullable|array',
        ]);

        $segment->update([
            'name' => $validated['name'],
            'filter_criteria' => $validated['filter_criteria'] ?? null,
        ]);

        return back()->with('status', __('messages.segment_updated'));
    }

    public function deleteSegment(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $segment = NewsletterSegment::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $segment->delete();

        return back()->with('status', __('messages.segment_deleted'));
    }

    public function createAbTest(Request $request, string $hash)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletter->ab_test_id) {
            return back()->with('error', __('messages.ab_test_already_exists'));
        }

        $validated = $request->validate([
            'test_field' => 'required|in:subject,blocks',
            'sample_percentage' => 'required|integer|min:5|max:50',
            'winner_criteria' => 'required|in:open_rate,click_rate',
            'winner_wait_hours' => 'required|integer|min:1|max:72',
        ]);

        $abTest = NewsletterAbTest::create([
            'role_id' => $role->id,
            'name' => $newsletter->subject.' - A/B Test',
            'test_field' => $validated['test_field'],
            'sample_percentage' => $validated['sample_percentage'],
            'winner_criteria' => $validated['winner_criteria'],
            'winner_wait_hours' => $validated['winner_wait_hours'],
            'status' => 'pending',
        ]);

        // Set the original as variant A
        $newsletter->update([
            'ab_test_id' => $abTest->id,
            'ab_variant' => 'A',
        ]);

        // Create variant B as a copy
        $variantB = $newsletter->replicate();
        $variantB->ab_test_id = $abTest->id;
        $variantB->ab_variant = 'B';
        $variantB->status = 'draft';
        $variantB->send_token = null;
        $variantB->save();

        return redirect()->route('newsletter.edit', [
            'hash' => UrlUtils::encodeId($variantB->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ])->with('status', __('messages.ab_test_created'));
    }

    public function sendAbTest(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorize();
        $role = $this->getRole($request);

        if (! $role->canSendNewsletter()) {
            $limit = $role->newsletterLimit();
            $used = $role->newslettersSentThisMonth();

            return back()->with('error', __('messages.newsletter_limit_reached', ['used' => $used, 'limit' => $limit]));
        }

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (! $newsletter->ab_test_id) {
            return back()->with('error', __('messages.no_ab_test'));
        }

        $abTest = $newsletter->abTest;
        $variants = $abTest->newsletters;

        if ($variants->count() < 2) {
            return back()->with('error', __('messages.ab_test_needs_variants'));
        }

        // Resolve full recipient list
        $allRecipients = $service->resolveRecipients($role, $newsletter->segment_ids ?? []);
        $sampleSize = (int) ceil($allRecipients->count() * ($abTest->sample_percentage / 100));
        $sample = $allRecipients->random(min($sampleSize, $allRecipients->count()));

        // Split sample between variants
        $halfSize = (int) ceil($sample->count() / 2);
        $sampleA = $sample->slice(0, $halfSize)->values();
        $sampleB = $sample->slice($halfSize)->values();

        $abTest->update(['status' => 'sending']);

        // Send each variant to its sample
        foreach ([['newsletter' => $variants->where('ab_variant', 'A')->first(), 'recipients' => $sampleA],
            ['newsletter' => $variants->where('ab_variant', 'B')->first(), 'recipients' => $sampleB]] as $variant) {
            $vn = $variant['newsletter'];
            $vn->update(['status' => 'sending', 'send_token' => Str::random(64)]);

            $recipientIds = [];
            foreach ($variant['recipients'] as $recipient) {
                $nr = NewsletterRecipient::create([
                    'newsletter_id' => $vn->id,
                    'user_id' => $recipient->user_id,
                    'email' => $recipient->email,
                    'name' => $recipient->name,
                    'token' => Str::random(64),
                    'status' => 'pending',
                ]);
                $recipientIds[] = $nr->id;
            }

            $chunks = array_chunk($recipientIds, 50);
            foreach ($chunks as $chunk) {
                \App\Jobs\SendNewsletterBatch::dispatch($vn->id, $chunk);
            }
        }

        // Schedule winner evaluation
        EvaluateAbTestWinner::dispatch($abTest->id)
            ->delay(now()->addHours($abTest->winner_wait_hours));

        return redirect()->route('newsletter.index', $this->roleIdParam($role))
            ->with('status', __('messages.ab_test_sending'));
    }

    public function importForm(Request $request)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $manualSegments = NewsletterSegment::where('role_id', $role->id)
            ->where('type', 'manual')
            ->get();

        return view('newsletter.import', compact('role', 'manualSegments'));
    }

    public function importStore(Request $request)
    {
        $this->authorize();
        $role = $this->getRole($request);

        $validated = $request->validate([
            'segment_target' => 'required|in:new,existing',
            'segment_name' => 'required_if:segment_target,new|nullable|string|max:255',
            'segment_id' => 'required_if:segment_target,existing|nullable|string',
            'entries' => 'required|array|min:1|max:10000',
            'entries.*.email' => 'required|email',
            'entries.*.name' => 'nullable|string|max:255',
        ]);

        if ($validated['segment_target'] === 'new') {
            $segment = NewsletterSegment::create([
                'role_id' => $role->id,
                'name' => $validated['segment_name'],
                'type' => 'manual',
            ]);
        } else {
            $segment = NewsletterSegment::where('role_id', $role->id)
                ->where('id', UrlUtils::decodeId($validated['segment_id']))
                ->where('type', 'manual')
                ->firstOrFail();
        }

        $imported = DB::transaction(function () use ($segment, $validated, $role) {
            $existingEmails = $segment->segmentUsers()
                ->pluck('email')
                ->map(fn ($e) => strtolower($e))
                ->toArray();

            $allEmails = collect($validated['entries'])->pluck('email')->map(fn ($e) => strtolower(trim($e)))->unique();
            $existingUsers = User::whereIn('email', $allEmails)->get()->keyBy('email');
            $existingFollowerIds = DB::table('role_user')->where('role_id', $role->id)
                ->whereIn('user_id', $existingUsers->pluck('id'))->pluck('user_id')->flip();

            $imported = 0;
            $seen = [];
            foreach ($validated['entries'] as $entry) {
                $email = strtolower(trim($entry['email']));
                if (in_array($email, $seen) || in_array($email, $existingEmails)) {
                    continue;
                }
                $seen[] = $email;

                $user = $existingUsers->get($email);
                if (! $user) {
                    $user = User::create([
                        'email' => $email,
                        'name' => $entry['name'] ?? null,
                        'is_subscribed' => true,
                    ]);
                    $existingUsers->put($email, $user);
                }

                if (! $existingFollowerIds->has($user->id)) {
                    $role->followers()->attach($user->id, ['level' => 'follower', 'created_at' => now()]);
                    $existingFollowerIds->put($user->id, true);
                }

                NewsletterSegmentUser::create([
                    'newsletter_segment_id' => $segment->id,
                    'user_id' => $user->id,
                    'email' => $email,
                    'name' => $entry['name'] ?? null,
                    'created_at' => now(),
                ]);
                $imported++;
            }

            return $imported;
        });

        if ($imported === 0) {
            return back()->with('error', __('messages.no_valid_emails'));
        }

        return redirect()->route('newsletter.segments', $this->roleIdParam($role))
            ->with('status', __('messages.emails_imported', ['count' => $imported]));
    }

    protected function parseEmailInput(string $text): array
    {
        $results = [];
        $seen = [];

        $lines = preg_split('/\r?\n/', $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Try "Name <email>" format
            if (preg_match('/^(.+?)\s*<([^>]+)>/', $line, $matches)) {
                $name = trim($matches[1]);
                $email = strtolower(trim($matches[2]));
                if (filter_var($email, FILTER_VALIDATE_EMAIL) && ! isset($seen[$email])) {
                    $seen[$email] = true;
                    $results[] = ['email' => $email, 'name' => $name];
                }

                continue;
            }

            // Split by comma for CSV-style or comma-separated
            $parts = array_map('trim', explode(',', $line));

            if (count($parts) === 2 && filter_var($parts[0], FILTER_VALIDATE_EMAIL) && ! filter_var($parts[1], FILTER_VALIDATE_EMAIL)) {
                // "email, Name" format
                $email = strtolower($parts[0]);
                $name = $parts[1];
                if (! isset($seen[$email])) {
                    $seen[$email] = true;
                    $results[] = ['email' => $email, 'name' => $name];
                }
            } else {
                // Comma-separated emails or single email
                foreach ($parts as $part) {
                    $part = trim($part);
                    // Check for "Name <email>" within comma-separated list
                    if (preg_match('/^(.+?)\s*<([^>]+)>/', $part, $matches)) {
                        $name = trim($matches[1]);
                        $email = strtolower(trim($matches[2]));
                        if (filter_var($email, FILTER_VALIDATE_EMAIL) && ! isset($seen[$email])) {
                            $seen[$email] = true;
                            $results[] = ['email' => $email, 'name' => $name];
                        }
                    } elseif (filter_var($part, FILTER_VALIDATE_EMAIL)) {
                        $email = strtolower($part);
                        if (! isset($seen[$email])) {
                            $seen[$email] = true;
                            $results[] = ['email' => $email, 'name' => null];
                        }
                    }
                }
            }
        }

        return $results;
    }
}
