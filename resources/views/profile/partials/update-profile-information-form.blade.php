<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.profile_information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.update_your_accounts_profile_information_and_email_address') }}
        </p>
    </header>

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('messages.name') . ' *'" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" :disabled="is_demo_mode()" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('messages.email') . ' *'" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email', $user->email)" required autocomplete="username" :disabled="is_demo_mode()" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (!is_demo_mode())
                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('messages.your_email_address_is_unverified') }}

                        <button form="send-verification"
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800">
                            {{ __('messages.click_here_to_re_send_the_verification_email') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ __('messages.new_verification_link_has_been_sent') }}
                    </p>
                    @endif
                </div>
                @endif
            @endif
        </div>

        <div>
            <x-input-label for="timezone" :value="__('messages.timezone')" />
            <select name="timezone" id="timezone" required {{ is_demo_mode() ? 'disabled' : '' }}
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                @foreach(\Carbon\CarbonTimeZone::listIdentifiers() as $timezone)
                <option value="{{ $timezone }}" {{ $user->timezone == $timezone ? 'SELECTED' : '' }}>{{ $timezone }}
                </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
        </div>

        <div>
            <x-input-label for="language_code" :value="__('messages.language')" />
            <select name="language_code" id="language_code" required {{ is_demo_mode() ? 'disabled' : '' }}
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                @foreach([
                'ar' => 'arabic',
                'en' => 'english',
                'nl' => 'dutch',
                'fr' => 'french',
                'de' => 'german',
                'he' => 'hebrew',
                'it' => 'italian',
                'pt' => 'portuguese',
                'es' => 'spanish',
                'et' => 'estonian',
                ] as $key => $value)
                <option value="{{ $key }}" {{ $user->language_code == $key ? 'SELECTED' : '' }}>{{ __('messages.' . $value) }}
                </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('language_code')" />
        </div>

        <div>
            <x-checkbox name="use_24_hour_time" label="{{ __('messages.use_24_hour_time_format') }}"
                checked="{{ old('use_24_hour_time', $user->use_24_hour_time) }}"
                data-custom-attribute="value" />
        </div>

        <div>
            <x-input-label :value="__('messages.square_profile_image')" />
            <input id="profile_image" name="profile_image" type="file" class="hidden"
                accept="image/png, image/jpeg" />
            <div class="mt-1 flex items-center gap-3">
                <button type="button" id="profile_image_choose"
                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ __('messages.choose_file') }}
                </button>
                <span id="profile_image_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                <button type="button" id="profile_image_clear"
                    class="text-gray-400 hover:text-red-500 p-1" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />

            @if ($user->profile_image_url)
            <img src="{{ $user->profile_image_url }}" style="max-height:120px" class="pt-3" />
            @endif
        </div>

        <div class="flex items-center gap-4">
            @if (is_demo_mode())
                <button type="button"
                    data-alert="{{ __('messages.saving_disabled_demo_mode') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                    {{ __('messages.save') }}
                </button>
            @else
                <x-primary-button>{{ __('messages.save') }}</x-primary-button>
            @endif

            @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.saved') }}</p>
            @endif
        </div>
    </form>
</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // Profile image file input change handler
    var profileImageInput = document.getElementById('profile_image');
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function() {
            document.getElementById('profile_image_filename').textContent = this.files[0]?.name || '';
            document.getElementById('profile_image_clear').style.display = this.files[0] ? 'inline' : 'none';
        });
    }

    // Choose file button
    var chooseBtn = document.getElementById('profile_image_choose');
    if (chooseBtn) {
        chooseBtn.addEventListener('click', function() {
            document.getElementById('profile_image').click();
        });
    }

    // Clear file button
    var clearBtn = document.getElementById('profile_image_clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            document.getElementById('profile_image').value = '';
            document.getElementById('profile_image_filename').textContent = '';
            this.style.display = 'none';
        });
    }

    // Alert buttons for demo mode
    document.querySelectorAll('[data-alert]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            alert(this.dataset.alert);
        });
    });
});
</script>