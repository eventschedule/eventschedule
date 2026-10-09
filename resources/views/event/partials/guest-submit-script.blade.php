{{-- The Vue app behind event/guest-submit.blade.php. Included from it, so it shares that view's
     variables ($role, $words, $labels, the request-form questions). --}}
  <script {!! nonce_attr() !!}>
    const { createApp } = Vue;

    const app = createApp({
        // The time boxes, the clock note, the bar, the emailed-code step and the small readers are
        // shared with the booking form (partials/request-form-kit).
        mixins: [window.RequestFormKit],
        data() {
            return {
                event: {
                    name: '',
                    name_en: '',
                    event_date: '',
                    event_start_time: '',
                    event_end_time: '',
                    is_online: false,
                    venue_name: '',
                    venue_name_en: '',
                    venue_country_code: @json($role->country_code),
                    venue_address1: '',
                    venue_city: '',
                    venue_state: '',
                    venue_postal_code: '',
                    event_url: '',
                    description: '',
                    social_image: null,
                    ticket_price: '',
                    ticket_currency_code: @json($defaultCurrency),
                    registration_url: '',
                    short_description: '',
                    short_description_en: '',
                    coupon_code: '',
                    coupon_discount: '',
                    coupon_discount_type: @json(\App\Models\Event::DEFAULT_COUPON_DISCOUNT_TYPE),
                    category_id: '',
                    group_id: '',
                },
                words: @json($words),
                labels: @json($labels),
                currencies: @json(collect($currencies)->map(fn($c) => ['value' => $c->value])->values()),
                importFields: @json($importFields),
                // Times typed into this form are read as the curator schedule's local time.
                scheduleTimezone: @json($role->timezone),
                deviceTimezone: '',
                requiredFields: @json($requiredImportFields),
                requestCustomFields: @json($requestCustomFields),
                customFieldValues: @json($requestCustomFieldValues),
                groups: @json(($role->groups ?? collect())->map(fn($g) => ['id' => \App\Utils\UrlUtils::encodeId($g->id), 'name' => $g->translatedName()])->values()),
                categories: @json($categories),
                use24hr: {{ $use24hr ? 'true' : 'false' }},
                aiEnabled: {{ $aiEnabled ? 'true' : 'false' }},
                pickers: { date: null, custom: {} },
                // The two time boxes: what is typed in them, and which one has its list open.
                timeText: { start: '', end: '' },
                timeBad: { start: false, end: false },
                timeOpen: null,
                timeHighlight: -1,
                // What the flyer wrote, so Undo takes back only that.
                filled: {},
                // Identity
                isAuthed: {{ auth()->check() ? 'true' : 'false' }},
                alreadyConnected: {{ $alreadyConnected ? 'true' : 'false' }},
                requiresCode: {{ (config('app.hosted') && ! config('app.is_testing')) ? 'true' : 'false' }},
                // A selfhost without ALLOW_REGISTRATION rejects account creation in
                // createAccountWithCode(), but only at submit time - so the register branch is
                // suppressed and the panel opens in login mode instead of letting someone fill
                // the whole form for nothing.
                registrationEnabled: {{ public_registration_enabled() ? 'true' : 'false' }},
                turnstileEnabled: {{ \App\Utils\TurnstileUtils::isEnabled() ? 'true' : 'false' }},
                turnstileSiteKey: @json(\App\Utils\TurnstileUtils::getSiteKey()),
                turnstileToken: '',
                turnstileWidgetId: null,
                turnstileTries: 0,
                talents: @json(auth()->check() ? auth()->user()->talents()->get()->map(fn($t) => ['id' => \App\Utils\UrlUtils::encodeId($t->id), 'name' => $t->name])->values() : []),
                // Encoded, not echoed: Blade escapes a quote to &#039; and this sits inside a
                // <script>, where that is a syntax error that kills the whole Vue app silently.
                accountMode: @json(public_registration_enabled() ? 'register' : 'login'),
                scheduleName: '',
                userName: @json(auth()->check() ? auth()->user()->name : ''),
                userEmail: '',
                userPassword: '',
                verificationCode: '',
                acceptedTerms: false,
                emailChecking: false,
                emailExists: null,
                emailStub: false,
                // The emailed code is its own step, entered after Submit.
                step: 'form',
                codeSending: false,
                codeEmail: '',
                codeSentAt: 0,
                codeError: null,
                // The six boxes: whether the input over them has the caret, and where the digits
                // that have just arrived begin (they pop in).
                codeFocused: false,
                codeFrom: 0,
                resendCountdown: 0,
                resendTimer: null,
                selectedTalentId: '',
                honeypot: '',
                submitted: false,
                submissionResult: null,
                sent: { name: '', when: '', where: '', image: null },
                // What the server refused, by field, until the visitor changes something.
                serverErrors: {},
                barMessage: null,
                saving: false,
                showPassword: false,
                // Fields only show an error after a submit attempt.
                triedSubmit: false,
                // The page was left open past its session: nothing can be sent until it is reloaded.
                expired: false,
                pendingSubmit: false,
                openRow: null,
                // The flyer
                flyerPreviewUrl: null,
                flyerBusy: false,
                flyerError: null,
                flyerNote: null,
                showPageName: false,
                flyerDragging: false,
                flyerDragDepth: 0,
                showPaste: false,
                autoFillText: '',
                autoFillDone: false,
                beforeAutoFill: null,
                emailCheckSeq: 0,
                // Draft autosave. Event fields in localStorage; who the visitor is and what they
                // answered the schedule's questions stay in sessionStorage, which ends with the tab.
                draftKey: 'es_guest_submit_draft_' + @json($role->subdomain),
                sessionKey: 'es_guest_submit_you_' + @json($role->subdomain),
                draftRestored: false,
                draftTimer: null,
            }
        },

        created() {
            if (this.isAuthed && this.talents.length) {
                this.selectedTalentId = this.talents[0].id;
            }
        },

        mounted() {
            try {
                this.deviceTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            } catch (e) { /* very old browsers */ }

            this.$nextTick(() => {
                document.getElementById('event-submit-app').classList.add('loaded');

                // Autofocus the event name on desktop only (avoid popping the mobile keyboard)
                if (window.innerWidth >= 640) {
                    var nameEl = document.getElementById('submit_event_name');
                    if (nameEl) nameEl.focus({ preventScroll: true });
                }

                this.maybeRenderTurnstile();
                this.publishBarHeight();
                window.addEventListener('resize', () => this.publishBarHeight());
            });

            // Flatpickr and EasyMDE live in the deferred Vite bundle, which runs AFTER this
            // parse-time script mounts - their window globals are not defined yet. Initialize
            // them on DOMContentLoaded (deferred modules run first), matching booking-request.
            var self = this;
            var initWidgets = function() {
                var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
                var localeConfig = fpLocale ? { locale: fpLocale } : {};

                if (window.flatpickr) {
                    var dateEl = document.getElementById('submit_event_date');
                    if (dateEl) {
                        self.pickers.date = window.flatpickr(dateEl, Object.assign({
                            allowInput: true,
                            altInput: true,
                            altFormat: 'M j, Y',
                            dateFormat: 'Y-m-d',
                            minDate: 'today',
                            onChange: function(selectedDates, dateStr) { self.event.event_date = dateStr; },
                        }, localeConfig));
                    }

                    // An example in the empty box, written by the picker in the format it shows,
                    // and the label's name on the box a person actually sees (Flatpickr hides the
                    // labelled one behind a copy).
                    var sample = new Date();
                    sample.setDate(sample.getDate() + 14);
                    var hint = function(picker, labelId, required) {
                        if (!picker) return;
                        var shown = self.shownInput(picker);
                        if (!shown) return;
                        if (picker.altInput && !picker.isMobile) shown.placeholder = picker.formatDate(sample, picker.config.altFormat);
                        if (labelId && document.getElementById(labelId)) shown.setAttribute('aria-labelledby', labelId);
                        shown.setAttribute('aria-required', required ? 'true' : 'false');
                    };
                    hint(self.pickers.date, 'submit_event_date_label', true);

                    // The schedule's own date questions. Each picker is kept: an answer put back
                    // after a reload has to be shown in its box (restoreSession), a missing one
                    // ringed (paintPickers), and the bar's link has to land on the box that shows
                    // (goTo). markRaw, so Vue does not walk the picker's own state.
                    document.querySelectorAll('[data-gs-date]').forEach(function(el) {
                        var picker = window.flatpickr(el, Object.assign({
                            allowInput: true,
                            altInput: true,
                            altFormat: 'M j, Y',
                            dateFormat: 'Y-m-d',
                            defaultDate: self.customFieldValues[el.dataset.key] || null,
                            onChange: function(selectedDates, dateStr) { self.customFieldValues[el.dataset.key] = dateStr; },
                        }, localeConfig));
                        self.pickers.custom[el.dataset.key] = Vue.markRaw(picker);
                        hint(picker, 'submit_custom_field_label_' + el.dataset.key, el.dataset.required === '1');
                    });
                }

                // EasyMDE description. The global .html-editor handler (app.js, also on
                // DOMContentLoaded but registered after this listener) inits it too if we
                // don't get there first; both paths guard via _easyMDE, and submit reads the
                // instance from the node either way.
                var descEl = document.getElementById('submit_description');
                if (descEl && !descEl._easyMDE && window.initTinyMDE) {
                    window.initTinyMDE(descEl, function() {
                        if (descEl._easyMDE) self.event.description = descEl._easyMDE.value();
                    });
                }

                // Restore only once the widgets exist (the date boxes are Flatpickr's, the
                // description is EasyMDE's).
                self.restoreSession();
                self.restoreDraft();
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initWidgets);
            } else {
                initWidgets();
            }
        },

        watch: {
            accountMode(mode) {
                if (mode === 'login') {
                    this.turnstileToken = '';
                    this.turnstileWidgetId = null;
                } else {
                    this.$nextTick(() => this.maybeRenderTurnstile());
                }
            },

            // Flatpickr's visible altInput is a DOM clone outside Vue's reach, so the error
            // ring can't be bound with :class - mirror it for each picker.
            problemKeys() { this.paintPickers(); },
            triedSubmit() { this.paintPickers(); },
            submitted() { this.publishBarHeight(); },
            step() { this.publishBarHeight(); },

            event: {
                deep: true,
                handler() {
                    this.touched();
                    this.queueDraftSave();
                },
            },
            customFieldValues: { deep: true, handler() { this.touched(); this.queueDraftSave(); } },
            userEmail() { this.touched(); this.queueDraftSave(); },
            userName() { this.touched(); this.queueDraftSave(); },
            userPassword() { this.touched(); },
            scheduleName() { this.queueDraftSave(); },
            acceptedTerms() { this.touched(); },
            openRow() { this.queueDraftSave(); },
        },

        computed: {

            needsPageName() {
                if (this.accountMode === 'login') return false;
                return this.talents.length === 0;
            },

            // What the visitor's page will be called if they leave it alone: their own name.
            pageNameShown() {
                return (this.scheduleName || this.userName || '').trim();
            },

            // Has the visitor begun? Until then the bar is two buttons and says nothing.
            started() {
                const e = this.event;
                return !!((e.name || '').trim() || e.event_date || e.event_start_time || (e.venue_name || '').trim()
                    || (e.event_url || '').trim() || e.social_image || (this.userEmail || '').trim());
            },

            showTalentPicker() {
                return this.isAuthed && this.talents.length > 1;
            },

            postingAsName() {
                return (this.isAuthed && this.talents.length === 1) ? this.talents[0].name : null;
            },

            // Submitting follows the schedule. Not said to someone who already does.
            willFollow() {
                return !(this.isAuthed && this.alreadyConnected);
            },

            needsCode() {
                return !this.isAuthed && this.accountMode === 'register' && this.requiresCode;
            },

            hasListingRow() {
                const f = this.importFields, r = this.requiredFields;
                return !!(f.short_description || r.short_description || f.category_id || r.category_id
                    || ((f.group_id || r.group_id) && this.groups.length > 0));
            },

            // Single source of truth for what blocks a submit: one entry per field, in the
            // order the page shows them, each with the words to put under the field. Powers
            // the red ring, the message, the bar's list and the jump to the first one.
            problems() {
                const e = this.event, w = this.words, r = this.requiredFields;
                const list = [];
                const add = (key, id, message, row) => list.push({ key, id, message, row: row || null, label: this.labelOf(key) });

                if (!(e.name || '').trim()) add('name', 'submit_event_name', w.required);
                if (!e.event_date) add('event_date', 'submit_event_date', w.required);
                if (this.timeBad.start) add('event_start_time', 'submit_event_time', this.timeHelp());
                else if (!e.event_start_time) add('event_start_time', 'submit_event_time', w.required);
                if (this.timeBad.end) add('event_end_time', 'submit_event_end_time', this.timeHelp());
                if (e.is_online) {
                    if (!(e.event_url || '').trim()) add('event_url', 'submit_event_url', w.required);
                    else if (!this.cleanUrl(e.event_url)) add('event_url', 'submit_event_url', w.valid_url);
                } else if (!((e.venue_name || '').trim() || (e.venue_address1 || '').trim() || (e.venue_city || '').trim())) {
                    add('venue_name', 'submit_venue_name', w.required);
                }

                // Curator-configured required submission fields (mirrored server-side).
                if (r.description && !(e.description || '').trim()) add('description', 'submit_description', w.required, 'description');
                // A free event answers a required price: zero is a price.
                if (r.ticket_price && (e.ticket_price === '' || e.ticket_price === null || e.ticket_price === undefined)) add('ticket_price', 'submit_ticket_price', w.required, 'price');
                if ((e.registration_url || '').trim()) {
                    if (!this.cleanUrl(e.registration_url)) add('registration_url', 'submit_registration_url', w.valid_url, 'price');
                } else if (r.registration_url) {
                    add('registration_url', 'submit_registration_url', w.required, 'price');
                }
                if (r.coupon_code && !(e.coupon_code || '').trim()) add('coupon_code', 'submit_coupon_code', w.required, 'price');
                if (r.short_description && !(e.short_description || '').trim()) add('short_description', 'submit_short_description', w.required, 'listing');
                if (r.category_id && !e.category_id) add('category_id', 'submit_category_id', w.required, 'listing');
                if (r.group_id && this.groups.length > 0 && !e.group_id) add('group_id', 'submit_group_id', w.required, 'listing');

                // Schedule-defined request questions. Keys are prefixed so they cannot collide
                // with the fixed fields above. "Required" is the wrong word for an answer that is
                // there but in the wrong shape, so that one quotes the schedule's own hint.
                this.requestCustomFields.forEach((field) => {
                    const key = 'cf_' + field.key, id = 'submit_custom_field_' + field.key;
                    if (this.customFieldIsMissing(field)) {
                        list.push({ key, id, message: w.required, row: null, label: field.label });
                    } else if (this.customFieldFailsPattern(field)) {
                        list.push({ key, id, message: field.regex_hint || w.required, row: null, label: field.label });
                    }
                });

                if (!this.isAuthed) {
                    const email = (this.userEmail || '').trim();
                    if (!email) add('account_email', 'account_email', w.required);
                    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) add('account_email', 'account_email', w.valid_email);

                    if (this.accountMode === 'login') {
                        if (!this.userPassword) add('account_password', 'login_password', w.required);
                    } else {
                        if (!(this.userName || '').trim()) add('account_name', 'account_name', w.required);
                        // min:8 mirrors the server rule. Typed but short is not "missing".
                        if (!this.userPassword) add('account_password', 'account_password', w.required);
                        else if (this.userPassword.length < 8) add('account_password', 'account_password', w.password_min);
                        if (!this.acceptedTerms) add('terms', 'account_terms', w.required);
                    }
                }

                // What the server refused on the last attempt, where the page has not caught it.
                Object.keys(this.serverErrors).forEach((key) => {
                    if (!list.some((p) => p.key === key)) {
                        const meta = this.fieldMeta(key);
                        list.push({ key, id: meta.id, message: this.serverErrors[key], row: meta.row, label: meta.label });
                    }
                });

                return list;
            },

            // One line per row, written from what is in it.
            rows() {
                const e = this.event;
                const plain = (e.description || '').replace(/[#*_>`~\[\]()]/g, ' ').replace(/\s+/g, ' ').trim();
                const price = [];
                if (e.ticket_price !== '' && e.ticket_price !== null && e.ticket_price !== undefined) {
                    price.push(Number(e.ticket_price) === 0 ? this.words.free : (e.ticket_price + ' ' + e.ticket_currency_code));
                }
                const link = this.cleanUrl(e.registration_url);
                if (link) {
                    try { price.push(new URL(link).hostname.replace(/^www\./, '')); } catch (err) { /* shown as typed */ }
                }
                if ((e.coupon_code || '').trim()) price.push(e.coupon_code.trim());
                const listing = [];
                const category = this.categories.find((c) => c.id === String(e.category_id));
                if (category) listing.push(category.name);
                const group = this.groups.find((g) => g.id === e.group_id);
                if (group) listing.push(group.name);
                if ((e.short_description || '').trim()) listing.push(e.short_description.trim());
                return {
                    description: plain.length > 90 ? plain.slice(0, 90) + '…' : plain,
                    price: price.join(' · '),
                    listing: listing.join(' · '),
                };
            },

        },

        methods: {
            labelOf(key) {
                return this.labels[key] || key;
            },

            // Where a field the server named lives on this page.
            fieldMeta(key) {
                const ids = {
                    name: 'submit_event_name', event_date: 'submit_event_date', event_start_time: 'submit_event_time',
                    event_end_time: 'submit_event_end_time', event_url: 'submit_event_url', venue_name: 'submit_venue_name', description: 'submit_description',
                    ticket_price: 'submit_ticket_price', registration_url: 'submit_registration_url',
                    coupon_code: 'submit_coupon_code', short_description: 'submit_short_description',
                    category_id: 'submit_category_id', group_id: 'submit_group_id', account_email: 'account_email',
                    account_name: 'account_name', account_password: this.accountMode === 'login' ? 'login_password' : 'account_password',
                    terms: 'account_terms',
                };
                const rowsByKey = {
                    description: 'description', ticket_price: 'price', registration_url: 'price', coupon_code: 'price',
                    short_description: 'listing', category_id: 'listing', group_id: 'listing',
                };
                if (key.indexOf('cf_') === 0) {
                    const field = this.requestCustomFields.find((f) => 'cf_' + f.key === key);
                    return { id: 'submit_custom_field_' + key.slice(3), row: null, label: field ? field.label : key };
                }
                return { id: ids[key] || null, row: rowsByKey[key] || null, label: this.labelOf(key) };
            },

            // --- rows ---------------------------------------------------------------------

            rowFixed(row) {
                const r = this.requiredFields;
                if (row === 'description') return !!r.description;
                if (row === 'price') return !!(r.ticket_price || r.registration_url || r.coupon_code);
                if (row === 'listing') return !!(r.short_description || r.category_id || (r.group_id && this.groups.length > 0));
                return false;
            },

            rowOpen(row) {
                return this.rowFixed(row) || this.openRow === row;
            },

            toggleRow(row) {
                if (this.rowFixed(row)) return;
                this.openRow = this.openRow === row ? null : row;
                if (this.openRow === 'description') {
                    // EasyMDE measures itself while hidden; let it measure again once shown.
                    this.$nextTick(() => {
                        const node = document.getElementById('submit_description');
                        if (node && node._easyMDE) node._easyMDE.codemirror.refresh();
                    });
                }
            },

            // --- who they are ---------------------------------------------------------------

            async checkEmailExists() {
                const email = (this.userEmail || '').trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.isAuthed || !emailRegex.test(email)) {
                    return;
                }
                // Two blur-triggered checks can resolve out of order; only the latest
                // request may update the form.
                const seq = ++this.emailCheckSeq;
                this.emailChecking = true;
                try {
                    // post(), not a bare fetch: if this never answers, Submit waits on it for good
                    // (pendingSubmit) with nothing on screen.
                    const response = await this.post('{{ route("event.check_email", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ email })
                    }, 15);
                    if (seq !== this.emailCheckSeq) {
                        return;
                    }
                    if (response.ok) {
                        const data = await response.json();
                        this.emailExists = data.exists;
                        this.emailStub = data.stub;
                        if (data.exists && !data.stub) {
                            this.accountMode = 'login';
                        } else {
                            this.accountMode = this.registrationEnabled ? 'register' : 'login';
                        }
                    }
                } catch (e) {
                    // Non-blocking: fall back to register mode on a failed check.
                } finally {
                    if (seq === this.emailCheckSeq) {
                        this.emailChecking = false;
                        // A submit that landed mid-check resumes instead of dead-clicking.
                        if (this.pendingSubmit) {
                            this.pendingSubmit = false;
                            this.submitEvent();
                        }
                    }
                }
            },

            openPageName() {
                this.showPageName = true;
                this.$nextTick(() => {
                    const el = document.getElementById('schedule_name');
                    if (el) el.focus();
                });
            },

            useAnotherEmail() {
                this.accountMode = this.registrationEnabled ? 'register' : 'login';
                this.emailExists = null;
                this.verificationCode = '';
                this.userPassword = '';
                this.$nextTick(() => {
                    const el = document.getElementById('account_email');
                    if (el) el.focus();
                });
            },

            // --- the flyer --------------------------------------------------------------------

            onFlyerSelected(e) {
                const file = e.target.files && e.target.files[0];
                e.target.value = '';
                if (file) this.setFlyer(file);
            },

            // dragenter/dragleave fire for every child crossed, so track depth instead of a boolean.
            flyerDragEnter() {
                this.flyerDragDepth++;
                this.flyerDragging = true;
            },

            flyerDragLeave() {
                this.flyerDragDepth = Math.max(0, this.flyerDragDepth - 1);
                if (this.flyerDragDepth === 0) this.flyerDragging = false;
            },

            flyerDrop(e) {
                this.flyerDragDepth = 0;
                this.flyerDragging = false;
                const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                if (file) {
                    this.setFlyer(file);
                    return;
                }
                const text = e.dataTransfer && e.dataTransfer.getData('text');
                if (text && this.aiEnabled) {
                    this.autoFillText = text;
                    this.showPaste = true;
                }
            },

            // An image on the clipboard is a flyer wherever on the form it is pasted. Text is
            // left to whichever field has the caret.
            onFormPaste(e) {
                const items = Array.from((e.clipboardData && e.clipboardData.items) || []);
                // Copying from a document puts the words AND a picture of them on the clipboard.
                // Those words are what was meant, so a picture counts only when it comes alone.
                if (items.some((item) => item.kind === 'string' && item.type === 'text/plain')) return;
                for (const item of items) {
                    if (item.kind === 'file' && item.type.startsWith('image/')) {
                        const file = item.getAsFile();
                        if (file) {
                            e.preventDefault();
                            this.setFlyer(file);
                            return;
                        }
                    }
                }
            },

            async setFlyer(file) {
                // The four kinds the server keeps. Saying so here spares an upload that would
                // only be refused (an .avif, an .svg, a phone's .heic).
                if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
                    this.flyerError = this.words.image_type;
                    return;
                }
                if (file.size > 2.5 * 1024 * 1024) {
                    this.flyerError = this.words.image_size;
                    return;
                }
                if (this.flyerBusy) return;
                this.flyerError = null;
                this.flyerNote = null;
                this.event.social_image = null;
                this.flyerPreviewUrl = null;
                const reader = new FileReader();
                reader.onload = (ev) => { this.flyerPreviewUrl = ev.target.result; };
                reader.readAsDataURL(file);

                // With an AI key the flyer is read as well as kept; reading stores it too. If the
                // reading fails for any reason the image is still uploaded, so it is never lost.
                let unread = null;
                if (this.aiEnabled) {
                    const filled = await this.runAutoFill(file);
                    if (filled) return;
                    unread = this.flyerError || this.words.flyer_not_read;
                }
                await this.uploadImage(file);
                // Kept, but not read: the tile promised to fill the form in, so it says it did not.
                if (unread && this.event.social_image) this.flyerNote = unread;
            },

            async uploadImage(file) {
                this.flyerBusy = true;
                try {
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('website', this.honeypot);
                    const response = await this.post('{{ route("event.guest_upload_image", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && data.success && data.filename) {
                        this.event.social_image = data.filename;
                        this.flyerError = null;
                    } else {
                        // The server's own words, which it writes in the visitor's language.
                        this.flyerError = (response.status === 429 || response.status === 419) ? this.refusalWords(response.status) : (data.message || this.words.image_error);
                        this.flyerPreviewUrl = null;
                    }
                } catch (error) {
                    this.flyerError = this.words.image_error;
                    this.flyerPreviewUrl = null;
                } finally {
                    this.flyerBusy = false;
                }
            },

            removeFlyer() {
                this.event.social_image = null;
                this.flyerPreviewUrl = null;
                this.flyerError = null;
                this.flyerNote = null;
                this.autoFillDone = false;
            },

            // Reads a flyer (file) or the pasted text (file === null) and fills in the form.
            // Only what the visitor has not typed: a flyer never overwrites their own words.
            async runAutoFill(file) {
                if (!file && !this.autoFillText.trim()) return false;
                this.flyerBusy = true;
                this.flyerError = null;
                try {
                    const formData = new FormData();
                    formData.append('event_details', file ? '' : this.autoFillText);
                    formData.append('website', this.honeypot);
                    if (file) formData.append('details_image', file);
                    const response = await this.post('{{ route("event.guest_parse", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });
                    let data = (await response.json().catch(() => null)) || {};
                    // On success the parse endpoint returns the parsed events as a bare array.
                    if (Array.isArray(data)) data = { parsed: data };
                    const p = data.parsed && data.parsed[0];
                    if (!p) {
                        // The parse endpoint reports failures (daily limit, parse error) under `error`.
                        this.flyerError = (response.status === 429 || response.status === 419) ? this.refusalWords(response.status) : (data.error || this.words.flyer_not_read);
                        return false;
                    }
                    // A second flyer replaces what the first one wrote. Left in place, the first
                    // one's details filled every field, so the replacement found nothing empty:
                    // the picture was the new one, every word the old one's, and Undo, whose
                    // memory had just been written over, took back nothing.
                    this.takeBackAutoFill();
                    this.beforeAutoFill = JSON.parse(JSON.stringify(this.event));
                    // "Form filled" only when something was: a flyer that says nothing the
                    // visitor has not already typed is kept as the picture and no more.
                    this.autoFillDone = this.applyParsed(p) > 0;
                    if (!this.autoFillDone) this.beforeAutoFill = null;
                    this.showPaste = false;
                    return true;
                } catch (e) {
                    this.flyerError = this.words.flyer_not_read;
                    return false;
                } finally {
                    this.flyerBusy = false;
                }
            },

            applyParsed(p) {
                const e = this.event;
                const before = JSON.parse(JSON.stringify(e));
                const empty = (v) => v === '' || v === null || v === undefined;
                // The model sometimes answers a postal code as a number; the form's text is text.
                const fill = (key, value) => { if (!empty(value) && empty(e[key])) e[key] = key === 'ticket_price' ? value : String(value); };

                fill('name', p.event_name);
                if (p.event_name && e.name === p.event_name) fill('name_en', p.event_name_en);

                // The parser returns one combined 'YYYY-MM-DD HH:MM' stamp, read as wall-clock. A
                // flyer with a day and no hour comes back as the day alone, and an hour is not
                // always two digits ("9:00"): requiring both filled neither the date nor the time.
                if (p.event_date_time) {
                    const m = /^(\d{4}-\d{2}-\d{2})(?:[ T](\d{1,2}):(\d{2}))?/.exec(String(p.event_date_time));
                    if (m) {
                        // Only a date the picker will show (minDate: today, in the browser's day).
                        const now = new Date();
                        const today = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
                        if (m[1] >= today && empty(e.event_date)) this.setDate(m[1]);
                        // To the minute: the half-hour list that rounded this is gone.
                        const start = m[2] === undefined ? null : parseInt(m[2], 10) * 60 + parseInt(m[3], 10);
                        if (start !== null && start < 1440 && parseInt(m[3], 10) < 60 && empty(e.event_start_time)) {
                            this.setTime('start', this.timeOf(start));
                            if (p.event_duration && empty(e.event_end_time)) {
                                this.setTime('end', this.timeOf(start + p.event_duration * 60));
                            }
                        }
                    }
                }

                fill('venue_name', p.venue_name);
                if (p.venue_name && e.venue_name === p.venue_name) fill('venue_name_en', p.venue_name_en);
                fill('venue_address1', p.event_address);
                fill('venue_city', p.event_city);
                fill('venue_state', p.event_state);
                fill('venue_postal_code', p.event_postal_code);
                if (p.event_country_code && e.venue_name === p.venue_name) e.venue_country_code = p.event_country_code;
                if (p.event_details && empty(e.description)) this.setDescription(p.event_details);
                fill('ticket_price', p.ticket_price);
                if (p.ticket_currency_code && e.ticket_price === p.ticket_price && this.currencies.some((c) => c.value === p.ticket_currency_code)) {
                    e.ticket_currency_code = p.ticket_currency_code;
                }
                fill('registration_url', p.registration_url);
                fill('short_description', p.short_description);
                if (p.short_description && e.short_description === p.short_description) fill('short_description_en', p.short_description_en);
                if (p.category_id && empty(e.category_id) && this.categories.some((c) => c.id === String(p.category_id))) {
                    e.category_id = String(p.category_id);
                }
                if (p.group_id && empty(e.group_id) && this.groups.some((g) => g.id === p.group_id)) {
                    e.group_id = p.group_id;
                }
                // A parsed flyer image doubles as the event image (the endpoint stores it and
                // returns the temp filename the save endpoint accepts).
                if (p.social_image) e.social_image = p.social_image;

                // Remember what the flyer wrote (Undo takes back only that, and only where it
                // still stands), and let those fields show themselves once they are drawn. The
                // answer is how many fields it wrote, the picture aside.
                const changed = Object.keys(e).filter((k) => e[k] !== before[k]);
                this.filled = {};
                changed.forEach((k) => { this.filled[k] = e[k]; });
                this.$nextTick(() => this.flash(changed));
                return changed.filter((k) => k !== 'social_image').length;
            },

            flash(keys) {
                const ids = {
                    name: 'submit_event_name', venue_name: 'submit_venue_name', venue_address1: 'submit_venue_address1',
                    venue_city: 'submit_venue_city', venue_state: 'submit_venue_state', venue_postal_code: 'submit_venue_postal_code',
                };
                const els = keys.map((k) => document.getElementById(ids[k])).filter(Boolean);
                if (keys.includes('event_date')) els.push(this.shownInput(this.pickers.date));
                if (keys.includes('event_start_time')) els.push(document.getElementById('submit_event_time'));
                if (keys.includes('event_end_time')) els.push(document.getElementById('submit_event_end_time'));
                els.filter(Boolean).forEach((el) => {
                    el.classList.remove('gs-filled');
                    void el.offsetWidth;
                    el.classList.add('gs-filled');
                });
            },

            // Takes back what a flyer wrote, where it still stands. Anything the visitor has
            // typed or changed since is theirs and stays; so does the picture. Used by Undo, and
            // by a second flyer before it writes (runAutoFill).
            takeBackAutoFill() {
                const before = this.beforeAutoFill, e = this.event;
                if (before) {
                    Object.keys(this.filled).forEach((key) => {
                        if (key === 'social_image' || e[key] !== this.filled[key]) return;
                        const was = before[key];
                        if (key === 'event_date') this.setDate(was || '');
                        else if (key === 'event_start_time') this.setTime('start', was || '');
                        else if (key === 'event_end_time') this.setTime('end', was || '');
                        else if (key === 'description') this.setDescription(was || '');
                        else e[key] = was;
                    });
                }
                this.filled = {};
                this.beforeAutoFill = null;
            },

            undoAutoFill() {
                this.takeBackAutoFill();
                this.autoFillDone = false;
            },

            setDescription(value) {
                this.event.description = value;
                const node = document.getElementById('submit_description');
                if (node && node._easyMDE) node._easyMDE.value(value);
            },

            // --- submit -----------------------------------------------------------------------

            async submitEvent() {
                if (this.saving || this.flyerBusy || this.codeSending) return;
                if (this.emailChecking) {
                    // Resumed from checkEmailExists' finally block once the check lands.
                    this.pendingSubmit = true;
                    return;
                }

                // Pull the description out of EasyMDE before validating (its change callback
                // usually keeps the model current, but be safe on direct submits).
                var descNode = document.getElementById('submit_description');
                if (descNode && descNode._easyMDE) {
                    this.event.description = descNode._easyMDE.value();
                }

                // A time still being typed is settled before it is judged.
                if (this.timeOpen) this.commitTime(this.timeOpen);

                this.serverErrors = {};
                this.barMessage = null;

                if (this.problems.length) {
                    this.triedSubmit = true;
                    this.step = 'form';
                    this.goTo(this.problems[0]);
                    return;
                }

                // A new account proves its address with an emailed code. That is asked for now,
                // on its own step, with everything else already known to be in order.
                if (this.needsCode && this.verificationCode.length !== 6) {
                    if (this.step !== 'code') {
                        if (await this.sendCode(false)) this.showCodeStep();
                    }
                    return;
                }

                this.saving = true;
                const e = this.event;
                const body = {
                    name: e.name,
                    starts_at: e.event_date + ' ' + e.event_start_time + ':00',
                    duration: this.durationHours(),
                    description: e.description,
                    social_image: e.social_image,
                    registration_url: this.cleanUrl(e.registration_url) || '',
                    ticket_price: e.ticket_price,
                    ticket_currency_code: e.ticket_currency_code,
                    short_description: e.short_description,
                    // The English the flyer reader wrote goes with the words it was written for,
                    // and not with whatever the visitor has since typed over them.
                    name_en: (this.filled.name === e.name && e.name_en) || null,
                    short_description_en: (this.filled.short_description === e.short_description && e.short_description_en) || null,
                    coupon_code: e.coupon_code,
                    coupon_discount: e.coupon_discount,
                    coupon_discount_type: e.coupon_discount_type,
                    category_id: e.category_id || null,
                    curator_group_id: e.group_id || null,
                    custom_field_values: this.customFieldValues,
                    selected_talent_id: this.selectedTalentId || null,
                    website: this.honeypot,
                };

                // Who they are is said by someone who is not signed in yet. After "Submit another"
                // the session says it, and the password is not posted a second time.
                if (!this.isAuthed) {
                    body.account_mode = this.accountMode;
                    body.account_email = this.userEmail;
                    body.account_password = this.userPassword;
                }

                if (e.is_online) {
                    body.event_url = this.cleanUrl(e.event_url);
                } else {
                    body.venue_name = e.venue_name;
                    body.venue_name_en = (this.filled.venue_name === e.venue_name && e.venue_name_en) || null;
                    body.venue_address1 = e.venue_address1;
                    body.venue_city = e.venue_city;
                    body.venue_state = e.venue_state;
                    body.venue_postal_code = e.venue_postal_code;
                    body.venue_country_code = e.venue_country_code;
                }

                // Whoever is about to get a page may name it: a new account, or someone signed in
                // who has none yet.
                if (this.needsPageName) body.schedule_name = this.scheduleName;

                if (!this.isAuthed && this.accountMode === 'register') {
                    body.account_name = this.userName;
                    body.verification_code = this.verificationCode;
                    // The box they ticked, sent: the server records when it was accepted.
                    body.terms = this.acceptedTerms;
                    // The new account should get the guest's timezone, not the curator's
                    // (the server falls back to the schedule's timezone without it).
                    try {
                        body.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || null;
                    } catch (err) { /* very old browsers */ }
                }

                try {
                    const response = await this.post('{{ route("event.guest_import.store", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify(body),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || !data.success) {
                        this.refused(response.status, data);
                        return;
                    }
                    this.sent = this.summary();
                    if (data.event.event_name) this.sent.name = data.event.event_name;
                    this.submissionResult = data.event;
                    // Adopt the submitter's real schedules from the response so "Submit
                    // another" shows the actual picker/identity.
                    if (Array.isArray(data.talents) && data.talents.length) {
                        this.talents = data.talents;
                        this.selectedTalentId = data.posted_as || data.talents[0].id;
                    }
                    this.submitted = true;
                    this.step = 'form';
                    // They are signed in now. Neither is needed again, so neither is kept.
                    this.userPassword = '';
                    this.verificationCode = '';
                    this.clearDraftStorage();
                    document.getElementById('gs-page').classList.remove('gs-coding');
                    document.getElementById('gs-page').classList.add('gs-done');
                    window.scrollTo({ top: 0, behavior: this.glide() });
                    this.$nextTick(() => {
                        const h = document.getElementById('submission-success-heading');
                        if (h) h.focus({ preventScroll: true });
                    });
                } catch (err) {
                    this.barMessage = this.words.error;
                    if (this.step === 'code') this.codeError = this.words.error;
                } finally {
                    this.saving = false;
                }
            },

            // A refusal is shown where it belongs: at the code box, at the field it names (the
            // row it is in opened, the page brought to it), or in the bar when it names none.
            refused(status, data) {
                const keys = {
                    name: 'name', starts_at: 'event_start_time', event_url: 'event_url', venue_name: 'venue_name',
                    description: 'description', ticket_price: 'ticket_price', registration_url: 'registration_url',
                    coupon_code: 'coupon_code', coupon_discount: 'coupon_code', short_description: 'short_description',
                    category_id: 'category_id', curator_group_id: 'group_id', account_email: 'account_email',
                    account_name: 'account_name', account_password: 'account_password', terms: 'terms',
                };
                const errors = data.errors || {};
                const mapped = {};
                let codeMessage = null, loose = null;
                Object.keys(errors).forEach((field) => {
                    const message = Array.isArray(errors[field]) ? errors[field][0] : String(errors[field]);
                    if (field === 'verification_code') codeMessage = message;
                    else if (keys[field]) mapped[keys[field]] = message;
                    else if (field.indexOf('custom_field_values.') === 0) mapped['cf_' + field.split('.')[1]] = message;
                    else if (!loose) loose = message;
                });

                if (codeMessage) {
                    this.verificationCode = '';
                    this.codeError = codeMessage;
                    this.step = 'code';
                    this.$nextTick(() => {
                        const el = document.getElementById('verification_code');
                        if (el) { el.value = ''; el.focus(); }
                    });
                    return;
                }

                this.step = 'form';
                document.getElementById('gs-page').classList.remove('gs-coding');
                this.verificationCode = '';
                if (Object.keys(mapped).length) {
                    this.serverErrors = mapped;
                    this.triedSubmit = true;
                    this.$nextTick(() => { if (this.problems.length) this.goTo(this.problems[0]); });
                    return;
                }
                // 419 is a page left open past its session: say something a person can act on.
                if (status === 419) this.expired = true;
                this.barMessage = (status === 429 || status === 419) ? this.refusalWords(status) : (loose || data.message || this.words.error);
                this.$nextTick(() => {
                    const bar = document.getElementById('submit-bar');
                    if (bar) { bar.scrollIntoView({ block: 'nearest', behavior: this.glide() }); const line = document.getElementById('submit-error-box'); if (line) line.focus({ preventScroll: true }); }
                });
            },

            summary() {
                const e = this.event;
                return {
                    name: e.name,
                    when: this.sentWhen(),
                    where: e.is_online ? this.rowsHost(e.event_url) : [e.venue_name, e.venue_city].filter(Boolean).join(', '),
                    image: this.flyerPreviewUrl,
                };
            },

            rowsHost(url) {
                try { return new URL(this.cleanUrl(url)).hostname.replace(/^www\./, ''); } catch (e) { return ''; }
            },

            // The date and time as the picker shows them, for the confirmation card.
            sentWhen() {
                const picker = this.pickers.date;
                const d = picker && picker.selectedDates[0] ? picker.formatDate(picker.selectedDates[0], 'M j, Y') : this.event.event_date;
                return [d, this.timeText.start].filter(Boolean).join(' · ');
            },

            // Shared by "Submit another" and the draft banner's "Start fresh". keepPlace leaves
            // the location in: the next event from the same person is usually in the same room.
            clearEventFields(keepPlace) {
                this.serverErrors = {};
                this.barMessage = null;
                this.triedSubmit = false;
                this.openRow = null;
                this.autoFillDone = false;
                this.beforeAutoFill = null;
                this.autoFillText = '';
                this.showPaste = false;
                this.flyerError = null;
                this.flyerNote = null;
                this.flyerPreviewUrl = null;
                const e = this.event;
                ['name', 'name_en', 'event_url', 'ticket_price', 'registration_url', 'short_description',
                    'short_description_en', 'coupon_code', 'coupon_discount', 'category_id', 'group_id'].forEach((k) => { e[k] = ''; });
                if (!keepPlace) {
                    ['venue_name', 'venue_name_en', 'venue_address1', 'venue_city', 'venue_state', 'venue_postal_code'].forEach((k) => { e[k] = ''; });
                    e.is_online = false;
                    e.venue_country_code = @json($role->country_code);
                    e.ticket_currency_code = @json($defaultCurrency);
                }
                e.social_image = null;
                this.event.coupon_discount_type = @json(\App\Models\Event::DEFAULT_COUPON_DISCOUNT_TYPE);
                this.setDate('');
                this.setTime('start', '');
                this.setTime('end', '');
                this.setDescription('');
            },

            resetForAnother() {
                this.submitted = false;
                this.submissionResult = null;
                this.clearEventFields(true);
                this.clearDraftStorage();
                document.getElementById('gs-page').classList.remove('gs-done');
                // The submitter is now authenticated and follows this schedule; the submit
                // response refreshed `talents` with their real schedules. Fabricate a named stub
                // only as a fallback (never an empty-name one).
                this.isAuthed = true;
                this.alreadyConnected = true;
                if (this.talents.length === 0) {
                    var fallbackName = (this.scheduleName || this.userName || '').trim();
                    if (fallbackName) {
                        this.talents = [{ id: '', name: fallbackName }];
                        this.selectedTalentId = '';
                    }
                }
                window.scrollTo({ top: 0, behavior: this.glide() });
                this.$nextTick(() => {
                    const el = document.getElementById('submit_event_name');
                    if (el) el.focus({ preventScroll: true });
                });
            },

            // --- Draft autosave -------------------------------------------------------
            // Best-effort recovery for guests who leave mid-form (e.g. switching to their
            // email app for the verification code on mobile).

            queueDraftSave() {
                if (this.submitted) return;
                clearTimeout(this.draftTimer);
                this.draftTimer = setTimeout(() => { this.saveDraft(); this.saveSession(); }, 500);
            },

            saveDraft() {
                try {
                    const e = this.event;
                    // The place alone is not a draft: "Submit another" leaves it in on purpose.
                    const hasContent = !!((e.name || '').trim() || e.event_date || e.event_start_time
                        || (e.event_url || '').trim() || (e.description || '').trim() || (e.short_description || '').trim()
                        || e.ticket_price || (e.registration_url || '').trim() || (e.coupon_code || '').trim() || e.coupon_discount);
                    if (!hasContent) {
                        // Clearing the form (Start fresh / Submit another) dissolves the draft.
                        localStorage.removeItem(this.draftKey);
                        return;
                    }
                    const draft = Object.assign({}, e);
                    // The temp upload may be gone by the time they return; never restore it.
                    delete draft.social_image;
                    localStorage.setItem(this.draftKey, JSON.stringify({
                        v: 2,
                        savedAt: Date.now(),
                        event: draft,
                        openRow: this.openRow,
                    }));
                } catch (err) {
                    // Storage full or blocked - drafts are best-effort.
                }
            },

            // Who they are and what they answered, for this tab only, and never the password. It
            // is what brings someone back from their mail app, or from Google, to a form that
            // still knows them.
            saveSession() {
                if (this.submitted) return;
                try {
                    sessionStorage.setItem(this.sessionKey, JSON.stringify({
                        v: 1,
                        email: this.isAuthed ? '' : this.userEmail,
                        name: this.isAuthed ? '' : this.userName,
                        page: this.scheduleName,
                        answers: this.customFieldValues,
                        codeEmail: this.codeEmail,
                        codeSentAt: this.codeSentAt,
                    }));
                } catch (err) { /* storage blocked */ }
            },

            restoreSession() {
                try {
                    const raw = sessionStorage.getItem(this.sessionKey);
                    if (!raw) return;
                    const s = JSON.parse(raw);
                    if (!s || s.v !== 1) return;
                    if (!this.isAuthed) {
                        if (s.email) this.userEmail = s.email;
                        if (s.name) this.userName = s.name;
                        if (s.email) this.checkEmailExists();
                    }
                    if (s.page) this.scheduleName = s.page;
                    // Only answers to questions the schedule still asks, in the shape it asks them.
                    this.requestCustomFields.forEach((field) => {
                        const value = (s.answers || {})[field.key];
                        if (value === undefined || value === null) return;
                        if (field.type === 'multiselect') {
                            if (Array.isArray(value)) this.customFieldValues[field.key] = value.filter((v) => field.options.includes(v));
                        } else if (field.type === 'dropdown') {
                            if (field.options.includes(value)) this.customFieldValues[field.key] = value;
                        } else if (field.type === 'date') {
                            if (/^\d{4}-\d{2}-\d{2}$/.test(value)) this.customFieldValues[field.key] = value;
                        } else if (typeof value === 'string') {
                            this.customFieldValues[field.key] = value;
                        }
                    });
                    this.showRestoredDates();
                    this.codeEmail = s.codeEmail || '';
                    this.codeSentAt = s.codeSentAt || 0;
                } catch (err) { /* corrupt or blocked - start clean */ }
            },

            restoreDraft() {
                try {
                    const raw = localStorage.getItem(this.draftKey);
                    if (!raw) return;
                    const draft = JSON.parse(raw);
                    if (!draft || (draft.v !== 1 && draft.v !== 2) || !draft.event) return;
                    const maxAge = 7 * 24 * 60 * 60 * 1000;
                    if (!draft.savedAt || (Date.now() - draft.savedAt) > maxAge) {
                        localStorage.removeItem(this.draftKey);
                        return;
                    }

                    const e = draft.event;
                    const fields = ['name', 'is_online', 'venue_name',
                        'venue_address1', 'venue_city', 'venue_state', 'venue_postal_code', 'venue_country_code',
                        'event_url', 'ticket_price', 'ticket_currency_code', 'registration_url',
                        'short_description', 'coupon_code', 'coupon_discount', 'coupon_discount_type'];
                    fields.forEach((f) => {
                        if (e[f] !== undefined && e[f] !== null && e[f] !== '') this.event[f] = e[f];
                    });
                    // Selects only accept known values (curator config may have changed).
                    if (e.category_id && this.categories.some((c) => c.id === String(e.category_id))) this.event.category_id = String(e.category_id);
                    if (e.group_id && this.groups.some((g) => g.id === e.group_id)) this.event.group_id = e.group_id;
                    if (e.ticket_currency_code && !this.currencies.some((c) => c.value === e.ticket_currency_code)) {
                        this.event.ticket_currency_code = @json($defaultCurrency);
                    }
                    // Only restore a date the picker will display (minDate: today, local).
                    var now = new Date();
                    var todayLocal = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
                    if (e.event_date && e.event_date >= todayLocal) this.setDate(e.event_date);
                    if (this.minutesOf(e.event_start_time) !== null) this.setTime('start', e.event_start_time);
                    if (this.minutesOf(e.event_end_time) !== null) this.setTime('end', e.event_end_time);
                    if (e.description) this.setDescription(e.description);
                    if (draft.openRow) this.openRow = draft.openRow;
                    this.draftRestored = true;
                } catch (err) {
                    // Corrupt or blocked storage - start clean.
                }
            },

            clearDraftStorage() {
                clearTimeout(this.draftTimer);
                try {
                    localStorage.removeItem(this.draftKey);
                    sessionStorage.removeItem(this.sessionKey);
                } catch (err) { /* storage blocked */ }
            },

            reloadPage() {
                // The draft is written on a short delay; write it now, so the last thing typed is not lost.
                this.saveDraft();
                this.saveSession();
                window.location.reload();
            },

            startFresh() {
                this.clearDraftStorage();
                this.draftRestored = false;
                this.clearEventFields(false);
            },
        },
    });

    window.__submitApp = app.mount('#event-submit-app[data-vue-root]');
  </script>
