<section>
    @include('profile.partials.heading', Route::has('marketing.docs.developer.webhooks')
        ? ['asideUrl' => route('marketing.docs.developer.webhooks'), 'asideLabel' => __('messages.view_webhook_documentation')]
        : [])

    @php
        // $settingsWebhooks and $settingsHasPro are read in profile/edit, where the sidebar needs
        // them too. Which form a refused save came from: both post a field named "url", so each
        // carries its own name and only that one shows the message and what was typed.
        $webhookFormBack = old('_form');
        $webhookAddOpen = $settingsWebhooks->isEmpty() || $webhookFormBack === 'add' || ($webhookFormBack === null && $errors->has('url'));
    @endphp

    <div class="mt-4">
    @if (! $settingsHasPro)
        @include('profile.partials.notice', ['noticeText' => __('messages.webhooks_require_pro')])
    @endif

    @include('profile.partials.notice', ['noticeDemo' => true])

    {{-- Secret display after creation --}}
    @if (session('show_new_webhook_secret'))
        <div class="mb-5 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg" data-no-dirty>
            <label for="webhook_secret" class="block text-sm font-medium text-green-800 dark:text-green-200 mb-2">{{ __('messages.webhook_secret_label') }}</label>
            <div class="flex items-center gap-2">
                <input type="text" id="webhook_secret" value="{{ session('new_webhook_secret') }}" class="flex-1 min-w-0 font-mono text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm" readonly>
                <button type="button" id="copy-webhook-secret-btn" class="px-3 py-2 border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg flex items-center justify-center" title="{{ __('messages.copy') }}" aria-label="{{ __('messages.copy') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                </button>
            </div>
            <p class="mt-2 text-xs text-green-700 dark:text-green-300">{{ __('messages.webhook_secret_warning') }}</p>
        </div>
    @endif
    </div>

    {{-- One line per webhook: where it sends, what it sends, and its four actions as words. --}}
    @if ($settingsWebhooks->isEmpty())
        <p class="event-empty">{{ __('messages.settings_no_webhooks') }}</p>
    @else
        <div class="event-list">
            @foreach ($settingsWebhooks as $webhook)
                @php
                    $webhookId = \App\Utils\UrlUtils::encodeId($webhook->id);
                    $webhookEditBack = $webhookFormBack === 'edit-'.$webhookId;
                    $webhookTypes = $webhook->event_types ?: [];
                    $webhookSends = empty($webhookTypes)
                        ? __('messages.all_events')
                        : implode(', ', array_slice($webhookTypes, 0, 3)).(count($webhookTypes) > 3 ? ' +'.(count($webhookTypes) - 3) : '');
                    $webhookChecked = $webhookEditBack ? (array) old('event_types', []) : $webhookTypes;
                @endphp
                <div class="event-list-row settings-row-wrap">
                    <div class="settings-row-main">
                        <div class="event-list-name">
                            <span>{{ $webhook->description ?: (parse_url((string) $webhook->url, PHP_URL_HOST) ?: $webhook->url) }}</span>
                            @if ($webhook->is_active)
                            <span class="event-status is-on ms-2">{{ __('messages.enabled') }}</span>
                            @else
                            <span class="event-chip">{{ __('messages.disabled') }}</span>
                            @endif
                        </div>
                        <div class="event-list-sub settings-mono truncate" dir="ltr" title="{{ $webhook->url }}">{{ $webhook->url }}</div>
                        <div class="event-list-sub">
                            {{ $webhookSends }}@if ($webhook->last_triggered_at) &middot; {{ __('messages.last_triggered') }}: {{ $webhook->last_triggered_at->diffForHumans() }}@endif
                        </div>
                    </div>
                    <div class="event-list-actions">
                        <form method="POST" action="{{ route('webhooks.toggle', $webhookId) }}">
                            @csrf
                            <button type="submit" class="event-link">{{ $webhook->is_active ? __('messages.disable') : __('messages.enable') }}</button>
                        </form>
                        <form method="POST" action="{{ route('webhooks.test', $webhookId) }}">
                            @csrf
                            <button type="submit" class="event-link">{{ __('messages.webhook_test') }}</button>
                        </form>
                        <button type="button" class="event-link webhook-edit-btn" data-reveal="webhook-edit-{{ $webhookId }}" aria-expanded="{{ $webhookEditBack ? 'true' : 'false' }}">{{ __('messages.edit') }}</button>
                    </div>
                </div>

                {{-- Edit form, behind "Edit". It comes back open, with what was typed and the
                     message, when its own save was refused: that used to appear under the Add
                     form's field with this one closed. --}}
                <div id="webhook-edit-{{ $webhookId }}" class="event-add-box mb-4" @unless ($webhookEditBack) hidden @endunless>
                    <form method="POST" action="{{ route('webhooks.update', $webhookId) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit-{{ $webhookId }}">
                        <div>
                            <x-input-label for="webhook_url_{{ $webhookId }}" :value="__('messages.webhook_url')" />
                            <x-text-input id="webhook_url_{{ $webhookId }}" type="url" name="url" dir="ltr" :value="$webhookEditBack ? old('url') : $webhook->url" required class="mt-1 block w-full" />
                            @if ($webhookEditBack)
                            <x-input-error class="mt-2" :messages="$errors->get('url')" />
                            @endif
                        </div>
                        <div>
                            <x-input-label for="webhook_description_{{ $webhookId }}" :value="__('messages.description')" />
                            <x-text-input id="webhook_description_{{ $webhookId }}" type="text" name="description" :value="$webhookEditBack ? old('description') : $webhook->description" class="mt-1 block w-full" maxlength="255" />
                        </div>
                        <div>
                            <p class="event-group-label">{{ __('messages.webhook_events') }}</p>
                            @php $webhookAllTicked = count(array_intersect(\App\Models\Webhook::EVENT_TYPES, ($webhookEditBack ? $webhookChecked : ($webhookTypes ?: \App\Models\Webhook::EVENT_TYPES)))) === count(\App\Models\Webhook::EVENT_TYPES); @endphp
                            <x-toggle name="webhook_all_events" id="webhook_all_events_{{ $webhookId }}" label="{{ __('messages.all_events') }}" :checked="$webhookAllTicked" data-webhook-all-events />
                            {{-- None ticked is stored as "all events", which nothing on the page said. --}}
                            <p class="event-hint mt-3" data-webhook-events @if ($webhookAllTicked) hidden @endif>{{ __('messages.settings_webhook_no_events_help') }}</p>
                            <div class="event-check-grid" data-webhook-events @if ($webhookAllTicked) hidden @endif>
                                @foreach (\App\Models\Webhook::EVENT_TYPES as $type)
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="event_types[]" value="{{ $type }}"
                                            @checked(($webhookEditBack ? false : empty($webhookTypes)) || in_array($type, $webhookChecked, true))
                                            class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <span class="settings-mono">{{ $type }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @include('profile.partials.save', ['saveClass' => ''])
                    </form>
                    <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
                        <form method="POST" action="{{ route('webhooks.regenerate_secret', $webhookId) }}" data-confirm="{{ __('messages.are_you_sure') }}">
                            @csrf
                            <button type="submit" class="event-link event-link-quiet">{{ __('messages.regenerate_secret') }}</button>
                        </form>
                        <button type="button" class="event-link event-link-quiet webhook-deliveries-btn" data-webhook-id="{{ $webhookId }}">{{ __('messages.webhook_deliveries') }}</button>
                        {{-- Delete sat on the row as an equal of Edit; it is the last of the rare
                             actions here, and still asks first. --}}
                        <form method="POST" action="{{ route('webhooks.destroy', $webhookId) }}" data-confirm="{{ __('messages.are_you_sure') }}" class="ms-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
                        </form>
                    </div>
                    <div id="webhook-deliveries-{{ $webhookId }}" class="hidden mt-2">
                        <div class="text-xs text-gray-400">{{ __('messages.loading') }}...</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add a webhook: open when there are none yet (it is the only thing to do), otherwise
         behind a link, as the lists of the event form are. --}}
    @unless ($webhookAddOpen)
    <div class="mt-3">
        <button type="button" class="event-link" data-reveal="webhook-add-form" aria-expanded="false">+ {{ __('messages.add_webhook') }}</button>
    </div>
    @endunless
    <form id="webhook-add-form" method="POST" action="{{ route('webhooks.store') }}" class="event-add-box space-y-4 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}" @unless ($webhookAddOpen) hidden @endunless>
        @csrf
        <input type="hidden" name="_form" value="add">

        <p class="event-group-label">{{ __('messages.add_webhook') }}</p>

        <div>
            <x-input-label for="webhook_url_new" :value="__('messages.webhook_url') . ' *'" />
            <x-text-input id="webhook_url_new" type="url" name="url" dir="ltr" :value="$webhookFormBack === 'add' ? old('url') : ''" required placeholder="https://example.com/webhook" class="mt-1 block w-full" />
            @if ($webhookFormBack === 'add' || $webhookFormBack === null)
            <x-input-error class="mt-2" :messages="$errors->get('url')" />
            @endif
        </div>

        <div>
            <x-input-label for="webhook_description_new" :value="__('messages.description')" />
            <x-text-input id="webhook_description_new" type="text" name="description" :value="$webhookFormBack === 'add' ? old('description') : ''" placeholder="{{ __('messages.webhook_description_placeholder') }}" class="mt-1 block w-full" maxlength="255" />
        </div>

        <div>
            <p class="event-group-label">{{ __('messages.webhook_events') }}</p>
            {{-- One switch for the usual case; the list of fourteen is for sending fewer. --}}
            @php $webhookNewAll = $webhookFormBack !== 'add' || count((array) old('event_types', [])) === count(\App\Models\Webhook::EVENT_TYPES) || old('event_types') === null; @endphp
            <x-toggle name="webhook_all_events" id="webhook_all_events_new" label="{{ __('messages.all_events') }}" :checked="$webhookNewAll" data-webhook-all-events />
            <p class="event-hint mt-3" data-webhook-events @if ($webhookNewAll) hidden @endif>{{ __('messages.settings_webhook_no_events_help') }}</p>
            <div class="event-check-grid" data-webhook-events @if ($webhookNewAll) hidden @endif>
                @foreach (\App\Models\Webhook::EVENT_TYPES as $type)
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="event_types[]" value="{{ $type }}"
                            @checked($webhookFormBack === 'add' ? in_array($type, (array) old('event_types', []), true) : true)
                            class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <span class="settings-mono">{{ $type }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @include('profile.partials.save', ['saveClass' => '', 'saveLabel' => __('messages.add_webhook')])
    </form>
</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // "All events" is the usual choice, so it is one switch. On, every box is ticked and the list
    // is put away; off, the list is there to untick from. What is posted is the boxes, as before.
    document.querySelectorAll('input[type="checkbox"][data-webhook-all-events]').forEach(function(all) {
        var form = all.closest('form');
        if (! form) {
            return;
        }
        all.addEventListener('change', function() {
            if (all.checked) {
                form.querySelectorAll('input[name="event_types[]"]').forEach(function(box) { box.checked = true; });
            }
            form.querySelectorAll('[data-webhook-events]').forEach(function(part) { part.hidden = all.checked; });
        });
    });

    // Copy webhook secret
    var copySecretBtn = document.getElementById('copy-webhook-secret-btn');
    if (copySecretBtn) {
        copySecretBtn.addEventListener('click', function() {
            var input = document.getElementById('webhook_secret');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(input.value);
            } else {
                input.select();
                document.execCommand('copy');
            }
            copySecretBtn.title = @json(__('messages.copied'), JSON_UNESCAPED_UNICODE);
            setTimeout(function() {
                copySecretBtn.title = @json(__('messages.copy'), JSON_UNESCAPED_UNICODE);
            }, 2000);
        });
    }

    // Load delivery logs on demand
    document.querySelectorAll('.webhook-deliveries-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-webhook-id');
            var container = document.getElementById('webhook-deliveries-' + id);
            container.classList.toggle('hidden');

            if (container.dataset.loaded) return;
            container.dataset.loaded = '1';

            fetch('{{ url("/webhooks") }}/' + id + '/deliveries', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(deliveries) {
                container.textContent = '';
                if (deliveries.length === 0) {
                    var emptyMsg = document.createElement('p');
                    emptyMsg.className = 'text-xs text-gray-400';
                    emptyMsg.textContent = '{{ __("messages.no_deliveries") }}';
                    container.appendChild(emptyMsg);
                    return;
                }
                var wrapper = document.createElement('div');
                wrapper.className = 'space-y-1';
                deliveries.forEach(function(d) {
                    var row = document.createElement('div');
                    row.className = 'flex items-center justify-between text-xs py-1 border-b border-gray-100 dark:border-gray-700';

                    var eventSpan = document.createElement('span');
                    eventSpan.className = 'text-gray-500 dark:text-gray-400';
                    eventSpan.textContent = d.event_type;

                    var statusSpan = document.createElement('span');
                    statusSpan.className = (d.success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400') + ' font-mono';
                    statusSpan.textContent = d.response_status ? d.response_status : 'timeout';

                    var durationSpan = document.createElement('span');
                    durationSpan.className = 'text-gray-400';
                    durationSpan.textContent = d.duration_ms ? d.duration_ms + 'ms' : '-';

                    var dateSpan = document.createElement('span');
                    dateSpan.className = 'text-gray-400';
                    dateSpan.textContent = new Date(d.created_at).toLocaleString();

                    row.appendChild(eventSpan);
                    row.appendChild(statusSpan);
                    row.appendChild(durationSpan);
                    row.appendChild(dateSpan);
                    wrapper.appendChild(row);
                });
                container.appendChild(wrapper);
            })
            .catch(function() {
                container.textContent = '';
                var errorMsg = document.createElement('p');
                errorMsg.className = 'text-xs text-red-500';
                errorMsg.textContent = '{{ __("messages.error_loading") }}';
                container.appendChild(errorMsg);
            });
        });
    });
});
</script>
