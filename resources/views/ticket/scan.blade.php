<x-app-admin-layout>

    <x-slot name="head">

        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
        <script src="{{ asset('js/html5-qrcode.min.js') }}" {!! nonce_attr() !!}></script>

        {{-- The door scanner, used standing at a door on a phone. The camera sits in a square
             stage, and the answer is a card laid over the same square (grid stacking, so a long
             order can grow it): a solid band that says admit or not from across a room, then
             who it is and what they hold. The camera is driven with Html5Qrcode, the lower-level
             class of the bundled library, so none of its own buttons are drawn. The bands are
             state colours measured as large text on white ink (green-700 5.0:1, red-700 6.5:1,
             blue-700 6.7:1, slate-600 7.6:1) and dark ink on amber-400 (10:1), in both themes. --}}
        <style {!! nonce_attr() !!}>
            .scan-picker {
                margin-bottom: 1rem;
            }
            .scan-stage {
                display: grid;
                width: 100%;
                max-width: 28rem;
                margin-inline: auto;
            }
            .scan-stage > * {
                grid-area: 1 / 1;
                min-width: 0;
            }
            .scan-camera {
                display: flex;
                align-items: center;
                justify-content: center;
                aspect-ratio: 1 / 1;
                overflow: hidden;
                border-radius: 1rem;
                background: #0b0b0d;
            }
            {{-- Under an answer the paused camera folds away, so the card is the size of what it
                 says. Only its box is clipped: the video keeps its size, so resuming does not
                 move the library's scanning frame. --}}
            .scan-stage.has-result .scan-camera {
                visibility: hidden;
                aspect-ratio: auto;
                height: 0;
            }
            #reader {
                width: 100%;
                border: 0 !important;
            }
            #reader video {
                display: block;
                width: 100% !important;
                height: auto !important;
            }
            .scan-overlay {
                position: relative;
                z-index: 2;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 1rem;
                padding: 1.5rem;
                border-radius: 1rem;
                text-align: center;
                color: #e5e7eb;
            }
            .scan-overlay > svg {
                width: 2.5rem;
                height: 2.5rem;
                color: #9ca3af;
            }
            .scan-overlay p {
                margin: 0;
                max-width: 22rem;
                font-size: 0.9375rem;
                line-height: 1.375rem;
            }
            .scan-hint {
                position: relative;
                z-index: 2;
                align-self: end;
                justify-self: center;
                margin: 0 0.75rem 0.875rem;
                border-radius: 999px;
                padding: 0.375rem 0.875rem;
                background: rgba(0, 0, 0, 0.6);
                font-size: 0.8125rem;
                line-height: 1.25rem;
                text-align: center;
                color: #fff;
                pointer-events: none;
            }
            .scan-result {
                position: relative;
                z-index: 3;
                align-self: stretch;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                border: 1px solid rgb(var(--ap-border));
                border-radius: 1rem;
                background: rgb(var(--ap-surface));
                box-shadow: var(--ap-shadow-card);
            }
            .scan-band {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem 1.25rem;
                color: #fff;
            }
            .scan-band > svg {
                flex: none;
                width: 2rem;
                height: 2rem;
            }
            .scan-band-title {
                flex: 1;
                min-width: 0;
                margin: 0;
                font-size: 1.5rem;
                line-height: 1.875rem;
                font-weight: 700;
                overflow-wrap: anywhere;
            }
            .scan-band-pill {
                flex: none;
                border-radius: 999px;
                padding: 0.25rem 0.625rem;
                background: rgba(255, 255, 255, 0.2);
                font-size: 0.875rem;
                font-weight: 600;
                white-space: nowrap;
            }
            .scan-result.is-success .scan-band { background: #15803d; }
            .scan-result.is-error .scan-band { background: #b91c1c; }
            .scan-result.is-info .scan-band { background: #1d4ed8; }
            .scan-result.is-neutral .scan-band { background: #475569; }
            .scan-result.is-warning .scan-band {
                background: #fbbf24;
                color: #451a03;
            }
            .scan-result.is-warning .scan-band-pill {
                background: rgba(69, 26, 3, 0.12);
            }
            .scan-body {
                display: flex;
                flex: 1;
                flex-direction: column;
                gap: 0.5rem;
                padding: 1rem 1.25rem 1.25rem;
            }
            .scan-body p {
                margin: 0;
            }
            .scan-reason {
                font-size: 1rem;
                line-height: 1.5rem;
                font-weight: 500;
                color: rgb(var(--ap-ink));
            }
            .scan-name {
                font-size: 1.25rem;
                line-height: 1.75rem;
                font-weight: 600;
                color: rgb(var(--ap-ink));
                overflow-wrap: anywhere;
            }
            .scan-label {
                font-size: 0.8125rem;
                color: rgb(var(--ap-ink-3));
            }
            .scan-big {
                font-size: 1.125rem;
                line-height: 1.625rem;
                font-weight: 600;
                color: rgb(var(--ap-ink));
                overflow-wrap: anywhere;
            }
            .scan-lines {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                margin: 0;
                padding: 0;
                list-style: none;
                font-size: 1rem;
                color: rgb(var(--ap-ink-2));
            }
            .scan-seats {
                display: flex;
                flex-wrap: wrap;
                gap: 0.375rem;
                margin-top: 0.375rem;
            }
            .scan-seat {
                display: inline-flex;
                align-items: center;
                min-height: 2rem;
                border: 1px solid rgb(var(--ap-border-strong));
                border-radius: 0.5rem;
                padding: 0 0.625rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: rgb(var(--ap-ink));
            }
            .scan-seat.is-used {
                border-color: #f59e0b;
                background: rgba(245, 158, 11, 0.12);
                color: #92400e;
            }
            .dark .scan-seat.is-used {
                color: #fcd34d;
            }
            .scan-meta {
                font-size: 0.875rem;
                line-height: 1.25rem;
                color: rgb(var(--ap-ink-3));
            }
            .scan-pass-badge {
                display: inline-flex;
                align-items: center;
                align-self: flex-start;
                gap: 0.25rem;
                border-radius: 999px;
                padding: 0.25rem 0.75rem;
                background: var(--ap-tint-2);
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--brand-blue);
            }
            .scan-tools {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
                margin-top: 0.75rem;
            }
            .scan-tools .page-tool[aria-pressed="false"] {
                color: rgb(var(--ap-ink-3));
            }
            .scan-actions {
                max-width: 28rem;
                margin-inline: auto;
            }
            .scan-countdown {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 1.5rem;
                height: 1.5rem;
                margin-inline-start: 0.5rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.25);
                font-size: 0.875rem;
                font-variant-numeric: tabular-nums;
            }
            @media (max-width: 639.98px) {
                {{-- The camera's hint says the same, and on a phone every line above the camera
                     pushes it further down. --}}
                .scan-page .page-lead {
                    display: none;
                }
                {{-- A thumb's reach on a phone: the next step stays at the bottom of the screen
                     however long the order is. --}}
                .scan-actions {
                    position: sticky;
                    bottom: 0;
                    z-index: 5;
                    max-width: none;
                    margin: 1rem -1.25rem -1.25rem;
                    border-top: 1px solid rgb(var(--ap-border));
                    border-radius: 0 0 0.75rem 0.75rem;
                    padding: 0.75rem 1.25rem calc(0.75rem + env(safe-area-inset-bottom));
                    background: rgb(var(--ap-surface));
                }
            }
            @media (prefers-reduced-motion: no-preference) {
                .scan-result {
                    animation: scan-result-in 0.16s ease-out;
                }
                @keyframes scan-result-in {
                    from {
                        opacity: 0;
                        transform: scale(0.98);
                    }
                }
            }
        </style>

    </x-slot>

    @php
        // Everything the script says, in one value: @json splits its argument on commas, so it is
        // handed a bare variable (CLAUDE.md, "Never hand @json an expression with a comma").
        $scanStrings = [
            'checking' => __('messages.scan_checking'),
            'checked_in' => __('messages.scan_checked_in'),
            'not_checked_in' => __('messages.scan_not_checked_in'),
            'already' => __('messages.scan_already_checked_in'),
            'first_scanned_at' => __('messages.scan_first_scanned_at'),
            'other_event' => __('messages.scan_other_event'),
            'other_date' => __('messages.scan_other_date'),
            'scanning_for' => __('messages.scan_scanning_for'),
            'overdue_title' => __('messages.installment_door_title'),
            'overdue_sub' => __('messages.installment_door_sub'),
            'could_not_check' => __('messages.scan_could_not_check'),
            'offline' => __('messages.scan_offline'),
            'server_error' => __('messages.scan_server_error'),
            'signed_out' => __('messages.scan_signed_out'),
            'invalid_qr' => __('messages.invalid_ticket_qr_code'),
            'ticket_line' => __('messages.scan_ticket_line'),
            'camera' => [
                'blocked' => __('messages.scan_camera_blocked'),
                'missing' => __('messages.scan_camera_missing'),
                'busy' => __('messages.scan_camera_busy'),
                'insecure' => __('messages.scan_camera_insecure'),
                'unsupported' => __('messages.scan_camera_unsupported'),
            ],
            'pass' => [
                'season_pass' => __('messages.season_pass'),
                'subscription' => __('messages.subscription'),
                'valid' => __('messages.pass_welcome_checked_in'),
                'already_today' => __('messages.pass_already_checked_in_today'),
                'no_event_today' => __('messages.pass_no_event_today'),
                'too_early' => __('messages.pass_valid'),
                'event_over' => __('messages.pass_event_over'),
                'limit_reached' => __('messages.pass_limit_reached'),
                'expired' => __('messages.pass_expired'),
                'not_covered' => __('messages.pass_not_covered'),
                'all_admits_used' => __('messages.pass_all_admits_used'),
                'no_room' => __('messages.pass_no_room_for_guest'),
                'visit_x_of_n' => __('messages.pass_visit_x_of_n'),
                'unlimited' => __('messages.pass_unlimited_visits'),
                'one_per_event' => __('messages.pass_one_per_event'),
                'admitted_x_of_n' => __('messages.pass_admitted_x_of_n'),
                'all_guests_admitted' => __('messages.pass_all_guests_admitted'),
                'entered_at' => __('messages.pass_entered_at'),
                'check_in_opens' => __('messages.pass_check_in_opens_at'),
                'next_event' => __('messages.pass_next_event'),
                'valid_until' => __('messages.pass_valid_until'),
            ],
        ];
        $secondaryButton = 'ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800';
    @endphp

    <div class="page-shell page-col is-narrow scan-page">
        <x-page-header :title="__('messages.scan_ticket')" :lead="__('messages.scan_lead')"
                       :back="route('sales')" :back-label="__('messages.sales')">
            {{-- The check-in dashboard is Pro and hidden below it, as on the Sales page. Scanning is not. --}}
            @if ($hasPro ?? false)
            <x-slot name="actions">
                <x-secondary-link :href="route('checkin.index')">{{ __('messages.checkin_dashboard') }}</x-secondary-link>
            </x-slot>
            @endif
        </x-page-header>

        @include('partials.team-access-notice', ['roles' => $planBlockedRoles])

        <div id="app">
            <div class="ap-card rounded-xl page-card">

                {{-- Which event the door is for. A pass is redeemed at it, and an ordinary ticket for
                     another event or another night of it stops for "Admit anyway". --}}
                @if (!empty($events) && count($events) > 0)
                <div class="scan-picker">
                    <p class="page-card-title mb-2">{{ __('messages.scanning_at_event') }}</p>
                    <x-event-selector />
                </div>
                @endif

                <div :class="['scan-stage', result ? 'has-result' : '']">
                    {{-- The library draws inside #reader, so nothing here is ever bound or removed
                         by Vue: a redraw must not take its video away. --}}
                    <div class="scan-camera">
                        <div id="reader"></div>
                    </div>

                    <div v-if="camera === 'idle'" class="scan-overlay">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" /></svg>
                        <x-brand-button v-on:click="startCamera">{{ __('messages.scan_start_camera') }}</x-brand-button>
                    </div>

                    <div v-else-if="camera === 'starting'" class="scan-overlay" role="status">
                        <svg class="animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span class="sr-only">{{ __('messages.loading') }}</span>
                    </div>

                    <div v-else-if="camera === 'error'" class="scan-overlay" role="alert">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                        <p>@{{ t.camera[cameraError] || t.camera.busy }}</p>
                        <x-brand-button v-if="cameraError !== 'insecure' && cameraError !== 'unsupported'" v-on:click="startCamera">{{ __('messages.try_again') }}</x-brand-button>
                    </div>

                    <p v-if="camera === 'scanning' && !result" class="scan-hint">{{ __('messages.scan_point_camera') }}</p>

                    {{-- The answer. Tapping it holds a count that is running, so it can be read. --}}
                    <div v-if="result" :class="['scan-result', 'is-' + tone]" v-on:pointerdown="holdCountdown">
                        <div class="scan-band">
                            <svg v-if="result.state === 'checking'" class="animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <svg v-else-if="tone === 'success'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <svg v-else-if="tone === 'warning'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                            <svg v-else-if="tone === 'error'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <svg v-else fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                            <p class="scan-band-title">@{{ bandTitle }}</p>
                            <span v-if="bandPill" class="scan-band-pill">@{{ bandPill }}</span>
                        </div>

                        <div v-if="result.state !== 'checking'" class="scan-body">
                            <span v-if="isPass" class="scan-pass-badge">@{{ passBadgeLabel }}</span>

                            <p v-if="reasonLine" class="scan-reason">@{{ reasonLine }}</p>

                            <template v-if="showDetails">
                                {{-- Another event or night: which one is the news. --}}
                                <template v-if="isElsewhere">
                                    <p class="scan-label">{{ __('messages.scan_ticket_is_for') }}</p>
                                    <p class="scan-big"><bdi>@{{ result.event }}</bdi></p>
                                    <p v-if="result.date" class="scan-big">@{{ result.date }}</p>
                                </template>

                                <p class="scan-name"><bdi>@{{ result.attendee }}</bdi></p>

                                <ul v-if="ticketLines.length" class="scan-lines">
                                    <li v-for="(line, index) in ticketLines" :key="index">
                                        <bdi>@{{ line.text }}</bdi>
                                        <div v-if="line.seats.length" class="scan-seats">
                                            <span v-for="(seat, n) in line.seats" :key="n" :class="['scan-seat', seat.used ? 'is-used' : '']"><bdi>@{{ seat.label }}</bdi></span>
                                        </div>
                                    </li>
                                </ul>

                                {{-- Season pass or subscription: where this visit stands --}}
                                <template v-if="isPass">
                                    <p v-if="passStatus === 'already_today' && result.checked_in_at" class="scan-meta">@{{ t.pass.entered_at }} @{{ result.checked_in_at }}</p>
                                    <p v-if="passStatus === 'too_early' && result.check_in_opens" class="scan-meta">@{{ t.pass.check_in_opens }} @{{ result.check_in_opens }}</p>
                                    <p v-if="passUsesLabel" class="scan-meta">@{{ passUsesLabel }}</p>
                                    <p v-if="admitsLabel && passStatus !== 'valid'" class="scan-meta">@{{ admitsLabel }}</p>
                                    <p v-if="passStatus === 'valid' && admitsPerEvent > 1 && admitsRemaining === 0" class="scan-meta">@{{ t.pass.all_guests_admitted }}</p>
                                    <p v-if="result.valid_until" class="scan-meta">@{{ t.pass.valid_until }} @{{ result.valid_until }}</p>
                                </template>

                                <p v-if="!isElsewhere" class="scan-meta">
                                    <bdi>@{{ result.event }}</bdi><template v-if="result.date"> &middot; <span v-if="isPass && passStatus === 'no_event_today'">@{{ t.pass.next_event }}: </span>@{{ result.date }}</template>
                                </p>
                            </template>

                            {{-- Red, too early or over: which night the ticket IS for --}}
                            <p v-else-if="result.event && result.date" class="scan-meta"><bdi>@{{ result.event }}</bdi> &middot; @{{ result.date }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="camera === 'scanning' && !result" class="scan-tools">
                    <button v-if="cameras.length > 1" v-on:click="switchCamera" type="button" class="page-tool">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        {{ __('messages.scan_switch_camera') }}
                    </button>
                    <button v-if="torchSupported" v-on:click="toggleTorch" type="button" class="page-tool" :aria-pressed="torchOn ? 'true' : 'false'">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        {{ __('messages.scan_light') }}
                    </button>
                    <button v-on:click="toggleSound" type="button" class="page-tool" :aria-pressed="soundOn ? 'true' : 'false'">
                        <svg v-if="soundOn" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" /></svg>
                        <svg v-else fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.531V19.94a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.506-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.395C2.806 8.757 3.63 8.25 4.51 8.25H6.75z" /></svg>
                        {{ __('messages.scan_sound') }}
                    </button>
                </div>

                {{-- What to do next, the one that goes on at the end. --}}
                <div v-if="result && result.state !== 'checking'" class="page-form-actions scan-actions">
                    <button v-if="result.override" v-on:click="admitAnyway" type="button" class="{{ $secondaryButton }}">
                        {{ __('messages.admit_anyway') }}
                    </button>
                    <button v-if="isPass && passStatus === 'valid' && admitsRemaining > 0" v-on:click="admitNextGuest" type="button" class="{{ $secondaryButton }}">
                        {{ __('messages.pass_admit_guest') }} (@{{ admitsRemaining }})
                    </button>
                    <x-brand-button v-if="result.reason !== 'signed_out'" v-on:click="startNewScan">
                        {{ __('messages.scan_next') }}<span v-if="countdown" class="scan-countdown" aria-hidden="true">@{{ countdown }}</span>
                    </x-brand-button>
                    <x-brand-button v-else v-on:click="reloadPage">{{ __('messages.reload') }}</x-brand-button>
                </div>
            </div>

            <p class="sr-only" aria-live="assertive">@{{ liveText }}</p>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        const { createApp } = Vue

        // Outside Vue's reactivity: a proxied camera, audio context or wake lock breaks on its own
        // methods ("Illegal invocation").
        let reader = null;
        let audio = null;
        let wakeLock = null;
        let countdownTimer = null;
        let fetchAbort = null;

        const AUTO_CONTINUE_SECONDS = 3;
        // When the scanner carries on by itself, the code just read is ignored this long: the guest
        // is often still holding their phone up, and would come back as "Already checked in".
        const SAME_CODE_MS = 4000;
        const REQUEST_TIMEOUT_MS = 12000;

        const store = {
            get(key) { try { return localStorage.getItem(key); } catch (e) { return null; } },
            set(key, value) { try { localStorage.setItem(key, value); } catch (e) {} },
            remove(key) { try { localStorage.removeItem(key); } catch (e) {} },
        };

        createApp({
            data() {
                return {
                    events: @json($events ?? []),
                    selectedEventId: @json($selectedEventId ?? ''),
                    scanUrlTemplate: @json($scanUrlTemplate ?? ''),
                    t: @json($scanStrings),
                    dropdownOpen: false,
                    camera: 'idle',
                    cameraError: null,
                    cameras: [],
                    torchSupported: false,
                    torchOn: false,
                    soundOn: store.get('scan_sound') !== 'off',
                    result: null,
                    lastScanUrl: null,
                    overrides: [],
                    countdown: 0,
                    lastCode: null,
                    ignoreUntil: 0,
                    liveText: '',
                }
            },
            computed: {
                selectedEvent() {
                    return this.events.find(e => e.id === this.selectedEventId) || null;
                },
                verdict() {
                    return this.result ? this.result.verdict || null : null;
                },
                isPass() {
                    return !!(this.result && this.result.is_pass);
                },
                passStatus() {
                    return this.isPass ? this.result.pass_status : null;
                },
                isElsewhere() {
                    return this.verdict === 'other_event' || this.verdict === 'other_date';
                },
                tone() {
                    const r = this.result;
                    if (!r || r.state === 'checking' || r.state === 'failed') return 'neutral';
                    if (this.isPass) {
                        if (r.error) return 'error';
                        if (this.passStatus === 'valid') return 'success';
                        if (this.passStatus === 'already_today') return 'warning';
                        if (['limit_reached', 'expired', 'not_covered'].includes(this.passStatus)) return 'error';
                        return 'info';
                    }
                    // `verdict` before `error`: the "another event / date" stops carry both.
                    if (this.verdict === 'checked_in') return 'success';
                    if (['partly_checked_in', 'already_checked_in', 'other_event', 'other_date', 'overdue'].includes(this.verdict)) return 'warning';
                    if (r.payment_status === 'overdue') return 'warning';
                    return r.error ? 'error' : 'success';
                },
                bandTitle() {
                    const r = this.result;
                    if (!r) return '';
                    if (r.state === 'checking') return this.t.checking;
                    if (r.state === 'failed') return this.t.could_not_check;
                    if (this.isPass) return r.error ? this.t.not_checked_in : this.passTitle;
                    switch (this.verdict) {
                        case 'checked_in':
                        case 'partly_checked_in':
                            return r.headcount_label || this.t.checked_in;
                        case 'already_checked_in': return this.t.already;
                        case 'other_event': return this.t.other_event;
                        case 'other_date': return this.t.other_date;
                        case 'overdue': return this.t.overdue_title.replace(':amount', r.amount_due || '');
                    }
                    return r.error ? this.t.not_checked_in : this.t.checked_in;
                },
                bandPill() {
                    const r = this.result;
                    if (r && (this.isElsewhere || this.verdict === 'overdue')) return r.headcount_label || null;
                    if (this.isPass && this.admitsLabel && this.passStatus === 'valid') return this.admitsLabel;
                    return null;
                },
                reasonLine() {
                    const r = this.result;
                    if (!r) return null;
                    if (r.state === 'failed') return r.message;
                    if (r.error && !this.isElsewhere) return r.error;
                    const firstAt = r.already_checked_in_at ? this.t.first_scanned_at.replace(':time', r.already_checked_in_at) : null;
                    switch (this.verdict) {
                        case 'checked_in': return this.t.checked_in;
                        case 'partly_checked_in': return [r.already_label, firstAt].filter(Boolean).join(' · ');
                        case 'already_checked_in': return firstAt;
                        case 'other_date': return r.door_date ? this.t.scanning_for.replace(':date', r.door_date) : null;
                        case 'overdue': return this.t.overdue_sub.replace(':paid', r.amount_paid || '').replace(':total', r.amount_total || '');
                    }
                    if (this.isPass && this.passStatus === 'already_today' && r.seats_full) return this.t.pass.no_room;
                    return null;
                },
                showDetails() {
                    const r = this.result;
                    return !!(r && r.state === 'done' && r.attendee && !(r.error && !this.isElsewhere));
                },
                ticketLines() {
                    const tickets = (this.result && this.result.tickets) || [];
                    return tickets.map(ticket => {
                        const slots = Object.values(ticket.seats || {});
                        const labels = Array.isArray(ticket.seat_labels) ? ticket.seat_labels : [];
                        return {
                            text: this.t.ticket_line.replace(':count', Math.max(1, slots.length)).replace(':type', ticket.type || ''),
                            // A real seat only where the event has a seating plan: a slot's number
                            // means nothing to the door.
                            seats: labels.length ? slots.map((value, index) => ({ label: labels[index] || '', used: !!value })).filter(s => s.label) : [],
                        };
                    });
                },
                passTitle() {
                    // A fully admitted multi-admit pass reads "all admissions used", not the
                    // single-admit "already checked in today"; a full room says so instead.
                    if (this.passStatus === 'already_today' && this.admitsPerEvent > 1 && !this.result.seats_full) {
                        return this.t.pass.all_admits_used.replace(':count', this.admitsPerEvent);
                    }
                    return this.t.pass[this.passStatus] || this.t.checked_in;
                },
                passBadgeLabel() {
                    return this.result && this.result.pass_usage_type === 'per_occurrence' ? this.t.pass.season_pass : this.t.pass.subscription;
                },
                passUsesLabel() {
                    if (!this.isPass) return null;
                    const r = this.result;
                    if (r.pass_usage_type === 'total' && r.pass_max_uses) {
                        return this.t.pass.visit_x_of_n.replace(':used', r.pass_usage_count).replace(':total', r.pass_max_uses);
                    }
                    if (r.pass_usage_type === 'unlimited' || r.pass_usage_type === 'per_occurrence') return this.t.pass.unlimited;
                    if (r.pass_usage_type === 'per_event') return this.t.pass.one_per_event;
                    return null;
                },
                admitsPerEvent() {
                    return (this.result && this.result.admits_per_event) || 1;
                },
                admitsUsed() {
                    return (this.result && this.result.admits_used) || 0;
                },
                admitsRemaining() {
                    return Math.max(0, this.admitsPerEvent - this.admitsUsed);
                },
                admitsLabel() {
                    if (this.admitsPerEvent <= 1 || !['valid', 'already_today'].includes(this.passStatus)) return null;
                    // Clamp to the limit so a lowered admits cap can't read "3 of 2".
                    const used = Math.min(this.admitsPerEvent, this.admitsUsed || this.admitsPerEvent);
                    return this.t.pass.admitted_x_of_n.replace(':used', used).replace(':total', this.admitsPerEvent);
                },
                // Carry on by itself only on a clean green with nothing left to do.
                autoContinues() {
                    return this.tone === 'success' && !(this.isPass && this.admitsRemaining > 0);
                },
            },
            methods: {
                toggleDropdown() {
                    this.dropdownOpen = !this.dropdownOpen;
                },
                closeDropdown() {
                    this.dropdownOpen = false;
                },
                onEventChange(eventId) {
                    this.selectedEventId = eventId;
                    this.closeDropdown();
                    store.set('scan_event_id', eventId);
                },

                async startCamera() {
                    if (!window.isSecureContext) return this.cameraFailed('insecure');
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof Html5Qrcode === 'undefined') {
                        return this.cameraFailed('unsupported');
                    }

                    this.camera = 'starting';
                    this.cameraError = null;

                    if (!reader) {
                        reader = new Html5Qrcode('reader', {
                            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                            verbose: false,
                            experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                        });
                    }

                    const saved = store.get('scan_camera_id');
                    try {
                        await this.runCamera(saved ? { deviceId: { exact: saved } } : { facingMode: 'environment' });
                    } catch (error) {
                        // A camera saved on this phone that has gone: try the default one once.
                        if (saved && /NotFound|Overconstrained|not found/i.test(String(error && (error.name || error)))) {
                            store.remove('scan_camera_id');
                            try {
                                await this.runCamera({ facingMode: 'environment' });
                            } catch (retryError) {
                                return this.cameraFailed(this.cameraProblem(retryError));
                            }
                        } else {
                            return this.cameraFailed(this.cameraProblem(error));
                        }
                    }

                    this.camera = 'scanning';
                    store.set('scan_camera_started', '1');
                    this.afterCameraStarted();
                },
                runCamera(source) {
                    return reader.start(source, {
                        fps: 10,
                        qrbox: (width, height) => {
                            const side = Math.max(120, Math.floor(Math.min(width, height) * 0.7));
                            return { width: side, height: side };
                        },
                        aspectRatio: 1,
                    }, this.onDecode, () => {});
                },
                afterCameraStarted() {
                    try {
                        const settings = reader.getRunningTrackSettings();
                        if (settings && settings.deviceId) store.set('scan_camera_id', settings.deviceId);
                    } catch (e) {}

                    try {
                        this.torchSupported = !!reader.getRunningTrackCameraCapabilities().torchFeature().isSupported();
                    } catch (e) {
                        this.torchSupported = false;
                    }
                    this.torchOn = false;

                    Html5Qrcode.getCameras().then(list => { this.cameras = list || []; }).catch(() => {});
                    this.keepAwake();
                },
                cameraProblem(error) {
                    const text = String(error && (error.name || error.message || error));
                    if (/NotAllowed|Permission|denied/i.test(text)) return 'blocked';
                    if (/NotFound|Overconstrained|not found|no camera/i.test(text)) return 'missing';
                    return 'busy';
                },
                cameraFailed(reason) {
                    this.camera = 'error';
                    this.cameraError = reason;
                },
                async switchCamera() {
                    if (!reader || this.cameras.length < 2) return;
                    let current = store.get('scan_camera_id');
                    const index = this.cameras.findIndex(c => c.id === current);
                    const next = this.cameras[(index + 1) % this.cameras.length];
                    try { await reader.stop(); } catch (e) {}
                    this.camera = 'starting';
                    try {
                        await this.runCamera({ deviceId: { exact: next.id } });
                        this.camera = 'scanning';
                        this.afterCameraStarted();
                        if (this.result) reader.pause(true);
                    } catch (error) {
                        this.cameraFailed(this.cameraProblem(error));
                    }
                },
                async toggleTorch() {
                    try {
                        await reader.getRunningTrackCameraCapabilities().torchFeature().apply(!this.torchOn);
                        this.torchOn = !this.torchOn;
                    } catch (e) {
                        this.torchSupported = false;
                    }
                },
                toggleSound() {
                    this.soundOn = !this.soundOn;
                    store.set('scan_sound', this.soundOn ? 'on' : 'off');
                    if (this.soundOn) this.unlockAudio();
                },

                onDecode(text) {
                    if (this.result) return;
                    if (text === this.lastCode && Date.now() < this.ignoreUntil) return;

                    try { reader.pause(true); } catch (e) {}
                    this.lastCode = text;
                    this.overrides = [];

                    let url;
                    try {
                        url = new URL(text);
                    } catch (e) {
                        return this.showInvalidCode();
                    }

                    // Take only the event id + secret from the QR. The path may carry a base
                    // path (an install served under /public/), and the origin may be a custom
                    // domain, so neither is safe to reuse as the POST target.
                    const match = url.pathname.match(/\/ticket\/view\/([^/]+)\/([^/]+)\/?$/);
                    if (!match) return this.showInvalidCode();

                    this.lastScanUrl = this.scanUrlTemplate
                        .replace('__EVENT_ID__', encodeURIComponent(match[1]))
                        .replace('__SECRET__', encodeURIComponent(match[2]));
                    this.postScan();
                },
                showInvalidCode() {
                    this.settle({ state: 'done', verdict: 'error', error: this.t.invalid_qr });
                },
                async postScan() {
                    if (!this.lastScanUrl) return;
                    this.clearCountdown();
                    // Never green before the server has answered.
                    this.result = { state: 'checking' };

                    if (fetchAbort) fetchAbort.abort();
                    fetchAbort = new AbortController();
                    const timer = setTimeout(() => fetchAbort && fetchAbort.abort(), REQUEST_TIMEOUT_MS);
                    const token = document.querySelector('meta[name="csrf-token"]');

                    try {
                        const response = await fetch(this.lastScanUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            signal: fetchAbort.signal,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                            },
                            body: JSON.stringify({ scan_event_id: this.selectedEventId || null, overrides: this.overrides }),
                        });
                        clearTimeout(timer);

                        // Signed out on a long shift: the session or its token has gone.
                        if (response.status === 401 || response.status === 419 || (response.redirected && /\/login/.test(response.url))) {
                            return this.failed('signed_out', this.t.signed_out);
                        }
                        if (!response.ok || !(response.headers.get('content-type') || '').includes('json')) {
                            return this.failed('server_error', this.t.server_error);
                        }

                        this.settle(Object.assign({ state: 'done' }, await response.json()));
                    } catch (error) {
                        clearTimeout(timer);
                        this.failed('offline', this.t.offline);
                    }
                },
                failed(reason, message) {
                    this.settle({ state: 'failed', reason, message });
                },
                settle(result) {
                    this.result = result;
                    this.liveText = [this.bandTitle, this.reasonLine, result.attendee].filter(Boolean).join('. ');
                    this.signal(this.tone);
                    if (this.autoContinues) this.startCountdown();
                },
                // Wave through the stop on screen, keeping any waved through before it.
                admitAnyway() {
                    if (!this.result || !this.result.override) return;
                    this.overrides = [...new Set([...this.overrides, this.result.override])];
                    this.postScan();
                },
                // Admit the next person (holder's guest) on a multi-admit pass without re-aiming
                // the camera at the same QR. The scan endpoint counts the admission.
                admitNextGuest() {
                    this.overrides = [];
                    this.postScan();
                },
                // `auto` only from the count: a tap passes the click event, and after a tap the same
                // code is read again at once (the guest on a pass that admits two shows the same one).
                startNewScan(auto) {
                    this.clearCountdown();
                    if (fetchAbort) fetchAbort.abort();
                    this.result = null;
                    this.overrides = [];
                    this.ignoreUntil = auto === true ? Date.now() + SAME_CODE_MS : 0;

                    // Resume the paused camera: no new session, nothing redrawn between guests.
                    if (this.camera === 'scanning' && reader) {
                        try {
                            if (reader.getState() === Html5QrcodeScannerState.PAUSED) reader.resume();
                            if (reader.getState() === Html5QrcodeScannerState.SCANNING) return;
                        } catch (e) {}
                    }
                    if (this.camera !== 'starting') this.startCamera();
                },
                reloadPage() {
                    window.location.reload();
                },

                startCountdown() {
                    this.clearCountdown();
                    this.countdown = AUTO_CONTINUE_SECONDS;
                    countdownTimer = setInterval(() => {
                        this.countdown -= 1;
                        if (this.countdown <= 0) this.startNewScan(true);
                    }, 1000);
                },
                clearCountdown() {
                    if (countdownTimer) clearInterval(countdownTimer);
                    countdownTimer = null;
                    this.countdown = 0;
                },
                holdCountdown() {
                    this.clearCountdown();
                },

                // A buzz and a tone the door can hear and feel without looking: one for green,
                // more for anything else.
                signal(tone) {
                    if (tone === 'neutral' && this.result && this.result.state === 'checking') return;
                    try {
                        if (navigator.vibrate) navigator.vibrate(tone === 'success' ? 60 : [90, 70, 90, 70, 90]);
                    } catch (e) {}

                    if (!this.soundOn) return;
                    const notes = {
                        success: [[880, 0, 0.12]],
                        warning: [[660, 0, 0.1], [660, 0.16, 0.1]],
                    }[tone] || [[220, 0, 0.35]];
                    try {
                        this.unlockAudio();
                        if (!audio) return;
                        const start = audio.currentTime;
                        notes.forEach(([frequency, at, length]) => {
                            const oscillator = audio.createOscillator();
                            const gain = audio.createGain();
                            oscillator.type = 'sine';
                            oscillator.frequency.value = frequency;
                            gain.gain.setValueAtTime(0.0001, start + at);
                            gain.gain.exponentialRampToValueAtTime(0.25, start + at + 0.01);
                            gain.gain.exponentialRampToValueAtTime(0.0001, start + at + length);
                            oscillator.connect(gain).connect(audio.destination);
                            oscillator.start(start + at);
                            oscillator.stop(start + at + length + 0.02);
                        });
                    } catch (e) {}
                },
                unlockAudio() {
                    try {
                        const Context = window.AudioContext || window.webkitAudioContext;
                        if (!Context) return;
                        audio = audio || new Context();
                        if (audio.state === 'suspended') audio.resume();
                    } catch (e) {}
                },

                // A door shift is longer than a screen timeout, and a locked phone stops the camera.
                async keepAwake() {
                    try {
                        if ('wakeLock' in navigator && !wakeLock && document.visibilityState === 'visible') {
                            wakeLock = await navigator.wakeLock.request('screen');
                            wakeLock.addEventListener('release', () => { wakeLock = null; });
                        }
                    } catch (e) {}
                },
                onVisible() {
                    if (document.visibilityState !== 'visible') return;
                    if (this.camera !== 'scanning' || !reader) return;
                    this.keepAwake();

                    // A camera the phone took away while it was locked does not come back by itself.
                    const video = document.querySelector('#reader video');
                    const track = video && video.srcObject && video.srcObject.getVideoTracks ? video.srcObject.getVideoTracks()[0] : null;
                    if (!track || track.readyState === 'ended') {
                        const restart = () => {
                            this.camera = 'idle';
                            this.startCamera();
                        };
                        try {
                            reader.stop().catch(() => {}).finally(restart);
                        } catch (e) {
                            restart();
                        }
                    }
                },
            },
            mounted() {
                // The event chosen on this phone last time, while it is still on today: a stale
                // pick from last week would stop every ticket tonight as "another event".
                const saved = store.get('scan_event_id');
                if (saved && this.events.some(e => e.id === saved && e.when === 'today')) {
                    this.selectedEventId = saved;
                }

                document.addEventListener('click', (e) => {
                    const el = document.getElementById('event-selector-dropdown');
                    if (el && !el.contains(e.target)) {
                        this.closeDropdown();
                    }
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') this.closeDropdown();
                });
                // A browser only lets a page make a sound after a tap on it.
                document.addEventListener('pointerdown', () => { if (this.soundOn) this.unlockAudio(); }, { once: true });
                document.addEventListener('visibilitychange', () => this.onVisible());

                if (!window.isSecureContext) return this.cameraFailed('insecure');

                // Start by itself where the camera is already allowed, or has been started on this
                // phone before; otherwise wait for the button, so nobody is asked on arrival.
                const autoStart = () => this.startCamera();
                if (store.get('scan_camera_started') === '1') return autoStart();
                if (navigator.permissions && navigator.permissions.query) {
                    navigator.permissions.query({ name: 'camera' }).then(status => {
                        if (status.state === 'granted') autoStart();
                        else if (status.state === 'denied') this.cameraFailed('blocked');
                    }).catch(() => {});
                }
            }
        }).mount('#app')
    </script>

</x-app-admin-layout>
