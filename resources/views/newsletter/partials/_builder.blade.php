@php
$isAdmin = $isAdmin ?? false;
$isTemplateMode = $isTemplateMode ?? false;

$templateDefaults = [
    // Modern arrives in the schedule's own accent, as a first newsletter does.
    'modern' => ($role ?? null) instanceof \App\Models\Role ? \App\Models\Newsletter::defaultStyleSettingsForRole($role) : \App\Models\Newsletter::templateDefaults('modern'),
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
        $segmentTypeLabels[$segment->id] = \App\Models\NewsletterSegment::typeLabel($segment->type);
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

// NewsletterService::eventChoices(): what a mail could list, under the date it would print.
$eventsData = collect($events ?? [])->values()->all();

$saved = isset($newsletter) && $newsletter->exists;

$newsletterData = null;
if ($saved) {
    $newsletterData = [
        'exists' => true,
        'canSend' => in_array($newsletter->status, ['draft', 'scheduled']),
        'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id),
    ];
}

// NewsletterBuilderRoutes: the same list the first save answers with.
$routesData = \App\Utils\NewsletterBuilderRoutes::for($isAdmin ? null : $role, $saved ? $newsletter : null, $isTemplateMode);

// A test goes to an address the app already knows: the person's own, and for a schedule its
// contact address too.
$me = auth()->user();
$testRecipients = [['value' => 'me', 'label' => __('messages.newsletter_test_to_me'), 'email' => $me->email ?? '']];
if (! $isAdmin && ! empty($role->email) && strtolower($role->email) !== strtolower($me->email ?? '')) {
    $testRecipients[] = ['value' => 'schedule', 'label' => __('messages.newsletter_test_to_schedule'), 'email' => $role->email];
}

// One of the two variants of an A/B test: what is tested, and the way to the other one.
$abTestData = null;
if (! $isAdmin && $saved && $newsletter->ab_test_id && $newsletter->abTest) {
    $abTest = $newsletter->abTest;
    $otherVariant = $abTest->newsletters->first(fn ($variant) => $variant->id !== $newsletter->id && $variant->status !== 'cancelled');
    $abTestData = [
        'variant' => $newsletter->ab_variant,
        'pending' => $abTest->status === 'pending',
        'field' => $abTest->test_field === 'blocks' ? __('messages.content_above_events') : __('messages.subject'),
        'sample' => (int) $abTest->sample_percentage,
        'hours' => (int) $abTest->winner_wait_hours,
        'criteria' => $abTest->winner_criteria === 'click_rate' ? __('messages.click_rate') : __('messages.open_rate'),
        'winner' => $abTest->winner_variant,
        'other_variant' => $otherVariant?->ab_variant,
        'other_url' => $otherVariant && in_array($otherVariant->status, ['draft', 'scheduled'])
            ? route('newsletter.edit', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($otherVariant->id)])
            : null,
    ];
}

// A scheduled newsletter's time, in the clock the schedule dialog reads it in.
$scheduledAtLocal = $saved && $newsletter->status === 'scheduled' && $newsletter->scheduled_at
    ? $newsletter->scheduled_at->copy()->timezone(\App\Http\Controllers\NewsletterController::scheduleTimezone($isAdmin ? null : $role))->format('Y-m-d H:i')
    : '';

$roleSocialLinks = $isAdmin ? [] : \App\Models\Newsletter::buildSocialLinksForRole($role);

