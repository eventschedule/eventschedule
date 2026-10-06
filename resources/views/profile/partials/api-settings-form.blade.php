<section>
    @include('profile.partials.heading', Route::has('marketing.docs.developer.api')
        ? ['asideUrl' => route('marketing.docs.developer.api'), 'asideLabel' => __('messages.view_api_documentation')]
        : [])

    @include('profile.partials.notice', ['noticeDemo' => true, 'noticeClass' => 'mt-4 mb-0'])

    @php
        $apiKeyExpires = auth()->user()->api_key ? auth()->user()->api_key_expires_at : null;
        $apiKeyExpired = $apiKeyExpires && $apiKeyExpires->isPast();
    @endphp
    {{-- A key past its date is refused by the API. The page showed it as it shows a live one. --}}
    @if ($apiKeyExpired)
        @include('profile.partials.notice', ['noticeText' => __('messages.settings_api_key_expired', ['date' => $apiKeyExpires->translatedFormat('M j, Y')]), 'noticeClass' => 'mt-4 mb-0'])
    @endif

    <form method="post" action="{{ route('api-settings.update') }}" id="api-settings-form" class="form-kit-fields mt-5 space-y-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}"
        data-disable-confirm="{{ auth()->user()->api_key ? __('messages.settings_api_disable_confirm') : '' }}">
        @csrf
        @method('patch')

        <div>
            <x-toggle name="enable_api" label="{{ __('messages.settings_enable_api') }}"
                checked="{{ auth()->user()->api_key ? true : false }}"
                help="{{ __('messages.settings_enable_api_help') }}" />
        </div>

        @if(auth()->user()->api_key)
            <div data-no-dirty>
                <label for="api_key" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                    {{ __('messages.settings_api_key') }}
                </label>
                <div class="mt-1 relative">
                    <input type="text" id="api_key"
                           value="{{ session('new_api_key') ? session('new_api_key') : str_repeat('•', 32) }}"
                           class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm {{ session('show_new_api_key') ? 'rounded-e-none pe-12' : '' }} font-mono"
                           readonly>
                    @if(session('show_new_api_key'))
                        <div class="absolute inset-y-0 end-0 flex items-center">
                            <div class="h-full w-px bg-gray-300 dark:bg-gray-600"></div>
                            <button type="button"
                                    id="copy-api-key-btn"
                                    class="px-3 border border-s-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-e-lg flex items-center justify-center group h-full"
                                    title="{{ __('messages.copy') }}" aria-label="{{ __('messages.copy') }}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5 text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                <span id="copy-feedback"
                                      class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 transition-opacity">
                                    {{ __('messages.copied') }}
                                </span>
                            </button>
                        </div>
                    @endif
                </div>
                @if(session('show_new_api_key'))
                    @include('profile.partials.notice', ['noticeText' => __('messages.settings_api_key_copy_now'), 'noticeClass' => 'mt-3 mb-0'])
                @endif
                @if ($apiKeyExpires && ! $apiKeyExpired)
                    <p class="event-hint" id="api-key-expires">{{ __('messages.expires') }}: {{ $apiKeyExpires->translatedFormat('M j, Y') }}</p>
                @endif
            </div>
        @endif

        @include('profile.partials.save', ['saveClass' => ''])
    </form>
</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // Switching API access off and saving deletes the key, and whatever uses it stops working.
    // The save asks first, through the layout's own data-confirm, only while the switch is off.
    var apiForm = document.getElementById('api-settings-form');
    var apiToggle = document.getElementById('enable_api');
    if (apiForm && apiToggle && apiForm.dataset.disableConfirm) {
        var syncApiConfirm = function() {
            if (apiToggle.checked) {
                apiForm.removeAttribute('data-confirm');
            } else {
                apiForm.setAttribute('data-confirm', apiForm.dataset.disableConfirm);
            }
        };
        apiToggle.addEventListener('change', syncApiConfirm);
        syncApiConfirm();
    }

    var copyBtn = document.getElementById('copy-api-key-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var apiKeyInput = document.getElementById('api_key');
            apiKeyInput.select();
            document.execCommand('copy');

            var feedback = document.getElementById('copy-feedback');
            feedback.classList.remove('opacity-0');

            setTimeout(function() {
                feedback.classList.add('opacity-0');
            }, 2000);
        });
    }
});
</script>
