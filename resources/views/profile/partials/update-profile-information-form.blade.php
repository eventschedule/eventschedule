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
            <x-input-label for="phone" :value="__('messages.phone_number')" />
            <x-phone-input name="phone" :value="old('phone', $user->phone)" :disabled="is_demo_mode()" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />

            @if (!is_demo_mode() && config('app.hosted'))
                @if ($user->phone && !$user->hasVerifiedPhone())
                <div id="phone-verify-section">
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('messages.your_phone_is_unverified') }}
                    </p>

                    @if (\App\Services\SmsService::isConfigured())
                    <div id="phone-verify-ui" class="mt-2">
                        <button type="button" id="phone-send-code-btn"
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800">
                            {{ __('messages.click_here_to_verify_phone') }}
                        </button>

                        <div id="phone-code-input" style="display: none;" class="mt-2 flex items-center gap-2">
                            <input type="text" id="phone-verification-code" maxlength="6" placeholder="000000"
                                class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm text-center tracking-widest" />
                            <button type="button" id="phone-verify-code-btn"
                                class="inline-flex items-center px-3 py-2 bg-[#4E81FA] text-white text-sm font-medium rounded-md hover:bg-[#3d6de8] transition-colors">
                                {{ __('messages.verify') }}
                            </button>
                        </div>

                        <p id="phone-verify-message" class="mt-2 text-sm" style="display: none;"></p>
                    </div>
                    @endif
                </div>
                @elseif ($user->phone && $user->hasVerifiedPhone())
                <p class="text-sm mt-2 text-green-600 dark:text-green-400">
                    {{ __('messages.phone_verified') }}
                </p>
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
                'ru' => 'russian',
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
                accept="image/png, image/jpeg" data-file-trigger="profile_image" data-filename-target="profile_image_filename" data-preview-target="profile_image_preview" />
            <div id="profile_image_choose" style="{{ $user->profile_image_url ? 'display:none' : '' }}">
                <div class="mt-1 flex items-center gap-3">
                    <button type="button" data-trigger-file-input="profile_image"
                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                        <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('messages.choose_file') }}
                    </button>
                    <span id="profile_image_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                <p id="profile_image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;"></p>
            </div>

            <div id="profile_image_preview_clear" class="relative inline-block pt-3" style="display: none;">
                <img id="profile_image_preview" src="#" alt="Profile Image Preview" style="max-height:120px;" class="rounded-md border border-gray-200 dark:border-gray-600" />
                <button type="button" data-clear-file-input="profile_image" data-clear-preview="profile_image_preview" data-clear-filename="profile_image_filename" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>

            @if ($user->profile_image_url)
            <div id="profile_image_existing" class="relative inline-block mt-4 pt-1" data-show-on-delete="profile_image_choose">
                <img src="{{ $user->profile_image_url }}" style="max-height:120px" class="rounded-md border border-gray-200 dark:border-gray-600" />
                <button type="button"
                    data-delete-image-url="{{ route('profile.delete_image') }}"
                    data-delete-image-token="{{ csrf_token() }}"
                    data-delete-image-parent="true"
                    style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                    class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
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
    function previewImage(input, previewId) {
        var preview = document.getElementById(previewId);
        var clearBtn = document.getElementById(previewId + '_clear');
        var warningElement = document.getElementById('profile_image_size_warning');

        if (!input || !input.files || !input.files[0]) {
            if (preview) preview.src = '';
            if (clearBtn) clearBtn.style.display = 'none';
            if (warningElement) {
                warningElement.textContent = '';
                warningElement.style.display = 'none';
            }
            return;
        }

        var file = input.files[0];
        var reader = new FileReader();

        reader.onloadend = function () {
            if (!reader.result) return;

            // Show preview immediately
            preview.src = reader.result;
            preview.style.display = '';
            if (clearBtn) clearBtn.style.display = 'inline-block';

            // Check dimensions/size asynchronously (for warnings only)
            var img = new Image();
            img.onload = function() {
                var width = this.width;
                var height = this.height;
                var fileSize = file.size / 1024 / 1024;
                var warningMessage = '';

                if (fileSize > 2.5) {
                    warningMessage += @json(__('messages.image_size_warning'), JSON_UNESCAPED_UNICODE);
                }

                if (width !== height) {
                    if (warningMessage) warningMessage += ' ';
                    warningMessage += @json(__('messages.image_not_square'), JSON_UNESCAPED_UNICODE);
                }

                if (warningElement) {
                    if (warningMessage) {
                        warningElement.textContent = warningMessage;
                        warningElement.style.display = 'block';
                    } else {
                        warningElement.textContent = '';
                        warningElement.style.display = 'none';
                    }
                }
            };
            img.src = reader.result;
        };

        reader.readAsDataURL(file);
    }

    function clearProfileFileInput(inputId, previewId, filenameId) {
        var input = document.getElementById(inputId);
        if (input) input.value = '';
        var preview = document.getElementById(previewId);
        var clearBtn = document.getElementById(previewId + '_clear');
        var filenameSpan = document.getElementById(filenameId);
        var warningElement = document.getElementById('profile_image_size_warning');
        if (preview) {
            preview.src = '';
            preview.style.display = 'none';
        }
        if (clearBtn) clearBtn.style.display = 'none';
        if (filenameSpan) filenameSpan.textContent = '';
        if (warningElement) {
            warningElement.textContent = '';
            warningElement.style.display = 'none';
        }
    }

    function deleteProfileImage(url, token, element) {
        if (!confirm(@json(__('messages.are_you_sure'), JSON_UNESCAPED_UNICODE))) {
            return;
        }

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        }).then(function(response) {
            if (response.ok) {
                if (element) {
                    var showTarget = element.dataset.showOnDelete;
                    element.remove();
                    if (showTarget) {
                        var target = document.getElementById(showTarget);
                        if (target) target.style.display = '';
                    }
                } else {
                    location.reload();
                }
            } else {
                alert(@json(__('messages.failed_to_delete_image'), JSON_UNESCAPED_UNICODE));
            }
        });
    }

    // Delegated click handler
    document.addEventListener('click', function(e) {
        // Trigger file input buttons
        var triggerBtn = e.target.closest('[data-trigger-file-input]');
        if (triggerBtn) {
            var fileInput = document.getElementById(triggerBtn.dataset.triggerFileInput);
            if (fileInput) fileInput.click();
            return;
        }

        // Clear file input buttons
        var clearBtn = e.target.closest('[data-clear-file-input]');
        if (clearBtn) {
            clearProfileFileInput(clearBtn.dataset.clearFileInput, clearBtn.dataset.clearPreview, clearBtn.dataset.clearFilename);
            return;
        }

        // Delete image buttons
        var deleteBtn = e.target.closest('[data-delete-image-url]');
        if (deleteBtn) {
            deleteProfileImage(deleteBtn.dataset.deleteImageUrl, deleteBtn.dataset.deleteImageToken, deleteBtn.parentElement);
            return;
        }

        // Alert buttons for demo mode
        var alertBtn = e.target.closest('[data-alert]');
        if (alertBtn) {
            alert(alertBtn.dataset.alert);
            return;
        }
    });

    // Phone verification
    var sendCodeBtn = document.getElementById('phone-send-code-btn');
    var verifyCodeBtn = document.getElementById('phone-verify-code-btn');

    if (sendCodeBtn) {
        sendCodeBtn.addEventListener('click', function() {
            sendCodeBtn.disabled = true;
            sendCodeBtn.textContent = '...';

            fetch('{{ route("phone.send_code") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ phone: document.getElementById('phone_hidden').value })
            }).then(function(r) { return r.json(); }).then(function(data) {
                var msgEl = document.getElementById('phone-verify-message');
                if (data.success) {
                    document.getElementById('phone-code-input').style.display = '';
                    sendCodeBtn.style.display = 'none';
                    msgEl.textContent = data.message;
                    msgEl.className = 'mt-2 text-sm text-green-600 dark:text-green-400';
                    msgEl.style.display = '';
                } else {
                    msgEl.textContent = data.message;
                    msgEl.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
                    msgEl.style.display = '';
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.textContent = @json(__('messages.click_here_to_verify_phone'), JSON_UNESCAPED_UNICODE);
                }
            }).catch(function() {
                sendCodeBtn.disabled = false;
                sendCodeBtn.textContent = @json(__('messages.click_here_to_verify_phone'), JSON_UNESCAPED_UNICODE);
            });
        });
    }

    if (verifyCodeBtn) {
        verifyCodeBtn.addEventListener('click', function() {
            var code = document.getElementById('phone-verification-code').value;
            verifyCodeBtn.disabled = true;

            fetch('{{ route("phone.verify_code") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ code: code })
            }).then(function(r) { return r.json(); }).then(function(data) {
                var msgEl = document.getElementById('phone-verify-message');
                if (data.success) {
                    msgEl.textContent = data.message;
                    msgEl.className = 'mt-2 text-sm text-green-600 dark:text-green-400';
                    msgEl.style.display = '';
                    document.getElementById('phone-code-input').style.display = 'none';
                    document.getElementById('phone-verify-ui').style.display = 'none';
                    var section = document.getElementById('phone-verify-section');
                    if (section) section.innerHTML = '<p class="text-sm mt-2 text-green-600 dark:text-green-400">' + data.message + '</p>';
                } else {
                    msgEl.textContent = data.message;
                    msgEl.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
                    msgEl.style.display = '';
                    verifyCodeBtn.disabled = false;
                }
            }).catch(function() {
                verifyCodeBtn.disabled = false;
            });
        });
    }

    // File input change handler (delegated)
    document.addEventListener('change', function(e) {
        var el = e.target.closest('[data-file-trigger]');
        if (!el) return;

        var filenameTarget = el.dataset.filenameTarget;
        if (filenameTarget) {
            var filenameEl = document.getElementById(filenameTarget);
            if (filenameEl) {
                filenameEl.textContent = el.files[0] ? el.files[0].name : '';
            }
        }

        var previewTarget = el.dataset.previewTarget;
        if (previewTarget) {
            previewImage(el, previewTarget);
        }
    });
});
</script>