$builderProps = [
    'initialBlocks' => $initialBlocks,
    'roleSocialLinks' => $roleSocialLinks,
    'initialTemplate' => $newsletter->template ?? 'modern',
    'initialSubject' => $newsletter->subject ?? '',
    // Laid over the defaults, so a newsletter saved before a setting existed still has its key.
    'initialStyleSettings' => array_merge(\App\Models\Newsletter::defaultStyleSettings(), $newsletter->style_settings ?? []),
    'initialSegmentIds' => $newsletter->segment_ids ?? [],
    'templateDefaults' => $templateDefaults,
    'events' => $eventsData,
    'segments' => $segmentsData,
    'csrfToken' => csrf_token(),
    'newsletter' => $newsletterData,
    'routes' => $routesData,
    'testRecipients' => $testRecipients,
    'everyoneLabel' => $isAdmin ? __('messages.all_platform_users') : __('messages.newsletter_audience_everyone', ['name' => $role->name]),
    'abTest' => $abTestData,
    'scheduledAt' => $scheduledAtLocal,
    'use24Hour' => (bool) get_use_24_hour_time($isAdmin ? null : $role),
    'roleName' => $isAdmin ? config('app.name') : ($role->name ?? ''),
    'isTemplateMode' => $isTemplateMode,
    'isAdmin' => $isAdmin,
    'canUploadImages' => $isAdmin || (isset($role) && $role->isPro()),
    'availableBlockTypes' => $isAdmin
        ? ['heading', 'text', 'button', 'image', 'video', 'divider', 'spacer', 'social_links', 'quote', 'offer']
        : ['heading', 'text', 'events', 'button', 'image', 'video', 'divider', 'spacer', 'social_links', 'quote', 'profile_image', 'header_banner', 'sponsors', 'poll'],
    'translations' => [
        'add_block' => __('messages.add_block'),
        'blocks' => __('messages.blocks'),
        'block_settings' => __('messages.block_settings'),
        'subject' => __('messages.subject'),
        'style_settings' => __('messages.style_settings'),
        'recipients' => __('messages.recipients'),
        'preview' => __('messages.preview'),
        'preview_text' => __('messages.newsletter_preview_text'),
        'preview_text_help' => __('messages.newsletter_preview_text_help'),
        'desktop' => __('messages.newsletter_preview_desktop'),
        'mobile' => __('messages.newsletter_preview_mobile'),
        'system' => __('messages.newsletter_font_system'),
        'send_a_test' => __('messages.send_a_test'),
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
        'image_caption' => __('messages.image_caption'),
        'image_link' => __('messages.image_link'),
        'width' => __('messages.width'),
        'layout' => __('messages.layout'),
        'layout_column' => __('messages.layout_column'),
        'layout_row' => __('messages.layout_row'),
        'layout_grid' => __('messages.layout_grid'),
        'add_image' => __('messages.add_image'),
        'platform' => __('messages.platform'),
        'add_link' => __('messages.add_link'),
        'auto_populated_from_schedule' => __('messages.auto_populated_from_schedule'),
        'drag_blocks_here' => __('messages.drag_blocks_here'),
        'clone_block' => __('messages.clone_block'),
        'remove_block' => __('messages.remove_block'),
        'profile_image' => __('messages.profile_image'),
        'header_image' => __('messages.header_image'),
        'image' => __('messages.image'),
        'images' => __('messages.images'),
        'links' => __('messages.links'),
        'background_color' => __('messages.background_color'),
        'accent_color' => __('messages.accent_color'),
        'text_color' => __('messages.text_color'),
        'font_family' => __('messages.font_family'),
        'button_style' => __('messages.button_style'),
        'rounded' => __('messages.rounded'),
        'square' => __('messages.square'),
        'no_segments' => __('messages.no_segments'),
        'manage_segments' => __('messages.manage_segments'),
        'email' => __('messages.email'),
        'cancel' => __('messages.cancel'),
        'send' => __('messages.send'),
        'send_at' => __('messages.send_at'),
        'schedule' => __('messages.schedule'),
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
        'block_video' => __('messages.block_video'),
        'block_quote' => __('messages.block_quote'),
        'youtube_url' => __('messages.youtube_url'),
        'quote_text' => __('messages.quote_text'),
        'quote_author' => __('messages.quote_author'),
        'quote_author_title' => __('messages.quote_author_title'),
        'save_as_template' => __('messages.save_as_template'),
        'template_name' => __('messages.template_name'),
        'template_name_placeholder' => __('messages.template_name_placeholder'),
        'upload_image' => __('messages.upload_image'),
        'or_enter_url' => __('messages.or_enter_url'),
        'uploading' => __('messages.uploading'),
        'choose_file' => __('messages.choose_file'),
        'error_uploading_image' => __('messages.error_uploading_image'),
        'block_sponsors' => __('messages.block_sponsors'),
        'block_poll' => __('messages.block_poll'),
        'sponsor_source' => __('messages.sponsor_source'),
        'sponsor_source_schedule' => __('messages.sponsor_source_schedule'),
        'sponsor_source_first_event' => __('messages.sponsor_source_first_event'),
        'sponsor_source_help' => __('messages.sponsor_source_help'),
        'poll_auto_description' => __('messages.poll_auto_description'),
        'preview_session_expired' => __('messages.admin_confirm_password'),
        'something_went_wrong' => __('messages.something_went_wrong'),
        'error_uploading_image' => __('messages.error_uploading_image'),
        'to' => __('messages.to'),
        'change' => __('messages.change'),
        'close' => __('messages.close'),
        'undo' => __('messages.undo'),
        'saving' => __('messages.saving'),
        'saved' => __('messages.saved'),
        'sending' => __('messages.sending'),
        'unsaved_changes' => __('messages.unsaved_changes'),
        'move_up' => __('messages.move_up'),
        'move_down' => __('messages.move_down'),
        'design' => __('messages.design'),
        'design_modern' => __('messages.newsletter_design_modern'),
        'design_classic' => __('messages.newsletter_design_classic'),
        'design_minimal' => __('messages.newsletter_design_minimal'),
        'design_bold' => __('messages.newsletter_design_bold'),
        'design_compact' => __('messages.newsletter_design_compact'),
        'newsletter_design_reset' => __('messages.newsletter_design_reset', ['design' => ':design']),
        'templates' => __('messages.templates'),
        'newsletter_save_as_template_lead' => __('messages.newsletter_save_as_template_lead'),
        'newsletter_heading_follows_subject' => __('messages.newsletter_heading_follows_subject'),
        'newsletter_add_after' => __('messages.newsletter_add_after', ['block' => ':block']),
        'newsletter_add_at_end' => __('messages.newsletter_add_at_end'),
        'newsletter_block_removed' => __('messages.newsletter_block_removed', ['block' => ':block']),
        'preview_updating' => __('messages.newsletter_preview_updating'),
        'review_and_send' => __('messages.newsletter_review_and_send'),
        'schedule_for_later' => __('messages.newsletter_schedule_for_later'),
        'newsletter_times_in' => __('messages.newsletter_times_in', ['timezone' => ':timezone']),
        'newsletter_send_test_first' => __('messages.newsletter_send_test_first'),
        'newsletter_allowance' => __('messages.newsletter_allowance'),
        'newsletter_allowance_after' => __('messages.newsletter_allowance_after', ['left' => ':left', 'limit' => ':limit']),
        'newsletter_audience_chosen' => __('messages.newsletter_audience_chosen'),
        'newsletter_audience_none_ticked' => __('messages.newsletter_audience_none_ticked'),
        'newsletter_test_lead' => __('messages.newsletter_test_lead'),
        'ab_testing' => __('messages.ab_testing'),
        'ab_test_description' => __('messages.ab_test_description'),
        'create_ab_test' => __('messages.create_ab_test'),
        'test_field' => __('messages.test_field'),
        'content_above_events' => __('messages.content_above_events'),
        'sample_percentage' => __('messages.sample_percentage'),
        'wait_hours' => __('messages.wait_hours'),
        'winner_criteria' => __('messages.winner_criteria'),
        'open_rate' => __('messages.open_rate'),
        'click_rate' => __('messages.click_rate'),
        'winner' => __('messages.winner'),
        'variant' => __('messages.variant'),
        'send_ab_test' => __('messages.newsletter_send_ab_test'),
        'ab_test_create_note' => __('messages.ab_test_create_note'),
        'ab_test_pending_note' => __('messages.ab_test_pending_note'),
        'ab_test_edit_other' => __('messages.ab_test_edit_other', ['variant' => ':variant']),
        'ab_follows_variant_a' => __('messages.ab_follows_variant_a'),
        'ab_test_review_explain' => __('messages.ab_test_review_explain', ['a' => ':a', 'b' => ':b', 'hours' => ':hours', 'criteria' => ':criteria', 'rest' => ':rest']),
    ],
];
@endphp

<script src="{{ asset('js/sortable.min.js') }}" {!! nonce_attr() !!}></script>
<div id="newsletter-builder" data-props="{{ json_encode($builderProps) }}"></div>
@vite('resources/js/newsletter-builder.js')
