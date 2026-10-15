{{-- What changed in a request since the version before it, for the partner who approves it: the
     fields by name, then each one Before and Now side by side (one under the other on a phone),
     and the note the partner wrote when it asked for the change. "Changed", never "New".

     Expects $bpVersion (the version waiting), $bpPrevious (the one before it, or null),
     $bpCurrency and $bpDeclineNote (or null). --}}
@php
    $bpChanged = $bpVersion ? $bpVersion->changedFrom($bpPrevious) : [];
    $bpChangeLabels = [
        'headline' => __('messages.headline'),
        'primary_text' => __('messages.primary_text'),
        'description' => __('messages.description'),
        'call_to_action' => __('messages.call_to_action'),
        'image_path' => __('messages.boost_partner_picture'),
        'destination_url' => __('messages.boost_partner_link'),
        'budget' => __('messages.ad_budget'),
    ];
    $bpChangeCta = [
        'LEARN_MORE' => __('messages.cta_learn_more'),
        'GET_TICKETS' => __('messages.cta_get_tickets'),
        'SIGN_UP' => __('messages.cta_sign_up'),
        'BOOK_TRAVEL' => __('messages.cta_book_now'),
    ];
@endphp
@if ($bpVersion && $bpPrevious)
<div class="bp-changes">
    <p class="bp-changes-title">
        @if ($bpChanged)
        {{ __('messages.boost_partner_version_changed', ['version' => $bpVersion->version, 'fields' => implode(', ', array_map(fn ($field) => $bpChangeLabels[$field] ?? $field, $bpChanged))]) }}
        @else
        {{ __('messages.boost_partner_version_unchanged', ['version' => $bpVersion->version]) }}
        @endif
    </p>

    @if (filled($bpDeclineNote))
    <figure class="bp-note">
        <figcaption>{{ __('messages.boost_partner_your_note') }}</figcaption>
        <blockquote><bdi>{{ $bpDeclineNote }}</bdi></blockquote>
    </figure>
    @endif

    @if ($bpChanged)
    <div class="bp-diff">
        @foreach ($bpChanged as $bpField)
        <div class="bp-diff-row">
            <div class="bp-diff-label">{{ $bpChangeLabels[$bpField] ?? $bpField }}</div>
            @foreach ([[$bpPrevious, 'boost_partner_before', ''], [$bpVersion, 'boost_partner_now', ' is-now']] as [$bpSide, $bpTag, $bpClass])
            <div class="bp-diff-cell{{ $bpClass }}">
                <span class="bp-diff-tag">{{ __('messages.'.$bpTag) }}</span>
                @if ($bpField === 'image_path')
                    @if ($bpSide->imageUrl())
                    <img src="{{ $bpSide->imageUrl() }}" alt="">
                    @else
                    {{ __('messages.none') }}
                    @endif
                @elseif ($bpField === 'call_to_action')
                {{ $bpChangeCta[$bpSide->call_to_action] ?? \Illuminate\Support\Str::headline(strtolower((string) $bpSide->call_to_action)) }}
                @elseif ($bpField === 'budget')
                {{ \App\Utils\MoneyUtils::format($bpSide->budget, $bpCurrency) }}
                @elseif ($bpField === 'destination_url')
                <span dir="ltr" class="break-all">{{ $bpSide->destination_url }}</span>
                @elseif (filled($bpSide->{$bpField}))
                <bdi class="bp-text-block">{{ $bpSide->{$bpField} }}</bdi>
                @else
                {{ __('messages.none') }}
                @endif
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif
