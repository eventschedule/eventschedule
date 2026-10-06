<section>
    @include('profile.partials.heading')
    <p class="form-kit-lead">{{ __('messages.update_your_accounts_profile_information_and_email_address') }}</p>

    @include('profile.partials.notice', ['noticeDemo' => true])

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('patch')

        {{-- Always on the page: these used to be the General tab. A link that points at one of
             them (the unsubscribe page, the boost form's "add a phone") needs no tab opened. --}}
        <div id="profile-tab-general" class="form-kit-fields space-y-6">
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

            </div>

            <div id="phone-field">
                <x-input-label for="phone" :value="__('messages.phone_number')" />
                <x-phone-input name="phone" :value="old('phone', $user->phone)" :disabled="is_demo_mode()" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />

                @if (!is_demo_mode() && config('app.hosted'))
                    @if ($user->phone && !$user->hasVerifiedPhone())
                    <div id="phone-verify-section" class="mt-3" data-no-dirty>
                        @include('profile.partials.notice', ['noticeText' => __('messages.your_phone_is_unverified'), 'noticeClass' => 'mb-0'])

                        @if (\App\Services\SmsService::isConfigured())
                        <div id="phone-verify-ui" class="mt-2">
                            <button type="button" id="phone-send-code-btn" class="event-link">
                                {{ __('messages.click_here_to_verify_phone') }}
                            </button>

                            <div id="phone-code-input" style="display: none;" class="mt-2 flex items-center gap-2">
                                <input type="text" id="phone-verification-code" maxlength="6" placeholder="000000" inputmode="numeric" autocomplete="one-time-code"
                                    aria-label="{{ __('messages.verify') }}"
                                    class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-center tracking-widest" />
                                <x-brand-button size="sm" id="phone-verify-code-btn">{{ __('messages.verify') }}</x-brand-button>
                            </div>

                            <p id="phone-verify-message" class="mt-2 text-sm" style="display: none;" role="status"></p>
                        </div>
                        @endif
                    </div>
                    @endif
                @endif
            </div>

            @if (isset($editorRoles) && $editorRoles->count() > 1)
            <div>
                <x-input-label for="default_role_id" :value="__('messages.default_schedule')" />
                <select name="default_role_id" id="default_role_id" {{ is_demo_mode() ? 'disabled' : '' }}
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                    <option value="">{{ __('messages.none') }}</option>
                    @foreach ($editorRoles as $editorRole)
                    <option value="{{ $editorRole->id }}" {{ old('default_role_id', $user->default_role_id) == $editorRole->id ? 'selected' : '' }}>
                        {{ $editorRole->name }} ({{ $editorRole->subdomain }})
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.default_schedule_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('default_role_id')" />
            </div>
            @endif

            <div>
                <x-input-label :value="__('messages.square_profile_image')" />
                <input id="profile_image" name="profile_image" type="file" class="hidden"
                    accept="image/png, image/jpeg" data-file-trigger="profile_image" data-filename-target="profile_image_filename" data-preview-target="profile_image_preview" />
                <div id="profile_image_choose" style="{{ $user->profile_image_url ? 'display:none' : '' }}">
                    <div class="mt-1 flex items-center gap-3">
                        <button type="button" data-trigger-file-input="profile_image"
                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                            <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ __('messages.choose_file') }}
                        </button>
                        <span id="profile_image_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                    @include('profile.partials.notice', ['noticeId' => 'profile_image_size_warning', 'noticeClass' => 'mt-3 mb-0', 'noticeHidden' => true, 'noticeText' => ''])
                </div>

                <div id="profile_image_preview_clear" class="relative inline-block pt-3" style="display: none;">
                    <img id="profile_image_preview" src="#" alt="{{ __('messages.square_profile_image') }}" style="max-height:120px;" class="rounded-lg border border-gray-200 dark:border-gray-600" />
                    <button type="button" data-clear-file-input="profile_image" data-clear-preview="profile_image_preview" data-clear-filename="profile_image_filename" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute top-1 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center" aria-label="{{ __('messages.remove') }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                @if ($user->profile_image_url)
                <div id="profile_image_existing" class="relative inline-block mt-4 pt-1" data-show-on-delete="profile_image_choose">
                    <img src="{{ $user->profile_image_url }}" alt="{{ __('messages.square_profile_image') }}" style="max-height:120px" class="rounded-lg border border-gray-200 dark:border-gray-600" />
                    <button type="button"
                        data-delete-image-url="{{ route('profile.delete_image') }}"
                        data-delete-image-token="{{ csrf_token() }}"
                        data-delete-image-parent="true"
                        aria-label="{{ __('messages.remove') }}"
                        style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                        class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                {{-- A delete that failed says so here; it used to raise an alert(). --}}
                <p id="profile_image_error" class="mt-2 text-sm text-red-600 dark:text-red-400" role="alert" hidden></p>
                @endif
            </div>

            {{-- The three switches about what the app sends you and asks you, together under one
                 heading and after the fields that say who you are. Two of them used to sit between
                 Email and Phone, and the third in Localization, which it has nothing to do with. --}}
            <div id="profile-preferences" class="space-y-6">
                <p class="event-group-label">{{ __('messages.settings_preferences') }}</p>
            {{-- users.is_subscribed: the account-wide opt-out the signed unsubscribe link in our
                 emails sets. Without this there was no way back once somebody clicked it. Always
                 on the page, never inside a row, because the unsubscribe page's "turn emails back
                 on" link lands here. --}}
            <div>
                <x-toggle name="is_subscribed" label="{{ __('messages.email_updates') }}"
                    help="{{ __('messages.email_updates_help') }}"
                    checked="{{ old('is_subscribed', $user->is_subscribed) }}" />
            </div>

            {{-- users.suggestions_off_at: the dashboard's "Turn off suggestions", and the way
                 back on. Beside "Email updates" because it also stops the reminder emails that
                 ask the same things. Only for somebody who edits a schedule: nobody else is
                 ever suggested anything. The wrapper carries the id (the toggle component puts
                 its attributes on a hidden checkbox) for the page's highlight script. --}}
            @if (isset($editorRoles) && $editorRoles->isNotEmpty())
            <div id="suggestions-field">
                <x-toggle name="show_suggestions" label="{{ __('messages.suggestions_toggle') }}"
                    help="{{ __('messages.suggestions_toggle_help') }}"
                    checked="{{ old('show_suggestions', $user->wantsSuggestions()) }}" />
            </div>
            @endif

                <div>
                    <x-toggle name="ask_before_following" label="{{ __('messages.ask_before_following') }}"
                        help="{{ __('messages.settings_ask_before_following_help') }}"
                        checked="{{ old('ask_before_following', ! $user->follow_consent_dismissed) }}" />
                </div>
            </div>
        </div>

        {{-- Rows that open in place, where three more tabs used to be. Each says what it holds, so
             the language or the time format can be read without opening anything. --}}
        <div class="event-subrows">
            <x-form-row group="profile" tab="localization" class="profile-tab" :title="__('messages.localization')" :summary="$settingsLocalization" />
            <div id="profile-tab-localization" class="event-subrow-body" hidden>
                <div class="form-kit-fields space-y-6">
                    <div>
                        <x-input-label for="timezone" :value="__('messages.timezone')" />
                        <select name="timezone" id="timezone" required {{ is_demo_mode() ? 'disabled' : '' }} data-searchable
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            <x-timezone-options :selected="old('timezone', $user->timezone)" />
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                    </div>

                    <div>
                        <x-input-label for="language_code" :value="__('messages.language')" />
                        {{-- old() first: a save refused over another field used to bring this back
                             on the stored language, and the next save kept that. $settingsLanguage
                             is the stored one, or the language the page is in when the stored one
                             is not in the list: with nothing marked the browser offers the first
                             of the list, and a save about something else would store it. --}}
                        <select name="language_code" id="language_code" required {{ is_demo_mode() ? 'disabled' : '' }}
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            @foreach(config('app.supported_languages') as $key => $value)
                            <option value="{{ $key }}" {{ old('language_code', $settingsLanguage) == $key ? 'selected' : '' }}>{{ __('messages.' . $value) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('language_code')" />
                    </div>

                    <div>
                        <x-toggle name="use_24_hour_time" label="{{ __('messages.use_24_hour_time_format') }}"
                            checked="{{ old('use_24_hour_time', $user->use_24_hour_time) }}" />
                    </div>

                </div>
            </div>

            {{-- Appearance holds no named inputs: the theme is stored in localStorage, per device,
                 so nothing here belongs in the profile POST, and its summary is written by the
                 script below from the picker itself. The picker is the same component the sidebar
                 footer's theme popover renders; markup, styling and its driver ship with it. --}}
            <x-form-row group="profile" tab="appearance" class="profile-tab" :title="__('messages.appearance')" />
            <div id="profile-tab-appearance" class="event-subrow-body" hidden data-no-dirty>
                <div class="max-w-md">
                    <x-theme-picker tone="surface" headings="full" />
                </div>
            </div>

            {{-- Offered only while the accessibility widget is hidden on this device (the script
                 below reads that from localStorage), since all it holds is the way to bring it back. --}}
            <x-form-row group="profile" tab="accessibility" id="profile-tab-nav-accessibility" class="profile-tab" hidden
                :title="__('accessibility.footer_link')" :summary="__('accessibility.settings_widget_hidden_note')" />
            <div id="profile-tab-accessibility" class="event-subrow-body" hidden data-no-dirty>
                <x-brand-button size="sm" id="accessibility-widget-show">{{ __('accessibility.settings_show_widget') }}</x-brand-button>
            </div>
        </div>

        @include('profile.partials.save', ['saveRowId' => 'profile-save-row', 'saveShown' => session('status') === 'profile-updated'])
    </form>
</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // The warning about a picked image (too large, not square) is the page's amber panel.
    function setImageWarning(text) {
        var panel = document.getElementById('profile_image_size_warning');
        if (! panel) {
            return;
        }
        panel.querySelector('[data-notice-text]').textContent = text || '';
        panel.hidden = ! text;
    }

    function previewImage(input, previewId) {
        var preview = document.getElementById(previewId);
        var clearBtn = document.getElementById(previewId + '_clear');

        if (!input || !input.files || !input.files[0]) {
            if (preview) preview.src = '';
            if (clearBtn) clearBtn.style.display = 'none';
            setImageWarning('');
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

                setImageWarning(warningMessage);
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
        if (preview) {
            preview.src = '';
            preview.style.display = 'none';
        }
        if (clearBtn) clearBtn.style.display = 'none';
        if (filenameSpan) filenameSpan.textContent = '';
        setImageWarning('');
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
                showImageError();
            }
        }).catch(showImageError);
    }

    function showImageError() {
        var error = document.getElementById('profile_image_error');
        if (error) {
            error.textContent = @json(__('messages.failed_to_delete_image'), JSON_UNESCAPED_UNICODE);
            error.hidden = false;
        }
    }

    // Delegated click handler
    document.addEventListener('click', function(e) {
        // Trigger file input buttons
        var triggerBtn = e.target.closest('[data-trigger-file-input]');
        if (triggerBtn) {
            var fileInput = document.getElementById(triggerBtn.dataset.triggerFileInput);
            if (fileInput) {
                fileInput.value = null;
                fileInput.click();
            }
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
    });

    // Phone verification
    var sendCodeBtn = document.getElementById('phone-send-code-btn');
    var verifyCodeBtn = document.getElementById('phone-verify-code-btn');

    if (sendCodeBtn) {
        var sendCodeLabel = @json(__('messages.click_here_to_verify_phone'), JSON_UNESCAPED_UNICODE);
        var showPhoneMessage = function(text, good) {
            var msgEl = document.getElementById('phone-verify-message');
            msgEl.textContent = text;
            msgEl.className = good ? 'mt-2 event-status is-on' : 'mt-2 text-sm text-red-600 dark:text-red-400';
            msgEl.style.display = '';
        };

        sendCodeBtn.addEventListener('click', function() {
            // The code goes to the number that is SAVED. With a different one typed in the field
            // the server refused, and its answer read "code invalid" before any code was sent.
            var savedPhone = @json((string) $user->phone);
            var typedPhone = document.getElementById('phone_hidden') ? document.getElementById('phone_hidden').value : savedPhone;
            if (typedPhone && typedPhone !== savedPhone) {
                showPhoneMessage(@json(__('messages.settings_save_phone_first'), JSON_UNESCAPED_UNICODE), false);
                return;
            }

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
                if (data.success) {
                    document.getElementById('phone-code-input').style.display = '';
                    sendCodeBtn.style.display = 'none';
                    showPhoneMessage(data.message, true);
                    var codeField = document.getElementById('phone-verification-code');
                    if (codeField) {
                        codeField.focus();
                    }
                } else {
                    showPhoneMessage(data.message, false);
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.textContent = sendCodeLabel;
                }
            }).catch(function() {
                // A request that never got an answer said nothing at all.
                showPhoneMessage(@json(__('messages.an_error_occurred'), JSON_UNESCAPED_UNICODE), false);
                sendCodeBtn.disabled = false;
                sendCodeBtn.textContent = sendCodeLabel;
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
                    msgEl.className = 'mt-2 event-status is-on';
                    msgEl.style.display = '';
                    document.getElementById('phone-code-input').style.display = 'none';
                    document.getElementById('phone-verify-ui').style.display = 'none';
                    var section = document.getElementById('phone-verify-section');
                    if (section) {
                        var p = document.createElement('p');
                        p.className = 'event-status is-on';
                        p.textContent = data.message;
                        section.innerHTML = '';
                        section.appendChild(p);
                    }
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

    // The rows under the fields. Localization says what the form holds as it is edited; Appearance
    // reads the picker, whose choice lives in this browser and is not part of the form.
    FormKit.summary('profile:localization', function() {
        return FormKit.join([
            FormKit.chosen('#language_code'),
            FormKit.value('#timezone'),
            FormKit.on('use_24_hour_time') ? @json(__('messages.settings_24_hour'), JSON_UNESCAPED_UNICODE) : @json(__('messages.settings_12_hour'), JSON_UNESCAPED_UNICODE)
        ]);
    });
    FormKit.summary('profile:appearance', function() {
        var pane = document.getElementById('profile-tab-appearance');
        var labels = [];
        pane.querySelectorAll('.js-theme-mode-btn[aria-checked="true"] .tp-label, .js-theme-variant-btn[aria-checked="true"] .tp-label').forEach(function(label) {
            labels.push(label.textContent.trim());
        });
        return FormKit.join(labels);
    });
    FormKit.refresh();
    document.getElementById('profile-tab-appearance').addEventListener('click', function() {
        setTimeout(FormKit.refresh, 0);
    });
});
</script>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    var row = document.getElementById('profile-tab-nav-accessibility');
    var btn = document.getElementById('accessibility-widget-show');
    var widgetHidden = false;
    try {
        widgetHidden = localStorage.getItem('es_a11y_hide_widget') === '1';
    } catch (e) {}

    if (widgetHidden && row) {
        row.hidden = false;
    }

    if (btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            try {
                localStorage.removeItem('es_a11y_hide_widget');
            } catch (err) {}
            window.location.reload();
        });
    }
});
</script>
