<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.webhooks') }}
        </h2>

        @if(Route::has('marketing.docs.developer.webhooks'))
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                <x-link href="{{ route('marketing.docs.developer.webhooks') }}" target="_blank">
                    {{ __('messages.view_webhook_documentation') }}
                </x-link>
            </p>
        @endif
    </header>

    @php
        $hasPro = auth()->user()->roles()->get()->contains(fn($role) => $role->isPro());
        $webhooks = auth()->user()->webhooks()->orderByDesc('created_at')->get();
    @endphp

    @if (! $hasPro)
        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded text-blue-800 dark:text-blue-200 text-sm">
            {{ __('messages.webhooks_require_pro') }}
        </div>
    @endif

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif

    {{-- Secret display after creation --}}
    @if (session('show_new_webhook_secret'))
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
            <p class="text-sm font-medium text-green-800 dark:text-green-200 mb-2">{{ __('messages.webhook_secret_label') }}</p>
            <div class="flex items-center gap-2">
                <input type="text" id="webhook_secret" value="{{ session('new_webhook_secret') }}" class="flex-1 font-mono text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" readonly>
                <button type="button" id="copy-webhook-secret-btn" class="px-3 py-2 border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md flex items-center justify-center" title="{{ __('Copy to clipboard') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                </button>
            </div>
            <p class="mt-2 text-xs text-green-700 dark:text-green-300">{{ __('messages.webhook_secret_warning') }}</p>
        </div>
    @endif

    {{-- Existing webhooks --}}
    @if ($webhooks->isNotEmpty())
        <div class="mt-6 space-y-4">
            @foreach ($webhooks as $webhook)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ ! $webhook->is_active ? 'opacity-60' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            @if ($webhook->description)
                                <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $webhook->description }}</p>
                            @endif
                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate font-mono" title="{{ $webhook->url }}">{{ $webhook->url }}</p>
                            <div class="flex flex-wrap gap-1 mt-2">
                                @if (empty($webhook->event_types))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">{{ __('messages.all_events') }}</span>
                                @else
                                    @foreach ($webhook->event_types as $type)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $type }}</span>
                                    @endforeach
                                @endif
                            </div>
                            @if ($webhook->last_triggered_at)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ __('messages.last_triggered') }}: {{ $webhook->last_triggered_at->diffForHumans() }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            {{-- Toggle active --}}
                            <form method="POST" action="{{ route('webhooks.toggle', \App\Utils\UrlUtils::encodeId($webhook->id)) }}">
                                @csrf
                                <button type="submit" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ $webhook->is_active ? __('messages.disable') : __('messages.enable') }}">
                                    @if ($webhook->is_active)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    @endif
                                </button>
                            </form>
                            {{-- Test ping --}}
                            <form method="POST" action="{{ route('webhooks.test', \App\Utils\UrlUtils::encodeId($webhook->id)) }}">
                                @csrf
                                <button type="submit" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('messages.webhook_test') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </button>
                            </form>
                            {{-- Edit (toggle form visibility) --}}
                            <button type="button" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 webhook-edit-btn" data-webhook-id="{{ \App\Utils\UrlUtils::encodeId($webhook->id) }}" title="{{ __('messages.edit') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('webhooks.destroy', \App\Utils\UrlUtils::encodeId($webhook->id)) }}" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('messages.delete') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Edit form (hidden by default) --}}
                    <div id="webhook-edit-{{ \App\Utils\UrlUtils::encodeId($webhook->id) }}" class="hidden mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <form method="POST" action="{{ route('webhooks.update', \App\Utils\UrlUtils::encodeId($webhook->id)) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.webhook_url') }}</label>
                                <input type="url" name="url" value="{{ $webhook->url }}" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.description') }}</label>
                                <input type="text" name="description" value="{{ $webhook->description }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" maxlength="255" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.webhook_events') }}</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach (\App\Models\Webhook::EVENT_TYPES as $type)
                                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <input type="checkbox" name="event_types[]" value="{{ $type }}" {{ empty($webhook->event_types) || in_array($type, $webhook->event_types) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[#4E81FA]">
                                            {{ $type }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('webhooks.regenerate_secret', \App\Utils\UrlUtils::encodeId($webhook->id)) }}" class="mt-2" onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
                            @csrf
                            <button type="submit" class="text-sm text-gray-600 dark:text-gray-400 underline hover:text-gray-900 dark:hover:text-gray-200">{{ __('messages.regenerate_secret') }}</button>
                        </form>
                    </div>

                    {{-- Delivery log (loaded on demand) --}}
                    <div class="mt-3">
                        <button type="button" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 underline webhook-deliveries-btn" data-webhook-id="{{ \App\Utils\UrlUtils::encodeId($webhook->id) }}">{{ __('messages.webhook_deliveries') }}</button>
                        <div id="webhook-deliveries-{{ \App\Utils\UrlUtils::encodeId($webhook->id) }}" class="hidden mt-2">
                            <div class="text-xs text-gray-400">{{ __('messages.loading') }}...</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add new webhook form --}}
    <form method="POST" action="{{ route('webhooks.store') }}" class="mt-6 space-y-4 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
        @csrf

        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.add_webhook') }}</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.webhook_url') }}</label>
            <input type="url" name="url" value="{{ old('url') }}" required placeholder="https://example.com/webhook" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
            @error('url')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.description') }}</label>
            <input type="text" name="description" value="{{ old('description') }}" placeholder="{{ __('messages.webhook_description_placeholder') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" maxlength="255" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.webhook_events') }}</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach (\App\Models\Webhook::EVENT_TYPES as $type)
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="event_types[]" value="{{ $type }}" checked class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[#4E81FA]">
                        {{ $type }}
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <x-primary-button>{{ __('messages.add_webhook') }}</x-primary-button>
        </div>
    </form>
</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
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
            copySecretBtn.title = '{{ __("Copied!") }}';
            setTimeout(function() {
                copySecretBtn.title = '{{ __("Copy to clipboard") }}';
            }, 2000);
        });
    }

    // Toggle edit forms
    document.querySelectorAll('.webhook-edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-webhook-id');
            var form = document.getElementById('webhook-edit-' + id);
            form.classList.toggle('hidden');
        });
    });

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
