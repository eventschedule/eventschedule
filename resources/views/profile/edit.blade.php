<x-app-admin-layout>

    <x-slot name="head">
        <style {!! nonce_attr() !!}>
        /* Hide all sections except the first one by default */
        .section-content {
            display: none;
        }
        .section-content:first-of-type {
            display: block;
        }

        /* Mobile accordion styles */
        .mobile-section-header.active .accordion-chevron {
            transform: rotate(180deg);
        }
        .mobile-section-header.active {
            color: var(--brand-blue);
            border-color: var(--brand-blue);
        }

        /* What is connected, on one line: its name and state, then its one action. */
        .settings-picked-main {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.25rem 0.75rem;
            min-width: 0;
        }
        .settings-picked-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.9375rem;
            font-weight: 600;
            color: rgb(var(--ap-ink));
        }
        .settings-picked-actions {
            display: flex;
            flex: none;
            align-items: center;
            gap: 0.875rem;
        }
        /* A form that is one text action sits on the line of the actions beside it. */
        .settings-picked-actions > form {
            display: flex;
            margin: 0;
        }
        /* The link beside a heading keeps its own small icon: the heading sizes its own. */
        .form-kit-title .settings-title-aside {
            flex: none;
            font-size: 0.875rem;
            font-weight: 400;
        }
        .form-kit-title .settings-title-aside svg {
            width: 0.75rem;
            height: 0.75rem;
        }
        .settings-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8125rem;
        }
        .event-list-row > .settings-row-main {
            flex: 1;
            min-width: 0;
        }
        /* A section's Save: the last thing in it, at the end of its own line, the size the save
           bar's is on the other two forms. Each section is its own form, so there is no one bar. */
        .settings-save {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-top: 1px solid rgb(var(--ap-border));
            padding-top: 1rem;
        }
        /* A row that is not set up yet describes itself on its line. Opened, the pane under it says
           the same thing in full, so the line steps aside (it still holds its place). */
        .section-content .event-subrow[aria-expanded="true"] .event-row-summary.is-empty {
            visibility: hidden;
        }
        .settings-save-status {
            min-width: 0;
        }
        .settings-save .settings-save-button {
            flex: none;
            min-width: 7.5rem;
        }
        /* A red button on this page is the size of the Save buttons around it (the kit already
           takes the shared component's capitals and wide tracking off inside a section). */
        .section-content button.settings-danger {
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        /* A tab that holds several sections: each is a block under its own title, and the next
           begins after a rule. An address that names a block moves the page to it. */
        .settings-block {
            scroll-margin-top: 1.5rem;
        }
        .settings-block + .settings-block {
            margin-top: 2.25rem;
            padding-top: 2.25rem;
            border-top: 1px solid rgb(var(--ap-border));
        }
        /* On a phone the kit drops a section's title, because the accordion header above it says
           the same. A block's title is not said anywhere else: the header names the tab. */
        @media (max-width: 1023px) {
            .section-content .settings-block .form-kit-title.settings-block-title {
                display: flex;
            }
            .section-content .settings-block .form-kit-title.settings-block-title > span:first-child {
                display: inline-flex;
            }
        }
        @media (max-width: 639px) {
            .event-list-row.settings-row-wrap {
                flex-wrap: wrap;
            }
            .event-list-row.settings-row-wrap .event-list-actions {
                width: 100%;
            }
        }
        </style>
    </x-slot>

    @php
        // One list gives the sidebar, the phone headers and the headings their names and icons, so
        // each is written once. The sections themselves are written out below with their ids in
        // plain sight: EventFormStructureTest reads them from this file to check that every link
        // the app builds to a section lands on one.
        $settingsSections = [
            'section-profile' => [
                'label' => __('messages.profile_information'),
                'icon' => ['M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
            ],
            'section-payment-methods' => [
                'label' => __('messages.payment_methods'),
                'icon' => ['M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z'],
            ],
            'section-password' => [
                'label' => __('messages.password'),
                'heading' => $user->hasPassword() ? __('messages.update_password') : __('messages.set_password'),
                'icon' => ['M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z'],
            ],
            'section-two-factor' => [
                // 'label' is the name in the list of sections and on the phone header: short, so
                // none wraps to two lines and none repeats the word "Settings" on the Settings
                // page. 'heading' is the section's own title, where it is fuller.
                'label' => __('messages.settings_two_factor_short'),
                'heading' => __('messages.two_factor_authentication'),
                'icon' => ['M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
            ],
            'section-google-calendar' => [
                'label' => 'Google',
                'heading' => __('messages.google_settings'),
                'icon' => ['M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
            ],
            'section-microsoft-calendar' => [
                'label' => 'Outlook',
                'heading' => __('messages.microsoft_settings'),
                'icon' => ['M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
            ],
            'section-facebook' => [
                'label' => 'Facebook',
                'heading' => __('messages.facebook_settings'),
                'icon' => ['M14.25 8.25h-1.5a1.5 1.5 0 0 0-1.5 1.5v11.25M9 13.5h6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ],
            'section-api' => [
                'label' => 'API',
                'heading' => __('messages.api_settings'),
                'icon' => ['M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5'],
            ],
            'section-webhooks' => [
                'label' => __('messages.webhooks'),
                'icon' => ['M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
            ],
            'section-backup' => [
                'label' => __('messages.backup_and_restore'),
                'icon' => ['M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125'],
            ],
            'section-app' => [
                'label' => __('messages.app_update'),
                'icon' => ['M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ],
            'section-data' => [
                'label' => __('messages.data_export_title'),
                'icon' => ['M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3'],
            ],
            'section-delete' => [
                'label' => __('messages.delete_account'),
                'icon' => ['M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0'],
            ],
        ];
        if (! facebook_login_enabled()) {
            unset($settingsSections['section-facebook']);
        }
        if (! can_self_update()) {
            unset($settingsSections['section-app']);
        }
        // Hidden from a selfhost admin only: ProfileController::destroy() refuses the last one.
        if (! (config('app.hosted') || config('app.is_testing') || ! auth()->user()->isAdmin())) {
            unset($settingsSections['section-delete']);
        }

        // What the sidebar and the phone headers list: tabs. A tab is one section, or the few that
        // belong together, one under the other. The page listed thirteen sections under five
        // headings; four of those headings (Security, Integrations, Developers, Data) are entries
        // now, and there are six of them.
        // A section inside a tab keeps its id, so every address the app builds to one
        // ("#section-two-factor", and there are some fifty) still opens it: the tab is shown and
        // the page moves to the section.
        // 'blocks' is what the page renders in each tab (the markup below asks it), so this list
        // and the page cannot drift apart.
        $settingsTabs = [
            'section-profile' => ['blocks' => ['section-profile']],
            'section-payment-methods' => ['blocks' => ['section-payment-methods']],
            'section-security' => [
                'label' => __('messages.settings_group_security'),
                'icon' => $settingsSections['section-two-factor']['icon'],
                'blocks' => ['section-password', 'section-two-factor'],
            ],
            'section-integrations' => [
                'label' => __('messages.integrations'),
                'icon' => ['M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z'],
                'blocks' => ['section-google-calendar', 'section-microsoft-calendar', 'section-facebook'],
            ],
            'section-developers' => [
                'label' => __('messages.settings_group_developers'),
                'icon' => $settingsSections['section-api']['icon'],
                'blocks' => ['section-api', 'section-webhooks'],
            ],
            'section-account-data' => [
                'label' => __('messages.data'),
                'icon' => $settingsSections['section-backup']['icon'],
                'blocks' => ['section-backup', 'section-data', 'section-delete'],
            ],
            'section-app' => ['blocks' => ['section-app']],
        ];
        foreach ($settingsTabs as $tabId => $tab) {
            $tab['blocks'] = array_values(array_filter($tab['blocks'], fn ($block) => isset($settingsSections[$block])));
            if (! $tab['blocks']) {
                unset($settingsTabs[$tabId]);

                continue;
            }
            // A tab of one section is that section: its name and its icon.
            $settingsTabs[$tabId] = $tab + [
                'label' => $settingsSections[$tab['blocks'][0]]['label'],
                'icon' => $settingsSections[$tab['blocks'][0]]['icon'],
            ];
        }

        $settingsJoin = fn (array $parts) => implode(' · ', array_values(array_filter($parts, fn ($part) => $part !== null && $part !== '')));

        // Payment methods: one state per gateway, read by the sidebar and by the rows of the section.
        $settingsGateways = payment_gateways()->withSettings();
        $paymentStates = [];
        foreach ($settingsGateways as $gatewayKey => $gateway) {
            // Not connected yet: the row says what the method is, which "Not connected" five
            // times over did not. A gateway added later says it with a settings_<key>_about
            // line; until it has one, its row says that it is not connected.
            $about = match (true) {
                $gatewayKey === 'stripe' => config('app.hosted') ? __('messages.stripe_help') : __('messages.settings_not_connected'),
                $gatewayKey === 'invoiceninja' => __('messages.invoiceninja_help'),
                $gatewayKey === 'payment_url' => __('messages.payment_url_help'),
                \Illuminate\Support\Facades\Lang::has('messages.settings_'.$gatewayKey.'_about') => __('messages.settings_'.$gatewayKey.'_about'),
                default => __('messages.settings_not_connected'),
            };
            $state = ['on' => false, 'warn' => false, 'text' => $about];
            if ($gatewayKey === 'stripe') {
                if (! config('app.hosted')) {
                    // Selfhost: Stripe is the install's own keys, set in its environment.
                    if (config('services.stripe_platform.secret')) {
                        $state = ['on' => true, 'warn' => false, 'text' => __('messages.connected')];
                    }
                } elseif ($user->stripe_completed_at) {
                    $state = ['on' => true, 'warn' => false, 'text' => $settingsJoin([__('messages.connected'), $user->stripe_company_name])];
                } elseif ($user->stripe_account_id) {
                    $state = ['on' => false, 'warn' => true, 'text' => __('messages.settings_setup_not_finished')];
                }
            } elseif ($gatewayKey === 'invoiceninja') {
                if ($user->invoiceninja_api_key) {
                    $state = ['on' => true, 'warn' => false, 'text' => $settingsJoin([__('messages.connected'), $user->invoiceninja_company_name])];
                }
            } elseif ($gatewayKey === 'payment_url') {
                if ($user->payment_url) {
                    $state = ['on' => true, 'warn' => false, 'text' => $settingsJoin([__('messages.connected'), $user->paymentUrlHost()])];
                }
            } elseif ($gateway->hasOwnCredentials($user)) {
                $state = ['on' => true, 'warn' => false, 'text' => __('messages.connected')];
            } elseif ($gateway->platformCredentials() !== []) {
                $state = ['on' => true, 'warn' => false, 'text' => __('messages.gateway_provided_by_install')];
            }
            $paymentStates[$gatewayKey] = $state;
        }
        $paymentNames = [];
        foreach ($settingsGateways as $gatewayKey => $gateway) {
            if ($paymentStates[$gatewayKey]['on']) {
                $paymentNames[] = $gateway->label(null);
            }
        }
        $paymentPending = collect($paymentStates)->contains(fn ($state) => $state['warn']);

        // Webhooks, read by the sidebar and by the section.
        $settingsWebhooks = $user->webhooks()->orderByDesc('created_at')->get();
        $settingsHasPro = $user->roles()->get()->contains(fn ($role) => $role->isPro());
        // The line names a webhook that is switched ON, and counts the others as webhooks, not as
        // hosts: a disabled one used to be the headline, and three on one host read as one.
        $webhookHost = fn ($webhook) => parse_url((string) $webhook->url, PHP_URL_HOST) ?: $webhook->url;
        $activeWebhooks = $settingsWebhooks->where('is_active', true)->values();

        // The stored language, or the one the page is in when the stored one is not offered, or
        // in demo mode, where the page follows the visitor's language and not the account's.
        $settingsLanguage = ! is_demo_mode() && array_key_exists((string) $user->language_code, config('app.supported_languages'))
            ? $user->language_code
            : app()->getLocale();
        $languageKey = config('app.supported_languages')[$settingsLanguage] ?? null;
        $settingsLocalization = $settingsJoin([
            $languageKey ? __('messages.'.$languageKey) : null,
            $user->timezone,
            $user->use_24_hour_time ? __('messages.settings_24_hour') : __('messages.settings_12_hour'),
        ]);

        // The line under each name: what is saved now, from what the page already knows.
        $filled = fn (string $text) => ['text' => $text, 'empty' => false];
        $quiet = fn (string $text) => ['text' => $text, 'empty' => true];
        $settingsSummaries = [
            // No "email not verified" here: the page is behind the verified middleware, so nobody
            // with an unverified address ever reaches it.
            'section-profile' => $filled($settingsJoin([$user->name, $user->email])),
            'section-payment-methods' => $paymentNames
                ? $filled(implode(', ', $paymentNames))
                : ($paymentPending ? $filled(__('messages.settings_setup_not_finished')) : $quiet(__('messages.settings_none_connected'))),
            // A key past its date is refused by the API, so it is not "Enabled".
            'section-api' => ! $user->api_key
                ? $quiet(__('messages.disabled'))
                : ($user->api_key_expires_at && $user->api_key_expires_at->isPast() ? $filled(__('messages.expired')) : $filled(__('messages.enabled'))),
            'section-webhooks' => $settingsWebhooks->isEmpty()
                ? $quiet(__('messages.none'))
                : ($activeWebhooks->isEmpty()
                    ? $quiet(__('messages.disabled'))
                    : $filled($webhookHost($activeWebhooks->first()).($settingsWebhooks->count() > 1 ? ' +'.($settingsWebhooks->count() - 1) : ''))),
            'section-google-calendar' => ($user->google_oauth_id || $user->google_token)
                ? $filled($settingsJoin([$user->google_oauth_id ? __('messages.settings_sign_in') : null, $user->google_token ? __('messages.calendar') : null]))
                : $quiet(__('messages.settings_not_connected')),
            'section-microsoft-calendar' => $user->microsoft_token ? $filled(__('messages.connected')) : $quiet(__('messages.settings_not_connected')),
            'section-facebook' => $user->facebook_id ? $filled(__('messages.connected')) : $quiet(__('messages.settings_not_connected')),
            // What the section is for, quietly, where it has no state to report: an empty line
            // made these three look unfinished beside the ones that say something.
            'section-backup' => ($activeExportJobId || $activeImportJobId)
                ? $filled(__('messages.settings_in_progress'))
                : $quiet(__('messages.backup_export').', '.__('messages.backup_import')),
            'section-app' => isset($version_installed)
                ? (($update_available ?? false)
                    ? $filled(__('messages.settings_update_available'))
                    : $quiet($settingsJoin([$version_installed, ($version_available ?? null) === null ? null : __('messages.settings_up_to_date')])))
                : $quiet(''),
            'section-password' => $user->hasPassword() ? $filled(__('messages.settings_password_set')) : $quiet(__('messages.settings_password_not_set')),
            'section-two-factor' => $user->two_factor_confirmed_at
                ? $filled(__('messages.enabled'))
                : ($user->two_factor_secret ? $filled(__('messages.settings_setup_not_finished')) : $quiet(__('messages.disabled'))),
            'section-data' => $quiet(__('messages.data_export_button')),
            'section-delete' => $quiet(__('messages.settings_cannot_be_undone')),
        ];

        // The line under a TAB's name. A tab of one section says what that section says. A tab of
        // several says the one or two things worth knowing before it is opened, by name: a count
        // would need a plural in 12 languages.
        $connectedNames = array_values(array_filter([
            ($user->google_oauth_id || $user->google_token) ? 'Google' : null,
            $user->microsoft_token ? 'Outlook' : null,
            isset($settingsSections['section-facebook']) && $user->facebook_id ? 'Facebook' : null,
        ]));
        $apiKeyExpired = $user->api_key && $user->api_key_expires_at && $user->api_key_expires_at->isPast();
        $developerParts = array_values(array_filter([
            $user->api_key ? $settingsSections['section-api']['label'].($apiKeyExpired ? ': '.__('messages.expired') : '') : null,
            $settingsSummaries['section-webhooks']['empty'] ? null : $settingsSummaries['section-webhooks']['text'],
        ]));
        $settingsTabSummaries = [
            // Two-factor is the one to see at a glance; the password only when there is none.
            'section-security' => [
                'text' => $settingsJoin([
                    $user->hasPassword() ? null : __('messages.password').': '.$settingsSummaries['section-password']['text'],
                    __('messages.settings_two_factor_short').': '.$settingsSummaries['section-two-factor']['text'],
                ]),
                'empty' => $user->hasPassword() && $settingsSummaries['section-two-factor']['empty'],
            ],
            'section-integrations' => $connectedNames
                ? $filled(implode(', ', $connectedNames))
                : $quiet(__('messages.settings_none_connected')),
            'section-developers' => $developerParts
                ? $filled($settingsJoin($developerParts))
                : $quiet($settingsSections['section-api']['label'].', '.$settingsSections['section-webhooks']['label']),
            // Nothing to report, so what it is for, quietly: the names of what is inside.
            'section-account-data' => ($activeExportJobId || $activeImportJobId)
                ? $filled(__('messages.settings_in_progress'))
                : $quiet(implode(', ', array_map(fn ($block) => $settingsSections[$block]['label'], $settingsTabs['section-account-data']['blocks'] ?? []))),
        ];
        foreach ($settingsTabs as $tabId => $tab) {
            $settingsTabSummaries[$tabId] = count($tab['blocks']) === 1 && $tab['blocks'][0] === $tabId
                ? $settingsSummaries[$tabId]
                : $settingsTabSummaries[$tabId];
        }
    @endphp

    <h2 class="pb-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ __('messages.settings') }}
    </h2>

    <div class="py-5">
        <div class="mx-auto lg:grid lg:grid-cols-12 lg:gap-6">
            <!-- Sidebar Navigation (hidden on small screens, visible on lg+) -->
            <div class="hidden lg:block lg:col-span-3">
                <div class="sticky top-6">
                    <nav class="space-y-1">
                        @foreach ($settingsTabs as $tabId => $tab)
                        <a href="#{{ $tabId }}" class="section-nav-link" data-section="{{ $tabId }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                                @foreach ($tab['icon'] as $iconPath)
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
                                @endforeach
                            </svg>
                            <span class="section-nav-text">
                                <span>{{ $tab['label'] }}</span>
                                <span class="section-nav-summary {{ $settingsTabSummaries[$tabId]['empty'] ? 'is-empty' : '' }}"><bdi>{{ $settingsTabSummaries[$tabId]['text'] }}</bdi></span>
                            </span>
                            <span class="section-nav-dot" data-dirty-dot="{{ $tabId }}" hidden></span>
                        </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-9 space-y-6 lg:space-y-0">
                @if (isset($settingsTabs['section-profile']))
                @include('profile.partials.section-header', ['settingsSection' => 'section-profile'])
                <div id="section-profile" class="section-content">
                    <div class="form-kit-col">
                        @include('profile.partials.update-profile-information-form', ['settingsSection' => 'section-profile'])
                    </div>
                </div>
                @endif

                @if (isset($settingsTabs['section-payment-methods']))
                @include('profile.partials.section-header', ['settingsSection' => 'section-payment-methods'])
                <div id="section-payment-methods" class="section-content lg:mt-0">
                    <div class="form-kit-col">
                        @include('profile.partials.update-payments-form', ['settingsSection' => 'section-payment-methods'])
                    </div>
                </div>
                @endif

                {{-- Security: the password, then two-factor. One tab; each section is under its own title
                     and keeps its id, so an address that names one still opens it (and moves to it). --}}
                @if (isset($settingsTabs['section-security']))
                @include('profile.partials.section-header', ['settingsSection' => 'section-security'])
                <div id="section-security" class="section-content lg:mt-0">
                    <div class="form-kit-col">
                        @if (in_array('section-password', $settingsTabs['section-security']['blocks'], true))
                        <div id="section-password" class="settings-block">
                            @include('profile.partials.update-password-form', ['settingsSection' => 'section-password', 'settingsBlock' => true])
                        </div>
                        @endif
                        @if (in_array('section-two-factor', $settingsTabs['section-security']['blocks'], true))
                        <div id="section-two-factor" class="settings-block">
                            @include('profile.partials.two-factor-form', ['settingsSection' => 'section-two-factor', 'settingsBlock' => true])
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Integrations: the accounts this one is connected to. --}}
                @if (isset($settingsTabs['section-integrations']))
                @include('profile.partials.section-header', ['settingsSection' => 'section-integrations'])
                <div id="section-integrations" class="section-content lg:mt-0">
                    <div class="form-kit-col">
                        @if (in_array('section-google-calendar', $settingsTabs['section-integrations']['blocks'], true))
                        <div id="section-google-calendar" class="settings-block">
                            @include('profile.partials.google-calendar-form', ['settingsSection' => 'section-google-calendar', 'settingsBlock' => true])
                        </div>
                        @endif
                        @if (in_array('section-microsoft-calendar', $settingsTabs['section-integrations']['blocks'], true))
                        <div id="section-microsoft-calendar" class="settings-block">
                            @include('profile.partials.microsoft-calendar-form', ['settingsSection' => 'section-microsoft-calendar', 'settingsBlock' => true])
                        </div>
                        @endif
                        @if (in_array('section-facebook', $settingsTabs['section-integrations']['blocks'], true))
                        <div id="section-facebook" class="settings-block">
                            @include('profile.partials.facebook-account-form', ['settingsSection' => 'section-facebook', 'settingsBlock' => true])
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Developers: the API key, then webhooks. --}}
                @if (isset($settingsTabs['section-developers']))
                @include('profile.partials.section-header', ['settingsSection' => 'section-developers'])
                <div id="section-developers" class="section-content lg:mt-0">
                    <div class="form-kit-col">
                        @if (in_array('section-api', $settingsTabs['section-developers']['blocks'], true))
                        <div id="section-api" class="settings-block">
                            @include('profile.partials.api-settings-form', ['settingsSection' => 'section-api', 'settingsBlock' => true])
                        </div>
                        @endif
                        @if (in_array('section-webhooks', $settingsTabs['section-developers']['blocks'], true))
                        <div id="section-webhooks" class="settings-block">
                            @include('profile.partials.webhook-settings-form', ['settingsSection' => 'section-webhooks', 'settingsBlock' => true])
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Data: a backup, a copy of what is held about you, and deleting the account, last. --}}
                @if (isset($settingsTabs['section-account-data']))
                @include('profile.partials.section-header', ['settingsSection' => 'section-account-data'])
                <div id="section-account-data" class="section-content lg:mt-0">
                    <div class="form-kit-col">
                        @if (in_array('section-backup', $settingsTabs['section-account-data']['blocks'], true))
                        <div id="section-backup" class="settings-block">
                            @include('profile.partials.backup-restore-form', ['settingsSection' => 'section-backup', 'settingsBlock' => true])
                        </div>
                        @endif
                        @if (in_array('section-data', $settingsTabs['section-account-data']['blocks'], true))
                        <div id="section-data" class="settings-block">
                            @include('profile.partials.data-export-form', ['settingsSection' => 'section-data', 'settingsBlock' => true])
                        </div>
                        @endif
                        @if (in_array('section-delete', $settingsTabs['section-account-data']['blocks'], true))
                        <div id="section-delete" class="settings-block">
                            @include('profile.partials.delete-user-form', ['settingsSection' => 'section-delete', 'settingsBlock' => true])
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if (isset($settingsTabs['section-app']))
                @include('profile.partials.section-header', ['settingsSection' => 'section-app'])
                <div id="section-app" class="section-content lg:mt-0">
                    <div class="form-kit-col">
                        @include('profile.partials.update-app-form', ['settingsSection' => 'section-app'])
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('partials.form-kit-script')

    <script {!! nonce_attr() !!}>
    var _scrollGuard = function() { window.scrollTo(0, 0); };
    window.addEventListener('scroll', _scrollGuard);
    window._settingsLoading = true;

    // Section navigation functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sectionLinks = document.querySelectorAll('.section-nav-link');
        const sections = document.querySelectorAll('.section-content');
        const mobileHeaders = document.querySelectorAll('.mobile-section-header');

        function stored(key) {
            try { return localStorage.getItem(key); } catch (e) { return null; }
        }

        // Function to sync mobile accordion headers
        function syncMobileHeaders(sectionId) {
            mobileHeaders.forEach(header => {
                if (header.getAttribute('data-section') === sectionId) {
                    header.classList.add('active');
                } else {
                    header.classList.remove('active');
                }
            });
        }

        // Function to show a specific section and hide others
        function showSection(sectionId, preventScroll = false) {
            sections.forEach(section => {
                if (section.id === sectionId) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });

            // Update active link
            sectionLinks.forEach(link => {
                if (link.getAttribute('data-section') === sectionId) {
                    link.classList.add('nav-active');
                } else {
                    link.classList.remove('nav-active');
                }
            });

            // Sync mobile accordion headers
            syncMobileHeaders(sectionId);

            // Save to localStorage for cross-page persistence
            try { localStorage.setItem('lastSettingsSection', sectionId); } catch (e) {}

            // Update URL hash
            if (history.replaceState) {
                history.replaceState(null, null, '#' + sectionId);
            } else {
                window.location.hash = sectionId;
            }

            // Prevent scroll if requested
            if (preventScroll) {
                window.scrollTo(0, 0);
            }
        }

        // The rows, the summaries and "open what holds this" all go through this one function.
        FormKit.showSection = showSection;

        function isSection(id) {
            const element = id ? document.getElementById(id) : null;

            return !! (element && element.classList.contains('section-content'));
        }

        // A tab can hold several sections, one under the other (.settings-block). One that an
        // address names, or that a refused save left a message in, is moved to once the page has
        // settled: the page holds itself at the top while it loads (the scroll guard below).
        // The first block of a tab is where the tab opens anyway.
        function blockOf(element) {
            const block = element && element.closest ? element.closest('.settings-block') : null;

            return block && block.previousElementSibling ? block : null;
        }
        function moveTo(block) {
            if (block) {
                window._settingsMoveTo = block;
                if (document.readyState === 'complete' && ! window._settingsLoading) {
                    block.scrollIntoView({ block: 'start' });
                }
            }
        }

        // Pressing the section you are on leaves its rows as they are: it used to press the first
        // tab inside it, which with rows would open one nobody asked for.
        sectionLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                showSection(this.getAttribute('data-section'));
            });
        });

        mobileHeaders.forEach(header => {
            header.addEventListener('click', function() {
                showSection(this.getAttribute('data-section'));
            });
        });

        // Rows start closed, so nothing remembers one: these two keys only misled the Help link.
        try {
            localStorage.removeItem('profileActiveTab');
            localStorage.removeItem('paymentActiveTab');
        } catch (e) {}

        // Which section to open: the one a refused save left a message in, then whatever the
        // address names (a section, a row, or a field inside one), then the last one visited.
        // A hash that names nothing here used to hide every section.
        function initializeSections() {
            // Read before a section is shown: showing one rewrites the address to the tab's own.
            const named = document.getElementById(window.location.hash.replace(/^#/, '') || ' ');
            if (FormKit.routeErrors().length) {
                moveTo(blockOf(document.querySelector('.section-content ul.text-red-600, .section-content ul.text-red-400, .section-content [data-field-error]:not(:empty)')));
                return;
            }
            if (FormKit.openFromHash(window.location.hash)) {
                window.scrollTo(0, 0);
                moveTo(blockOf(named));
                return;
            }
            // The last one visited. A name stored before the sections were gathered into tabs
            // names a section inside one now: its tab is the one to show.
            const saved = document.getElementById(stored('lastSettingsSection') || ' ');
            const savedTab = saved ? saved.closest('.section-content') : null;
            if (savedTab) {
                showSection(savedTab.id, true);
            } else if (sections[0]) {
                showSection(sections[0].id, true);
            }
        }

        window.addEventListener('hashchange', function() {
            const named = document.getElementById(window.location.hash.replace(/^#/, '') || ' ');
            if (FormKit.openFromHash(window.location.hash)) {
                moveTo(blockOf(named));
            }
        });

        initializeSections();

        const urlParams = new URLSearchParams(window.location.search);

        // ?tab= opens that row of the profile (the unsubscribe page links to ?tab=general, whose
        // fields are always on the page, so it needs nothing).
        const requestedTab = urlParams.get('tab');
        if (requestedTab && document.getElementById('profile-tab-' + requestedTab) && ! FormKit.errorSections().length) {
            showSection('section-profile', true);
            FormKit.openRow('profile', requestedTab);
        }

        // A form inside a row reloads the page when it saves. The row it was in is opened again
        // once, so the result of connecting or unlinking is where the person was looking.
        const REOPEN = 'settingsOpenRow';
        document.addEventListener('submit', function(event) {
            // Not a submit that was called off: "Unlink account" answered with Cancel used to be
            // remembered, and the row opened by itself on the next visit.
            if (event.defaultPrevented) {
                return;
            }
            const pane = event.target.closest ? event.target.closest('.event-subrow-body') : null;
            const row = pane && pane.id ? document.querySelector('button[data-row-group][aria-controls="' + pane.id + '"]') : null;
            if (row) {
                try { sessionStorage.setItem(REOPEN, row.getAttribute('data-row-group') + ':' + row.getAttribute('data-tab')); } catch (e) {}
            }
        });
        try {
            const reopen = sessionStorage.getItem(REOPEN);
            sessionStorage.removeItem(REOPEN);
            if (reopen && ! FormKit.errorSections().length) {
                const parts = reopen.split(':');
                const row = document.querySelector('button[data-row-group="' + parts[0] + '"][data-tab="' + parts[1] + '"]');
                const section = row ? row.closest('.section-content') : null;
                if (section && section.style.display === 'block') {
                    FormKit.openRow(parts[0], parts[1]);
                }
            }
        } catch (e) {}

        // A link or button that shows one more thing in place: [data-reveal="id"].
        document.addEventListener('click', function(event) {
            const trigger = event.target.closest ? event.target.closest('[data-reveal]') : null;
            if (! trigger) {
                return;
            }
            const target = document.getElementById(trigger.getAttribute('data-reveal'));
            if (! target) {
                return;
            }
            event.preventDefault();
            target.hidden = ! target.hidden;
            trigger.setAttribute('aria-expanded', target.hidden ? 'false' : 'true');
            if (! target.hidden) {
                const field = target.querySelector(trigger.getAttribute('data-reveal-focus') || 'input:not([type="hidden"]), textarea, select');
                if (field) {
                    field.focus();
                }
            }
        });

        // When clicking Settings nav while already on Settings,
        // go to last-saved section; if already there, reset to first section
        var settingsNavLinks = document.querySelectorAll('a[href="{{ route('profile.edit') }}"]');
        settingsNavLinks.forEach(function(link) {
            if (link.closest('.mt-auto')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var currentSection = document.querySelector('.section-content[style*="display: block"]');
                    var currentId = currentSection ? currentSection.id : null;
                    var saved = stored('lastSettingsSection');
                    if (saved && saved !== currentId && isSection(saved)) {
                        showSection(saved);
                    } else {
                        showSection(sections[0].id);
                    }
                });
            }
        });

        // Highlight a field the visitor was sent here for: the phone (from boost) or the
        // suggestions toggle (from the dashboard's "Suggestions are off" row). Both are among the
        // profile's always-visible fields (the switches sit under Preferences, below the image),
        // so the section is all that needs opening.
        const highlightIds = { phone: 'phone-field', suggestions: 'suggestions-field' };
        if (highlightIds[urlParams.get('highlight')]) {
            const phoneField = document.getElementById(highlightIds[urlParams.get('highlight')]);
            if (phoneField) {
                FormKit.reveal(phoneField);
                phoneField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                phoneField.classList.add('ring-2', 'ring-[var(--brand-blue)]', 'rounded-lg', 'p-2', '-m-2');
                setTimeout(() => {
                    phoneField.classList.remove('ring-2', 'ring-[var(--brand-blue)]', 'rounded-lg', 'p-2', '-m-2');
                }, 5000);
            }
        }

        // A section with something typed and not saved gets a dot. Each section saves on its own,
        // and saving one reloads the page, so the dot is what says another still holds a change.
        // Left out: password fields (a browser filling one in is not the person typing), the text
        // box of a searchable list (typing a search chooses nothing; choosing does, and the list
        // itself reports that), and anything marked data-no-dirty because it acts at once and is
        // not part of a save (a code to verify, the backup tool, a field shown once to be copied).
        FormKit.track(document, { ignore: 'input[type="password"], input[role="combobox"], [data-no-dirty], [data-no-dirty] *' });
        window.addEventListener('load', function() {
            setTimeout(function() { FormKit.arm(); }, 400);
        });
    });

    window.addEventListener('load', function() {
        window.scrollTo(0, 0);
        requestAnimationFrame(function() {
            window.scrollTo(0, 0);
            setTimeout(function() {
                window.removeEventListener('scroll', _scrollGuard);
                window._settingsLoading = false;
                // The section an address named, or the one a refused save left a message in.
                if (window._settingsMoveTo) {
                    window._settingsMoveTo.scrollIntoView({ block: 'start' });
                }
            }, 300);
        });
    });
    </script>

</x-app-admin-layout>
