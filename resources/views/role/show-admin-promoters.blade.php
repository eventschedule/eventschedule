@php
    // RoleController::viewAdmin builds $promoters on this tab alone: the links, what each brought
    // in (PromoterLinks::stats()), and the schedule's own coming events with tickets.
    $promoterLinks = $promoters['links'] ?? collect();
    $promoterStats = $promoters['stats'] ?? [];
    $promoterEvents = $promoters['events'] ?? collect();
    $promotersAllowed = $role->isPro();
    $scheduleUrl = $role->getGuestUrl(true);
    $promoterTargets = [['url' => $scheduleUrl, 'label' => __('messages.promoter_whole_schedule')]];
    foreach ($promoterEvents as $promoterEvent) {
        $eventUrl = $promoterEvent->getGuestUrl($role->subdomain, null, true);
        if ($eventUrl) {
            $promoterTargets[] = ['url' => $eventUrl, 'label' => $promoterEvent->translatedName()];
        }
    }
    $withVia = fn (string $url, string $code) => $url.(str_contains($url, '?') ? '&' : '?').'via='.rawurlencode($code);
    $moneyList = fn (array $amounts) => $amounts
        ? collect($amounts)->map(fn ($amount, $currency) => \App\Utils\MoneyUtils::format($amount, $currency))->implode(', ')
        : '-';
    // After a refused save, the form it came from opens again with what was typed.
    $promoterForm = old('promoter_form');
    // The add form is the page while there is nothing to list; after that the list comes first
    // and the form opens from the button in the head.
    $addOpen = $promoterLinks->isEmpty() || $promoterForm === 'add';
@endphp

@if (! $promotersAllowed && $promoterLinks->isEmpty())
<x-plan-gate tier="pro" :title="__('messages.promoters')" :subdomain="$role->subdomain" :role="$role"
    :canUpgrade="auth()->id() == $role->user_id" source="promoters"
    :bullets="[__('messages.promoter_gate_bullet_links'), __('messages.promoter_gate_bullet_sales'), __('messages.promoter_gate_bullet_commission')]">
    {{ __('messages.promoter_gate_text') }}
</x-plan-gate>
@else

@if (! $promotersAllowed)
{{-- Links from when the plan had them: no sale is credited now, and what they earned stays here. --}}
<x-plan-gate tier="pro" variant="banner" :title="__('messages.promoters')" :subdomain="$role->subdomain" :role="$role"
    :canUpgrade="auth()->id() == $role->user_id" source="promoters">
    {{ __('messages.promoter_need_pro') }}
</x-plan-gate>
@endif

<div class="page-head">
    <p class="page-lead">{{ __('messages.promoters_lead') }}</p>
    <div class="page-actions">
        @if ($promotersAllowed && $promoterLinks->isNotEmpty())
        <x-brand-button type="button" data-promoter-add-open aria-controls="promoter-add" :aria-expanded="$addOpen ? 'true' : 'false'">
            <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            {{ __('messages.promoter_add_button') }}
        </x-brand-button>
        @endif
    </div>
</div>

@if ($promotersAllowed)
<form method="POST" action="{{ route('role.promoters.store', ['subdomain' => $role->subdomain]) }}" class="mb-4" id="promoter-add" @unless ($addOpen) hidden @endunless>
    @csrf
    <input type="hidden" name="promoter_form" value="add">
    <x-page-card :title="__('messages.promoter_add_title')">
        <div class="page-form-fields">
            <div>
                <x-input-label for="promoter-name" :value="__('messages.name') . ' *'" />
                <x-text-input id="promoter-name" name="name" type="text" class="mt-1 block w-full" maxlength="100"
                    :value="$promoterForm === 'add' ? old('name') : ''" required />
                @if ($promoterForm === 'add')<x-input-error class="mt-2" :messages="$errors->get('name')" />@endif
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="promoter-code" :value="__('messages.promoter_code')" />
                    <x-text-input id="promoter-code" name="code" type="text" class="mt-1 block w-full" maxlength="40" dir="ltr"
                        pattern="[A-Za-z0-9_\-]+" :value="$promoterForm === 'add' ? old('code') : ''" aria-describedby="promoter-code-help" />
                    <p class="event-hint mt-1" id="promoter-code-help">{{ __('messages.promoter_code_help') }}</p>
                    @if ($promoterForm === 'add')<x-input-error class="mt-2" :messages="$errors->get('code')" />@endif
                </div>
                <div>
                    <x-input-label for="promoter-commission" :value="__('messages.promoter_commission')" />
                    <x-text-input id="promoter-commission" name="commission_percent" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full"
                        :value="$promoterForm === 'add' ? old('commission_percent') : ''" aria-describedby="promoter-commission-help" />
                    <p class="event-hint mt-1" id="promoter-commission-help">{{ __('messages.promoter_commission_help') }}</p>
                    @if ($promoterForm === 'add')<x-input-error class="mt-2" :messages="$errors->get('commission_percent')" />@endif
                </div>
            </div>
        </div>
        <div class="page-form-actions">
            @if ($promoterLinks->isNotEmpty())
            <button type="button" class="event-link" data-promoter-add-close>{{ __('messages.cancel') }}</button>
            @endif
            <x-brand-button type="submit">{{ __('messages.promoter_add_button') }}</x-brand-button>
        </div>
    </x-page-card>
