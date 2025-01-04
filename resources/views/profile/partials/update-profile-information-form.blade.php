<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.profile_information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.update_your_accounts_profile_information_and_email_address') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('messages.name') . ' *'" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('messages.email') . ' *'" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

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
        </div>

        <div>
            <x-input-label for="timezone" :value="__('messages.timezone')" />
            <select name="timezone" id="timezone" required
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                @foreach(\Carbon\CarbonTimeZone::listIdentifiers() as $timezone)
                <option value="{{ $timezone }}" {{ $user->timezone == $timezone ? 'SELECTED' : '' }}>{{ $timezone }}
                </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
        </div>

        <div>
            <x-input-label for="language_code" :value="__('messages.language')" />
            <select name="language_code" id="language_code" required
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
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
                ] as $key => $value)
                <option value="{{ $key }}" {{ $user->language_code == $key ? 'SELECTED' : '' }}>{{ __('messages.' . $value) }}
                </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('language_code')" />
        </div>

        <!--
        <div>
            <x-input-label for="profile_image" :value="__('messages.square_profile_image')" />
            <input id="profile_image" name="profile_image" type="file" class="mt-1 block w-full text-gray-900 dark:text-gray-100"
                :value="old('profile_image')" accept="image/png, image/jpeg" />
            <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />

            @if ($user->profile_image_url)
            <img src="{{ $user->profile_image_url }}" style="max-height:120px" class="pt-3" />
            @endif
        </div>
        -->

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('messages.save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.saved') }}</p>
            @endif
        </div>
    </form>
</section>