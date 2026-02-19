@php
$isAdmin = $isAdmin ?? false;

$templateDefaults = [
    'modern' => \App\Models\Newsletter::templateDefaults('modern'),
    'classic' => \App\Models\Newsletter::templateDefaults('classic'),
    'minimal' => \App\Models\Newsletter::templateDefaults('minimal'),
    'bold' => \App\Models\Newsletter::templateDefaults('bold'),
    'compact' => \App\Models\Newsletter::templateDefaults('compact'),
];

$initialBlocks = isset($defaultBlocks) ? $defaultBlocks : ($newsletter->blocks ?? []);

$segmentTypeLabels = [];
foreach (($segments ?? collect()) as $segment) {
    if ($isAdmin) {
        if ($segment->type === 'all_users') {
            $segmentTypeLabels[$segment->id] = __('messages.all_platform_users');
        } elseif ($segment->type === 'plan_tier') {
            $segmentTypeLabels[$segment->id] = __('messages.plan_tier');
        } elseif ($segment->type === 'signup_date') {
            $segmentTypeLabels[$segment->id] = __('messages.signup_date');
        } else {
            $segmentTypeLabels[$segment->id] = $segment->type;
        }
    } else {
        if ($segment->type === 'all_followers') {
            $segmentTypeLabels[$segment->id] = __('messages.all_followers');
        } elseif ($segment->type === 'ticket_buyers') {
            $segmentTypeLabels[$segment->id] = __('messages.ticket_buyers');
        } elseif ($segment->type === 'manual') {
            $segmentTypeLabels[$segment->id] = __('messages.manual');
        } else {
            $segmentTypeLabels[$segment->id] = __('messages.group');
        }
    }
}

$segmentsData = ($segments ?? collect())->map(function ($segment) use ($segmentTypeLabels) {
    return [
        'id' => $segment->id,
        'name' => $segment->name,
        'type_label' => $segmentTypeLabels[$segment->id] ?? '',
        'count' => number_format($segment->recipient_count ?? 0),
    ];
})->values()->toArray();

$eventsData = ($events ?? collect())->map(function ($event) {
    return [
        'id' => $event->id,
        'name' => $event->name,
        'date' => $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format('M j, Y') : '',
    ];
})->values()->toArray();

$newsletterData = null;
if (isset($newsletter) && $newsletter->exists) {
    $newsletterData = [
        'exists' => true,
        'canSend' => in_array($newsletter->status, ['draft', 'scheduled']),
    ];
}

if ($isAdmin) {
    $routesData = [
        'manage_segments' => route('admin.newsletters.segments'),
    ];
    if (isset($newsletter) && $newsletter->exists) {
        $routesData['test_send'] = route('admin.newsletters.test_send', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]);
        $routesData['schedule'] = route('admin.newsletters.schedule', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]);
        $routesData['send'] = route('admin.newsletters.send', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]);
    }

    $previewUrl = isset($newsletter) && $newsletter->exists
        ? route('admin.newsletters.preview', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)])
        : route('admin.newsletters.preview_draft');
} else {
    $routesData = [
        'manage_segments' => route('newsletter.segments', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]),
    ];
    if (isset($newsletter) && $newsletter->exists) {
        $routesData['test_send'] = route('newsletter.test_send', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]);
        $routesData['schedule'] = route('newsletter.schedule', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]);
        $routesData['send'] = route('newsletter.send', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]);
    }

    $previewUrl = isset($newsletter) && $newsletter->exists
        ? route('newsletter.preview', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)])
        : route('newsletter.preview_draft', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]);
}

$abTestHtml = '';
if (!$isAdmin && isset($newsletter) && $newsletter->exists) {
    $abTestHtml = view('newsletter.partials._ab-test-panel', ['newsletter' => $newsletter, 'role' => $role])->render();
}

$roleSocialLinks = $isAdmin ? [] : \App\Models\Newsletter::buildSocialLinksForRole($role);