</form>
@endif

@if ($promoterLinks->isNotEmpty())

{{-- Which sales count, said where the figures are read. --}}
<p class="event-hint mb-3">{{ __('messages.promoter_revenue_note') }}</p>

@if (count($promoterTargets) > 1)
{{-- Where every row's copied link leads. It changes the links, never the figures, which count
     every page of the schedule. --}}
<div class="page-filters mb-3">
    <div class="is-grow">
        <label for="promoter-target" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.promoter_links_go_to') }}</label>
        <select id="promoter-target" aria-describedby="promoter-target-help" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
            @foreach ($promoterTargets as $target)
            <option value="{{ $target['url'] }}">{{ $target['label'] }}</option>
            @endforeach
        </select>
        <p class="event-hint mt-1" id="promoter-target-help">{{ __('messages.promoter_links_go_to_hint') }}</p>
    </div>
</div>
@endif

<div class="ap-card rounded-xl overflow-hidden">
    <table role="table" class="page-table">
        <thead role="rowgroup">
            <tr role="row">
                <th scope="col" role="columnheader">{{ __('messages.name') }}</th>
                <th scope="col" role="columnheader">{{ __('messages.promoter_link') }}</th>
                <th scope="col" role="columnheader" class="c-num">{{ __('messages.promoter_clicks') }}</th>
                <th scope="col" role="columnheader" class="c-num">{{ __('messages.promoter_tickets') }}</th>
                <th scope="col" role="columnheader" class="c-num">{{ __('messages.promoter_revenue') }}</th>
                <th scope="col" role="columnheader" class="c-num">{{ __('messages.promoter_commission_earned') }}</th>
                <th scope="col" role="columnheader"><span class="sr-only">{{ __('messages.actions') }}</span></th>
            </tr>
        </thead>
        <tbody role="rowgroup">
            @foreach ($promoterLinks as $link)
            @php
                $linkHash = \App\Utils\UrlUtils::encodeId($link->id);
                $linkStats = $promoterStats[$link->id] ?? ['tickets' => 0, 'revenue' => [], 'commission' => []];
                $editing = $promoterForm === $linkHash;
            @endphp
            <tr role="row">
                <td role="cell" class="c-main">
                    <div class="event-list-name">
                        <span dir="auto" v-pre>{{ $link->name }}</span>
                        @if ($link->isArchived())
                        <span class="event-chip">{{ __('messages.promoter_archived') }}</span>
                        @endif
                    </div>
                    <div class="event-list-sub">
                        <span dir="ltr" v-pre>{{ $link->code }}</span>@if ($link->commission_percent) · {{ __('messages.promoter_commission_rate', ['rate' => rtrim(rtrim(number_format($link->commission_percent, 2, '.', ''), '0'), '.')]) }}@endif
                    </div>
                </td>
                <td role="cell" data-label="{{ __('messages.promoter_link') }}">
                    @if ($link->isArchived())
                    <span class="event-hint">{{ __('messages.promoter_archived') }}</span>
                    @else
                    {{-- The address scrolled to its end, where the part that differs per promoter is,
                         and a quiet tool beside it: a brand button on every row outshouted the page's
                         one action. The field stays selectable where the clipboard is unavailable. --}}
                    <div class="flex items-center gap-2" data-promoter-code="{{ $link->code }}">
                        <input type="text" readonly dir="ltr" id="promoter-link-{{ $linkHash }}" value="{{ $withVia($scheduleUrl, $link->code) }}"
                            aria-label="{{ __('messages.promoter_link_for', ['name' => $link->name]) }}"
                            class="min-w-0 w-48 flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <button type="button" class="page-tool" data-promoter-copy="promoter-link-{{ $linkHash }}"
                            aria-label="{{ __('messages.promoter_copy_for', ['name' => $link->name]) }}">
                            <span data-promoter-copy-label aria-live="polite">{{ __('messages.promoter_copy') }}</span>
                        </button>
                    </div>
                    @endif
                </td>
                <td role="cell" class="c-num" data-label="{{ __('messages.promoter_clicks') }}">{{ number_format($link->clicks) }}</td>
                <td role="cell" class="c-num" data-label="{{ __('messages.promoter_tickets') }}">{{ number_format($linkStats['tickets']) }}</td>
                <td role="cell" class="c-num" data-label="{{ __('messages.promoter_revenue') }}"><bdi dir="ltr">{{ $moneyList($linkStats['revenue']) }}</bdi></td>
                <td role="cell" class="c-num" data-label="{{ __('messages.promoter_commission_earned') }}">
                    @if ($link->commission_percent)
                    <bdi dir="ltr">{{ $moneyList($linkStats['commission']) }}</bdi>
                    @else
                    <span class="event-hint">{{ __('messages.promoter_no_rate') }}</span>
                    @endif
                </td>
                <td role="cell" class="c-actions">
                    @if ($promotersAllowed)
                    <button type="button" class="event-link" id="promoter-edit-open-{{ $linkHash }}" data-promoter-edit="{{ $linkHash }}"
                        aria-expanded="{{ $editing ? 'true' : 'false' }}" aria-controls="promoter-edit-{{ $linkHash }}"
                        aria-label="{{ __('messages.promoter_edit_for', ['name' => $link->name]) }}">{{ __('messages.edit') }}</button>
                    @endif
                    @if ($link->isArchived())
                        @if ($promotersAllowed)
                        <form method="POST" action="{{ route('role.promoters.restore', ['subdomain' => $role->subdomain, 'hash' => $linkHash]) }}">
                            @csrf
                            <button type="submit" class="event-link" aria-label="{{ __('messages.promoter_restore_for', ['name' => $link->name]) }}">{{ __('messages.restore') }}</button>
                        </form>
                        @endif
                    @else
                    <form method="POST" action="{{ route('role.promoters.archive', ['subdomain' => $role->subdomain, 'hash' => $linkHash]) }}" data-confirm="{{ __('messages.promoter_archive_confirm') }}">
                        @csrf
                        <button type="submit" class="event-link is-danger" aria-label="{{ __('messages.promoter_archive_for', ['name' => $link->name]) }}">{{ __('messages.promoter_archive') }}</button>
                    </form>
                    @endif
                </td>
            </tr>
            @if ($promotersAllowed)
            <tr role="row" id="promoter-edit-{{ $linkHash }}" @unless ($editing) hidden @endunless>
                <td role="cell" colspan="7">
                    <form method="POST" action="{{ route('role.promoters.update', ['subdomain' => $role->subdomain, 'hash' => $linkHash]) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="promoter_form" value="{{ $linkHash }}">
                        <div class="page-form-fields">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <x-input-label :for="'promoter-name-'.$linkHash" :value="__('messages.name') . ' *'" />
                                    <x-text-input :id="'promoter-name-'.$linkHash" name="name" type="text" class="mt-1 block w-full" maxlength="100" required
                                        :value="$editing ? old('name') : $link->name" />
                                    @if ($editing)<x-input-error class="mt-2" :messages="$errors->get('name')" />@endif
                                </div>
                                <div>
                                    <x-input-label :for="'promoter-code-'.$linkHash" :value="__('messages.promoter_code')" />
                                    @if ($link->codeLocked())
                                    <x-text-input :id="'promoter-code-'.$linkHash" type="text" class="mt-1 block w-full" dir="ltr" :value="$link->code" disabled />
                                    <p class="event-hint mt-1">{{ __('messages.promoter_code_locked_hint') }}</p>
                                    @else
                                    <x-text-input :id="'promoter-code-'.$linkHash" name="code" type="text" class="mt-1 block w-full" maxlength="40" dir="ltr" pattern="[A-Za-z0-9_\-]+"
                                        :value="$editing ? old('code') : $link->code" />
                                    @if ($editing)<x-input-error class="mt-2" :messages="$errors->get('code')" />@endif
                                    @endif
                                </div>
                                <div>
                                    <x-input-label :for="'promoter-commission-'.$linkHash" :value="__('messages.promoter_commission')" />
                                    <x-text-input :id="'promoter-commission-'.$linkHash" name="commission_percent" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full"
                                        :value="$editing ? old('commission_percent') : $link->commission_percent" />
                                    @if ($editing)<x-input-error class="mt-2" :messages="$errors->get('commission_percent')" />@endif
                                </div>
                            </div>
                        </div>
                        <div class="page-form-actions">
                            <button type="button" class="event-link" data-promoter-edit="{{ $linkHash }}">{{ __('messages.cancel') }}</button>
                            <x-brand-button type="submit" size="sm">{{ __('messages.save') }}</x-brand-button>
                        </div>
                    </form>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif

