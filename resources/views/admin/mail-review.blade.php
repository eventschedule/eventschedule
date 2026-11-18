<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'mail-review'])

    {{-- What AudienceMailGuard held back for an operator (AdminMailReviewController): newsletters
         held for review, then accounts whose email sending is paused, the ones that asked for it
         back first. Each newsletter row carries what a decision needs: who sent it, how new the
         account is and whether it has paid, how many of its readers came from an import, the
         watched word that held it, and a preview. Not a Vue mount: everything an account typed is
         printed as text. --}}
    @php
        $day = fn ($moment) => $moment ? $moment->copy()->setTimezone(auth()->user()->timezone ?: config('app.timezone'))->translatedFormat('M j, Y') : null;
        $planNames = ['free' => __('messages.free'), 'pro' => __('messages.pro_plan'), 'enterprise' => __('messages.enterprise_plan')];
        $field = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ __('messages.mail_review_lead') }}</p>
    </div>

    <div class="page-shell page-stack">
        <x-page-flash :keys="['success' => 'success', 'warning' => 'warn', 'error' => 'error']" />

        {{-- ===================== Held newsletters ===================== --}}
        <div class="page-subhead" id="held">
            <h2>@lang('messages.mail_review_held_title')</h2>
            <p>{{ __('messages.mail_review_held_lead', ['count' => \App\Services\AudienceMailGuard::reviewThreshold()]) }}</p>
        </div>

        <div>
            @if ($rows->isNotEmpty())
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.newsletter')</th>
                                <th scope="col">@lang('messages.account')</th>
                                <th scope="col" class="c-num">@lang('messages.recipients')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                            @php
                                $newsletter = $row['newsletter'];
                                $owner = $row['owner'];
                                $newsletterHash = \App\Utils\UrlUtils::encodeId($newsletter->id);
                                $ownerHash = $owner ? \App\Utils\UrlUtils::encodeId($owner->id) : null;
                            @endphp
                            <tr>
                                <td class="c-main c-wrap">
                                    <a href="{{ route('admin.mail_review.preview', ['hash' => $newsletterHash]) }}" target="_blank" rel="noopener" class="c-strong"><bdi>{{ $newsletter->subject }}</bdi></a>
                                    <span class="c-sub"><bdi>{{ $row['role']?->name }}</bdi> <span dir="ltr">{{ $row['role']?->subdomain }}</span></span>
                                    <span class="c-sub">{{ __('messages.mail_review_held_on', ['date' => $day($newsletter->held_at ?? $newsletter->updated_at)]) }}</span>
                                    @if ($row['word'])
                                    <span class="event-status is-warn">{{ __('messages.mail_review_word', ['word' => $row['word']]) }}</span>
                                    @endif
                                </td>
                                <td class="c-wrap" data-label="{{ __('messages.account') }}">
                                    @if ($owner)
                                    <a href="{{ route('admin.blocked.account', ['hash' => $ownerHash]) }}" class="c-strong"><bdi>{{ $owner->name ?: $owner->email }}</bdi></a>
                                    <span class="c-sub" dir="ltr">{{ $owner->email }}</span>
                                    <span class="c-sub">{{ __('messages.mail_review_signed_up', ['date' => $day($owner->created_at)]) }}</span>
                                    <span class="c-sub">
                                        {{ $planNames[$row['plan']] ?? $row['plan'] }}@if ($row['trial']) · @lang('messages.mail_review_on_trial')@endif
                                        · {{ $row['paid'] ? __('messages.mail_review_has_paid') : __('messages.mail_review_never_paid') }}
                                    </span>
                                    @if ($owner->mail_disabled_at)
                                    <span class="event-status is-bad">@lang('messages.mail_paused_chip')</span>
                                    @endif
                                    @else
                                    <span class="c-sub">@lang('messages.none')</span>
                                    @endif
                                </td>
                                <td class="c-num" data-label="{{ __('messages.recipients') }}">
                                    {{ number_format($row['mix']['sends_to']) }}
                                    <span class="c-sub">{{ __('messages.mail_review_mix', ['opted' => number_format($row['mix']['opted_in']), 'imported' => number_format($row['mix']['imported'])]) }}</span>
                                </td>
                                <td class="c-actions">
                                    <a href="{{ route('admin.mail_review.preview', ['hash' => $newsletterHash]) }}" target="_blank" rel="noopener" class="event-link">@lang('messages.preview')</a>
                                    <form method="POST" action="{{ route('admin.mail_review.approve', ['hash' => $newsletterHash]) }}" data-confirm="{{ __('messages.mail_review_approve_confirm') }}">
                                        @csrf
                                        <button type="submit" class="event-link">@lang('messages.mail_review_approve')</button>
                                    </form>
                                    @if ($owner && ! $owner->mail_disabled_at && ! $owner->isAdmin())
                                    <form method="POST" action="{{ route('admin.mail_review.pause', ['hash' => $ownerHash]) }}">
                                        @csrf
                                        <button type="submit" class="event-link is-danger">@lang('messages.mail_pause_account')</button>
                                    </form>
                                    @endif
                                    <details class="mail-review-reject">
                                        <summary class="event-link">@lang('messages.mail_review_reject')</summary>
                                        <form method="POST" action="{{ route('admin.mail_review.reject', ['hash' => $newsletterHash]) }}" class="mail-review-reject-form">
                                            @csrf
                                            <label for="reject-note-{{ $newsletterHash }}" class="sr-only">@lang('messages.mail_review_note_label')</label>
                                            <textarea name="note" id="reject-note-{{ $newsletterHash }}" rows="3" maxlength="1000" dir="auto" class="{{ $field }}" placeholder="{{ __('messages.mail_review_default_note') }}"></textarea>
                                            <button type="submit" class="event-link">@lang('messages.mail_review_reject_send')</button>
                                        </form>
                                    </details>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty compact :title="__('messages.mail_review_none_title')" :text="__('messages.mail_review_none_text')" />
            </div>
            @endif

            @if ($held->hasPages())
            <div class="page-pager">{{ $held->links() }}</div>
            @endif
        </div>

        {{-- ===================== Paused email ===================== --}}
        <div class="page-subhead" id="requests">
            <h2>@lang('messages.mail_paused_title')</h2>
            <p>@lang('messages.mail_paused_admin_lead')</p>
        </div>

        <div>
            @if ($requests->isNotEmpty() || $paused->total() > 0)
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.account')</th>
                                <th scope="col">@lang('messages.mail_paused_col_reason')</th>
                                <th scope="col">@lang('messages.status')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests->concat($paused->getCollection()) as $account)
                            @php
                                $accountHash = \App\Utils\UrlUtils::encodeId($account->id);
                                $asked = $requests->contains('id', $account->id);
                            @endphp
                            <tr>
                                <td class="c-main c-wrap">
                                    <a href="{{ route('admin.blocked.account', ['hash' => $accountHash]) }}" class="c-strong"><bdi>{{ $account->name ?: $account->email }}</bdi></a>
                                    <span class="c-sub" dir="ltr">{{ $account->email }}</span>
                                    <span class="c-sub">{{ __('messages.mail_review_signed_up', ['date' => $day($account->created_at)]) }}</span>
                                </td>
                                <td class="c-wrap" data-label="{{ __('messages.mail_paused_col_reason') }}">
                                    {{ \App\Services\AudienceMailGuard::reasonLabel($account->mail_disabled_reason) }}
                                    <span class="c-sub">{{ $day($account->mail_disabled_at) }}</span>
                                </td>
                                <td data-label="{{ __('messages.status') }}">
                                    @if ($asked)
                                    <span class="event-status is-warn">{{ __('messages.mail_access_asked_on', ['date' => $day($account->mail_access_requested_at)]) }}</span>
                                    @else
                                    <span class="event-status is-bad">@lang('messages.mail_paused_chip')</span>
                                    @endif
                                </td>
                                <td class="c-actions">
                                    <a href="{{ route('admin.blocked.account', ['hash' => $accountHash]) }}" class="event-link">@lang('messages.review')</a>
                                    <form method="POST" action="{{ route('admin.mail_review.enable', ['hash' => $accountHash]) }}">
                                        @csrf
                                        <button type="submit" class="event-link">@lang('messages.mail_enable')</button>
                                    </form>
                                    @if ($asked)
                                    <form method="POST" action="{{ route('admin.mail_review.keep_paused', ['hash' => $accountHash]) }}">
                                        @csrf
                                        <button type="submit" class="event-link event-link-quiet">@lang('messages.mail_keep_paused')</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty compact :title="__('messages.mail_paused_none_title')" :text="__('messages.mail_paused_none_text')" />
            </div>
            @endif

            @if ($paused->hasPages())
            <div class="page-pager">{{ $paused->links() }}</div>
            @endif
        </div>

        {{-- ===================== Flagged, nothing stopped ===================== --}}
        {{-- An account that has paid is never paused or held automatically: what would have done
             it (a watched word, a shared card) is shown here instead, for a look. --}}
        @if ($flagged->isNotEmpty())
        <div class="page-subhead" id="flagged">
            <h2>@lang('messages.mail_flagged_title')</h2>
            <p>@lang('messages.mail_flagged_lead')</p>
        </div>

        <div class="ap-card rounded-xl overflow-hidden">
            <div class="page-scroll">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.account')</th>
                            <th scope="col">@lang('messages.mail_paused_col_reason')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($flagged as $account)
                        @php $accountHash = \App\Utils\UrlUtils::encodeId($account->id); @endphp
                        <tr>
                            <td class="c-main c-wrap">
                                <a href="{{ route('admin.blocked.account', ['hash' => $accountHash]) }}" class="c-strong"><bdi>{{ $account->name ?: $account->email }}</bdi></a>
                                <span class="c-sub" dir="ltr">{{ $account->email }}</span>
                            </td>
                            <td class="c-wrap" data-label="{{ __('messages.mail_paused_col_reason') }}">
                                {{ \App\Services\AudienceMailGuard::reasonLabel($account->mail_flag_reason) }}
                                <span class="c-sub">{{ $day($account->mail_flagged_at) }}</span>
                            </td>
                            <td class="c-actions">
                                <a href="{{ route('admin.blocked.account', ['hash' => $accountHash]) }}" class="event-link">@lang('messages.review')</a>
                                <form method="POST" action="{{ route('admin.mail_review.dismiss_flag', ['hash' => $accountHash]) }}">
                                    @csrf
                                    <button type="submit" class="event-link">@lang('messages.mail_flag_dismiss')</button>
                                </form>
                                <form method="POST" action="{{ route('admin.mail_review.pause', ['hash' => $accountHash]) }}">
                                    @csrf
                                    <button type="submit" class="event-link is-danger">@lang('messages.mail_pause_account')</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* Send back opens under its own link, with the note the owner will read. */
            .mail-review-reject summary {
              cursor: pointer;
              list-style: none;
            }
            .mail-review-reject summary::-webkit-details-marker {
              display: none;
            }
            .mail-review-reject-form {
              display: flex;
              flex-direction: column;
              align-items: flex-end;
              gap: 0.5rem;
              margin-top: 0.5rem;
              min-width: 16rem;
            }
        </style>
    </x-slot>
</x-app-admin-layout>