$builderProps = [
    'initialBlocks' => $initialBlocks,
    'roleSocialLinks' => $roleSocialLinks,
    'initialTemplate' => $newsletter->template ?? 'modern',
    'initialSubject' => $newsletter->subject ?? '',
    'initialStyleSettings' => $newsletter->style_settings ?? \App\Models\Newsletter::defaultStyleSettings(),
    'initialSegmentIds' => $newsletter->segment_ids ?? [],
    'templateDefaults' => $templateDefaults,
    'events' => $eventsData,
    'segments' => $segmentsData,
    'previewUrl' => $previewUrl,
    'csrfToken' => csrf_token(),
    'newsletter' => $newsletterData,
    'routes' => $routesData,
    'roleEmail' => $isAdmin ? (auth()->user()->email ?? '') : ($role->email ?? ''),
    'roleName' => $isAdmin ? config('app.name') : ($role->name ?? ''),
    'abTestHtml' => $abTestHtml,
    'availableBlockTypes' => $isAdmin
        ? ['heading', 'text', 'button', 'image', 'divider', 'spacer', 'social_links', 'offer']
        : ['heading', 'text', 'events', 'button', 'image', 'divider', 'spacer', 'social_links', 'profile_image', 'header_banner'],
    'translations' => [
        'add_block' => __('messages.add_block'),
        'blocks' => __('messages.blocks'),
        'block_settings' => __('messages.block_settings'),
        'template' => __('messages.template'),
        'subject' => __('messages.subject'),
        'style_settings' => __('messages.style_settings'),
        'recipients' => __('messages.recipients'),
        'ab_testing' => __('messages.ab_testing'),
        'preview' => __('messages.preview'),
        'edit_blocks' => __('messages.edit_blocks'),
        'test_send' => __('messages.test_send'),
        'test_email_sent_to' => __('messages.test_email_sent_to', ['email' => ':email']),
        'send_a_test' => __('messages.send_a_test'),
        'schedule_newsletter' => __('messages.schedule_newsletter'),
        'save' => __('messages.save'),
        'send_now' => __('messages.send_now'),
        'heading_text' => __('messages.heading_text'),
        'heading_level' => __('messages.heading_level'),
        'heading_header' => __('messages.heading_header'),
        'heading_subheader' => __('messages.heading_subheader'),
        'heading_section' => __('messages.heading_section'),
        'alignment' => __('messages.alignment'),
        'left' => __('messages.left'),
        'center' => __('messages.center'),
        'right' => __('messages.right'),
        'content' => __('messages.content'),
        'markdown_supported' => __('messages.markdown_supported'),
        'event_layout' => __('messages.event_layout'),
        'cards' => __('messages.cards'),
        'list' => __('messages.list'),
        'all_upcoming_events' => __('messages.all_upcoming_events'),
        'events' => __('messages.events'),
        'no_upcoming_events' => __('messages.no_upcoming_events'),
        'button_text' => __('messages.button_text'),
        'button_url' => __('messages.button_url'),
        'divider_style' => __('messages.divider_style'),
        'solid' => __('messages.solid'),
        'dashed' => __('messages.dashed'),
        'dotted' => __('messages.dotted'),
        'spacer_height' => __('messages.spacer_height'),
        'image_url' => __('messages.image_url'),
        'image_alt' => __('messages.image_alt'),
        'width' => __('messages.width'),
        'platform' => __('messages.platform'),
        'add_link' => __('messages.add_link'),
        'auto_populated_from_schedule' => __('messages.auto_populated_from_schedule'),
        'drag_blocks_here' => __('messages.drag_blocks_here'),
        'clone_block' => __('messages.clone_block'),
        'remove_block' => __('messages.remove_block'),
        'profile_image' => __('messages.profile_image'),
        'header_image' => __('messages.header_image'),
        'links' => __('messages.links'),
        'background_color' => __('messages.background_color'),
        'accent_color' => __('messages.accent_color'),
        'text_color' => __('messages.text_color'),
        'font_family' => __('messages.font_family'),
        'button_style' => __('messages.button_style'),
        'rounded' => __('messages.rounded'),
        'square' => __('messages.square'),
        'no_segments' => __('messages.no_segments'),
        'default_all_followers' => $isAdmin
            ? __('messages.default_all_platform_users')
            : __('messages.default_all_followers'),
        'manage_segments' => __('messages.manage_segments'),
        'email' => __('messages.email'),
        'cancel' => __('messages.cancel'),
        'send' => __('messages.send'),
        'send_at' => __('messages.send_at'),
        'schedule' => __('messages.schedule'),
        'newsletter_send_confirm' => __('messages.newsletter_send_confirm'),
        'block_heading' => __('messages.block_heading'),
        'block_text' => __('messages.block_text'),
        'block_events' => __('messages.block_events'),
        'block_button' => __('messages.block_button'),
        'block_image' => __('messages.block_image'),
        'block_divider' => __('messages.block_divider'),
        'block_spacer' => __('messages.block_spacer'),
        'block_social_links' => __('messages.block_social_links'),
        'block_profile_image' => __('messages.block_profile_image'),
        'block_header_banner' => __('messages.block_header_banner'),
        'block_offer' => __('messages.block_offer'),
        'offer_title' => __('messages.offer_title'),
        'original_price' => __('messages.original_price'),
        'sale_price' => __('messages.sale_price'),
        'coupon_code_label' => __('messages.coupon_code_label'),
        'offer_description' => __('messages.offer_description'),
        'style' => __('messages.style'),
        'settings' => __('messages.settings'),
        'done' => __('messages.done'),
        'no_content' => __('messages.no_content'),
        'footer_text' => __('messages.footer_text'),
    ],
];
@endphp

<script src="{{ asset('js/sortable.min.js') }}" {!! nonce_attr() !!}></script>
<div id="newsletter-builder" data-props="{{ json_encode($builderProps) }}"></div>
@vite('resources/js/newsletter-builder.js')
