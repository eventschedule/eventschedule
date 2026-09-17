{{-- Plain text: the same decisions as the HTML part, from the same data. Unescaped on purpose:
     this part is text/plain, so {{ }} would only print entities like &quot; at the reader. $host
     is FederatedInstance::displayHost(), so plain-text clients do not auto-link it either. --}}
{!! __('messages.federation_welcome_heading') !!}
{!! __('messages.federation_welcome_subheading', ['host' => $host]) !!}

{!! __('messages.federation_welcome_intro') !!}

@if ($state === 'live')
{!! trans_choice('messages.federation_welcome_live_title', $liveCount, ['count' => number_format($liveCount)]) !!}
@elseif ($state === 'received')
{!! trans_choice('messages.federation_welcome_received_title', $receivedCount, ['count' => number_format($receivedCount)]) !!}
@else
{!! __('messages.federation_welcome_empty_title') !!}
{!! __('messages.federation_welcome_empty_body', $labels) !!}
@endif

{!! $ctaLabel !!}: {!! $ctaUrl !!}

{!! __('messages.federation_welcome_steps_title') !!}

1. {!! __('messages.federation_welcome_step_list_title') !!}
@if ($oneClick)
{!! __('messages.federation_welcome_step_list_body_oneclick', $labels + ['host' => $host]) !!}
@else
{!! __('messages.federation_welcome_step_list_body_manual', $labels + ['host' => $host]) !!}
@endif
@if ($showUpdateTip)
{!! __('messages.federation_welcome_step_update_tip', ['version' => $installedVersion, 'required' => $requiredVersion]) !!}
{!! __('messages.federation_welcome_update_link') !!}: {!! $updateGuideUrl !!}
@endif

2. {!! __('messages.federation_welcome_step_qualify_title') !!}
{!! __('messages.federation_welcome_step_qualify_body') !!}

3. {!! __('messages.federation_welcome_step_sync_title') !!}
{!! __('messages.federation_welcome_step_sync_body') !!}

    php artisan federation:push

@if ($state !== 'empty')
{!! __('messages.federation_welcome_guide') !!}: {!! $guideUrl !!}
@endif
{!! __('messages.federation_welcome_browse') !!}: {!! $browseUrl !!}

{!! __('messages.federation_welcome_reply') !!}

{!! __('messages.federation_welcome_why', ['host' => $host]) !!}