<script {!! nonce_attr() !!}>
(function () {
    function showEnd(input) { input.scrollLeft = input.scrollWidth; }
    document.querySelectorAll('[data-promoter-code] input').forEach(showEnd);

    // Where the copied links lead: every row's address follows the one choice above the list.
    var target = document.getElementById('promoter-target');
    if (target) {
        target.addEventListener('change', function () {
            var base = target.value;
            document.querySelectorAll('[data-promoter-code]').forEach(function (wrap) {
                var input = wrap.querySelector('input');
                if (input) {
                    input.value = base + (base.indexOf('?') === -1 ? '?' : '&') + 'via=' + encodeURIComponent(wrap.getAttribute('data-promoter-code'));
                    showEnd(input);
                }
            });
        });
    }

    document.addEventListener('click', function (e) {
        var el = e.target && e.target.closest ? e.target : null;
        if (! el) { return; }

        // Copy: the field is selected first, so where the clipboard is not available (an http://
        // selfhost) the link is ready to copy by hand.
        var copy = el.closest('[data-promoter-copy]');
        if (copy) {
            var input = document.getElementById(copy.getAttribute('data-promoter-copy'));
            var label = copy.querySelector('[data-promoter-copy-label]');
            if (! input || ! label) { return; }
            input.select();
            if (! navigator.clipboard) { return; }
            navigator.clipboard.writeText(input.value).then(function () {
                // Back to the label the page printed, not to whatever it says now: a second press
                // within the two seconds would otherwise keep "Copied!" for good.
                label.textContent = @json(__('messages.copied'), JSON_UNESCAPED_UNICODE);
                clearTimeout(label._promoterReset);
                label._promoterReset = setTimeout(function () { label.textContent = @json(__('messages.promoter_copy'), JSON_UNESCAPED_UNICODE); }, 2000);
            }).catch(function () {});
            return;
        }

        // The add form, from the button in the head.
        var addOpen = el.closest('[data-promoter-add-open]');
        var addClose = el.closest('[data-promoter-add-close]');
        if (addOpen || addClose) {
            var form = document.getElementById('promoter-add');
            var opener = document.querySelector('[data-promoter-add-open]');
            if (! form) { return; }
            var open = addOpen ? form.hasAttribute('hidden') : false;
            form.toggleAttribute('hidden', ! open);
            if (opener) { opener.setAttribute('aria-expanded', open ? 'true' : 'false'); }
            if (open) {
                var name = document.getElementById('promoter-name');
                if (name) { name.focus(); }
            } else if (opener) {
                opener.focus();
            }
            return;
        }

        // Edit opens the row's form beneath it; Cancel, or Edit again, closes it and goes back to Edit.
        var button = el.closest('[data-promoter-edit]');
        if (! button) { return; }
        var hash = button.getAttribute('data-promoter-edit');
        var row = document.getElementById('promoter-edit-' + hash);
        var editButton = document.getElementById('promoter-edit-open-' + hash);
        if (! row) { return; }
        var opening = row.hasAttribute('hidden');
        row.toggleAttribute('hidden', ! opening);
        if (editButton) { editButton.setAttribute('aria-expanded', opening ? 'true' : 'false'); }
        if (opening) {
            var first = row.querySelector('input:not([type=hidden]):not([disabled])');
            if (first) { first.focus(); }
        } else if (editButton) {
            editButton.focus();
        }
    });
})();
</script>
@endif
