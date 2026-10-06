{{-- The Vue app behind event/booking-request.blade.php. Included from it, so it shares that view's
     variables. The time boxes, the clock note, the bar and the emailed-code step come from
     partials/request-form-kit; this file is the booking form's own fields, problems and submit. --}}
  <script {!! nonce_attr() !!}>
    const { createApp } = Vue;

    const app = createApp({
        mixins: [window.RequestFormKit],

        data() {
            return {
                event: {
                    name: '',
                    event_date: '',
                    event_start_time: '',
                    event_end_time: '',
                    description: '',
                    // A venue schedule is always "in person": it is the place.
                    in_person: true,
                    is_online: false,
                    event_url: '',
                    venue_name: '',
                    venue_address1: '',
                    venue_city: '',
                    venue_state: '',
                    venue_postal_code: '',
                },
                words: @json($words),
                labels: @json($labels),
                required: @json($requiredFields),
                isVenue: {{ $role->isVenue() ? 'true' : 'false' }},
                allowOnline: {{ $allowOnline ? 'true' : 'false' }},
                askPhone: {{ $askPhone ? 'true' : 'false' }},
                // Times typed into this form are read as the schedule's local time.
                scheduleTimezone: @json($role->timezone),
                deviceTimezone: '',
                use24hr: {{ $use24hr ? 'true' : 'false' }},
                requestCustomFields: @json($requestCustomFields),
                customFieldValues: @json((object) $requestCustomFieldValues),
                pickers: { date: null, custom: {} },
                timeText: { start: '', end: '' },
                timeBad: { start: false, end: false },
                timeOpen: null,
                timeHighlight: -1,
                isAuthed: {{ auth()->check() ? 'true' : 'false' }},
                mustHaveAccount: {{ $mustHaveAccount ? 'true' : 'false' }},
                cannotSend: {{ $cannotSend ? 'true' : 'false' }},
                // The emailed code is asked of a new account where sign-up asks it: hosted.
                requiresCode: {{ $needsCode ? 'true' : 'false' }},
                turnstileEnabled: {{ \App\Utils\TurnstileUtils::isEnabled() ? 'true' : 'false' }},
                turnstileSiteKey: @json(\App\Utils\TurnstileUtils::getSiteKey()),
                turnstileToken: '',
                turnstileWidgetId: null,
                turnstileTries: 0,
                // Which request page is asking for a code: each page's funnel counts its own.
                formKind: 'booking',
                // 'register' while "Create an account" is on; the code step reads this.
                // Encoded, not echoed: Blade escapes a quote, and this sits inside a script.
                accountMode: @json($mustHaveAccount && $offerAccount ? 'register' : 'guest'),
                createAccount: {{ $mustHaveAccount && $offerAccount ? 'true' : 'false' }},
                userName: '',
                userEmail: '',
                userPhone: '',
                userPassword: '',
                acceptedTerms: false,
                showPassword: false,
                emailExists: null,
                emailStub: false,
                verificationCode: '',
                step: 'form',
                codeSending: false,
                codeEmail: '',
                codeSentAt: 0,
                codeError: null,
                codeFocused: false,
                codeFrom: 0,
                resendCountdown: 0,
                resendTimer: null,
                honeypot: '',
                submitted: false,
                submissionResult: null,
                sent: { name: '', when: '', where: '', email: '' },
                serverErrors: {},
                barMessage: null,
                saving: false,
                triedSubmit: false,
                // The page was left open past its session: nothing can be sent until it is reloaded.
                expired: false,
                draftKey: 'es_booking_request_draft_' + @json($role->subdomain),
                sessionKey: 'es_booking_request_you_' + @json($role->subdomain),
                draftRestored: false,
                draftTimer: null,
            }
        },

        mounted() {
            try {
                this.deviceTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            } catch (e) { /* very old browsers */ }

            this.$nextTick(() => {
                document.getElementById('event-submit-app').classList.add('loaded');
                if (window.innerWidth >= 640) {
                    var nameEl = document.getElementById('submit_event_name');
                    if (nameEl) nameEl.focus({ preventScroll: true });
                }
                this.maybeRenderTurnstile();
                this.publishBarHeight();
                window.addEventListener('resize', () => this.publishBarHeight());
            });

            // Flatpickr and EasyMDE live in the deferred Vite bundle, which runs after this
            // parse-time script: wire them once the document is ready.
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
                            // A request is for a day that has not happened yet.
                            minDate: 'today',
                            onChange: function(selectedDates, dateStr) { self.event.event_date = dateStr; },
                        }, localeConfig));
                    }
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
                    hint(self.pickers.date, 'submit_event_date_label', self.required.date_time);
                    // The label is for the box that shows: Flatpickr hides the one it was written
                    // for behind a copy, and a tap on "Date" then went nowhere.
                    var shownDate = self.shownInput(self.pickers.date);
                    var dateLabel = document.getElementById('submit_event_date_label');
                    if (shownDate && dateLabel && shownDate !== dateEl) {
                        shownDate.id = 'submit_event_date_shown';
                        dateLabel.setAttribute('for', shownDate.id);
                    }

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

                var descEl = document.getElementById('submit_description');
                if (descEl && !descEl._easyMDE && window.initTinyMDE) {
                    window.initTinyMDE(descEl, function() {
                        if (descEl._easyMDE) self.event.description = descEl._easyMDE.value();
                    });
                }
                self.nameTheEditor();

                // The country picker is a library control; it is given its inputs once they exist.
                if (window.initCountryInput && document.getElementById('venue_country_code')) {
                    window.initCountryInput('venue_country_code', document.getElementById('venue_country_code').value || 'us');
                    var wrap = document.querySelector('#gs-page .iti--country-only');
                    var ref = document.getElementById('submit_venue_name');
                    if (wrap && ref && ref.offsetHeight > 30) wrap.style.setProperty('height', ref.offsetHeight + 'px', 'important');
                    var button = wrap ? wrap.querySelector('.iti__selected-country') : null;
                    if (button) button.setAttribute('aria-labelledby', 'venue_country_label');
                }

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
            // No touched() here: this box is also switched off by the page itself, when the code
            // endpoint says the address has an account, and what it said must stay on screen.
            createAccount(on) {
                this.accountMode = on ? 'register' : 'guest';
                if (on) this.$nextTick(() => this.maybeRenderTurnstile());
            },
            // The code endpoint answers "this address has an account" by switching the page to
            // sign-in (the kit sets emailExists with it). This form has no sign-in of its own: the
            // account option is put away, and the note under the address says why and what to do.
            accountMode(mode) {
                if (mode === 'login') {
                    this.createAccount = this.mustHaveAccount;
                    this.accountMode = this.mustHaveAccount ? 'register' : 'guest';
                    this.barMessage = null;
                }
            },
            problemKeys() { this.paintPickers(); this.nameTheEditor(); },
            triedSubmit() { this.paintPickers(); this.nameTheEditor(); },
            submitted() { this.publishBarHeight(); },
            step() { this.publishBarHeight(); },
            event: { deep: true, handler() { this.touched(); this.queueDraftSave(); } },
            customFieldValues: { deep: true, handler() { this.touched(); this.queueDraftSave(); } },
            userEmail() { this.emailExists = null; this.touched(); this.queueDraftSave(); },
            userName() { this.touched(); this.queueDraftSave(); },
            userPhone() { this.touched(); this.queueDraftSave(); },
            userPassword() { this.touched(); },
            acceptedTerms() { this.touched(); },
        },

        computed: {
            // Has the visitor begun? Until then the bar is two buttons and says nothing.
            started() {
                const e = this.event;
                return !!((e.name || '').trim() || e.event_date || e.event_start_time || (e.description || '').trim()
                    || (e.venue_name || '').trim() || (e.event_url || '').trim() || (this.userEmail || '').trim() || (this.userName || '').trim());
            },

            needsCode() {
                return !this.isAuthed && this.createAccount && this.requiresCode;
            },

            // A place: any venue detail while In-person is on, or Online where the form offers it.
            locationGiven() {
                const e = this.event;
                const venue = e.in_person && ['venue_name', 'venue_address1', 'venue_city'].some((k) => (e[k] || '').trim() !== '');
                return venue || (this.allowOnline && e.is_online);
            },

            // One entry per thing that stops a send, in the order the page shows them.
            problems() {
                const e = this.event, w = this.words, r = this.required;
                const list = [];
                const add = (key, id, message) => list.push({ key, id, message, row: null, label: this.labelOf(key) });

                if (r.event_name && !(e.name || '').trim()) add('name', 'submit_event_name', w.required);

                // A date is only kept together with a time, so each asks for the other.
                const hasDate = !!e.event_date, hasTime = !!e.event_start_time;
                if ((r.date_time || hasTime) && !hasDate) add('event_date', 'submit_event_date', w.required);
                if (this.timeBad.start) add('event_start_time', 'submit_event_time', this.timeHelp());
                else if ((r.date_time || hasDate) && !hasTime) add('event_start_time', 'submit_event_time', w.required);
                if (this.timeBad.end) add('event_end_time', 'submit_event_end_time', this.timeHelp());
                else if (e.event_end_time && !hasTime) add('event_start_time', 'submit_event_time', w.required);

                if (r.description && !(e.description || '').trim()) add('description', 'submit_description', w.required);

                if (r.location && !this.isVenue && !this.locationGiven) {
                    add('location', e.in_person || !this.allowOnline ? 'submit_venue_name' : 'is_online', w.location);
                }
                if (this.allowOnline && e.is_online && (e.event_url || '').trim() && !this.cleanUrl(e.event_url)) {
                    add('event_url', 'submit_event_url', w.valid_url);
                }

                this.requestCustomFields.forEach((field) => {
                    const key = 'cf_' + field.key, id = 'submit_custom_field_' + field.key;
                    if (this.customFieldIsMissing(field)) {
                        list.push({ key, id, message: w.required, row: null, label: field.label });
                    } else if (this.customFieldFailsPattern(field)) {
                        list.push({ key, id, message: field.regex_hint || w.required, row: null, label: field.label });
                    }
                });

                if (!this.isAuthed) {
                    if (!(this.userName || '').trim()) add('account_name', 'account_name', w.required);
                    const email = (this.userEmail || '').trim();
                    if (!email) add('account_email', 'account_email', w.required);
                    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) add('account_email', 'account_email', w.valid_email);
                    // Where an account is the only way through, an address that has one already is a stop.
                    else if (this.emailExists && this.mustHaveAccount) add('account_email', 'account_email', w.has_account);
                }
                if (this.askPhone && r.phone && !(this.userPhone || '').trim()) add('contact_phone', 'contact_phone', w.required);
                if (!this.isAuthed && this.createAccount) {
                    if (!this.userPassword) add('account_password', 'account_password', w.required);
                    else if (this.userPassword.length < 8) add('account_password', 'account_password', w.password_min);
                    if (!this.acceptedTerms) add('terms', 'account_terms', w.required);
                }

                // What the server refused on the last attempt, where the page has not caught it.
                Object.keys(this.serverErrors).forEach((key) => {
                    if (!list.some((p) => p.key === key)) {
                        const meta = this.fieldMeta(key);
                        list.push({ key, id: meta.id, message: this.serverErrors[key], row: null, label: meta.label });
                    }
                });

                // In the order the page shows them, whichever side found them: the bar names the
                // first three, and the first is where the page goes.
                const order = ['name', 'event_date', 'event_start_time', 'event_end_time', 'description', 'location', 'event_url']
                    .concat(this.requestCustomFields.map((f) => 'cf_' + f.key))
                    .concat(['account_name', 'account_email', 'contact_phone', 'create_account', 'account_password', 'terms']);
                const place = (p) => { const i = order.indexOf(p.key); return i === -1 ? order.length : i; };
                return list.map((p, i) => ({ p, i })).sort((a, b) => (place(a.p) - place(b.p)) || (a.i - b.i)).map((x) => x.p);
            },
        },

        methods: {
            labelOf(key) {
                return this.labels[key] || key;
            },

            // Where a field the server named lives on this page.
            fieldMeta(key) {
                const ids = {
                    name: 'submit_event_name', event_date: 'submit_event_date', event_start_time: 'submit_event_time', event_end_time: 'submit_event_end_time',
                    description: 'submit_description', location: 'submit_venue_name', event_url: 'submit_event_url', account_name: 'account_name',
                    account_email: 'account_email', contact_phone: 'contact_phone', account_password: 'account_password', terms: 'account_terms',
                    create_account: 'create_account',
                };
                if (key.indexOf('cf_') === 0) {
                    const field = this.requestCustomFields.find((f) => 'cf_' + f.key === key);
                    return { id: 'submit_custom_field_' + key.slice(3), label: field ? field.label : key };
                }
                return { id: ids[key] || null, label: this.labelOf(key) };
            },

            // The description is typed into EasyMDE's own box, which knows nothing of the textarea
            // it stands in for: hand it the name, whether an answer is needed, and its message.
            nameTheEditor() {
                const node = document.getElementById('submit_description');
                if (!node || !node._easyMDE) return;
                const input = node._easyMDE.codemirror.getInputField();
                ['aria-label', 'aria-required'].forEach((name) => { if (node.hasAttribute(name)) input.setAttribute(name, node.getAttribute(name)); });
                const message = this.msg('description');
                input.setAttribute('aria-invalid', message ? 'true' : 'false');
                message ? input.setAttribute('aria-describedby', 'err_description') : input.removeAttribute('aria-describedby');
            },

            setDescription(value) {
                this.event.description = value;
                const node = document.getElementById('submit_description');
                if (node && node._easyMDE) node._easyMDE.value(value);
            },

            reloadPage() {
                // The draft is written on a short delay; write it now, so nothing typed in the last moment is lost.
                this.saveDraft();
                this.saveSession();
                window.location.reload();
            },

            // From the code step: send the request as it is, with no account made.
            sendWithoutAccount() {
                this.createAccount = false;
                this.verificationCode = '';
                this.step = 'form';
                document.getElementById('gs-page').classList.remove('gs-coding');
                this.$nextTick(() => this.submitEvent());
            },

            // Only what was answered: a question left alone is not sent as an empty answer.
            answers() {
                const out = {};
                this.requestCustomFields.forEach((field) => {
                    const value = this.customFieldValues[field.key];
                    if (Array.isArray(value) ? value.length : (value !== '' && value !== null && value !== undefined)) out[field.key] = value;
                });
                return out;
            },

            country() {
                const el = document.getElementById('venue_country_code');
                return el ? el.value : null;
            },

            async submitEvent() {
                if (this.saving || this.codeSending) return;

                var descNode = document.getElementById('submit_description');
                if (descNode && descNode._easyMDE) this.event.description = descNode._easyMDE.value();
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

                // A new account proves its address with an emailed code, asked for now, on its own
                // step, with everything else already known to be in order.
                if (this.needsCode && this.verificationCode.length !== 6) {
                    if (this.step !== 'code') {
                        if (await this.sendCode(false)) this.showCodeStep();
                    }
                    return;
                }

                this.saving = true;
                const e = this.event;
                const body = {
                    event_name: e.name,
                    date: e.event_date || null,
                    start_time: e.event_start_time || null,
                    end_time: e.event_start_time ? (e.event_end_time || null) : null,
                    description: e.description,
                    is_online: !!(this.allowOnline && e.is_online),
                    custom_field_values: this.answers(),
                    website: this.honeypot,
                };
                if (body.is_online) body.event_url = this.cleanUrl(e.event_url) || '';
                // What was typed under In-person is sent only while In-person is on.
                if (!this.isVenue && e.in_person) {
                    body.venue_name = e.venue_name;
                    body.venue_address1 = e.venue_address1;
                    body.venue_city = e.venue_city;
                    body.venue_state = e.venue_state;
                    body.venue_postal_code = e.venue_postal_code;
                    body.venue_country_code = this.country();
                }
                if (this.askPhone) body.contact_phone = this.userPhone;
                if (!this.isAuthed) {
                    body.contact_name = this.userName;
                    body.contact_email = this.userEmail;
                    if (this.createAccount) {
                        body.create_account = true;
                        body.password = this.userPassword;
                        body.terms = this.acceptedTerms;
                        body.verification_code = this.verificationCode;
                        try {
                            body.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || null;
                        } catch (err) { /* very old browsers */ }
                    }
                }

                try {
                    const response = await this.post('{{ route("event.booking_request.store", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(body),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || !data.success) {
                        this.refused(response.status, data);
                        return;
                    }
                    this.sent = this.summary();
                    this.submissionResult = { status: data.status || 'pending', emails_you: !!data.emails_you };
                    this.submitted = true;
                    this.step = 'form';
                    // Neither is needed again, so neither is kept.
                    this.userPassword = '';
                    this.verificationCode = '';
                    if (this.createAccount) this.isAuthed = true;
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

            // A refusal is shown where it belongs: at the code box, at the field it names, or in
            // the bar when it names none.
            refused(status, data) {
                const keys = {
                    event_name: 'name', date: 'event_date', start_time: 'event_start_time', end_time: 'event_end_time', description: 'description',
                    location: 'location', venue_name: 'location', venue_address1: 'location', venue_city: 'location', venue_state: 'location',
                    venue_postal_code: 'location', venue_country_code: 'location', event_url: 'event_url', contact_name: 'account_name', account_name: 'account_name',
                    contact_email: 'account_email', account_email: 'account_email', contact_phone: 'contact_phone', password: 'account_password',
                    account_password: 'account_password', terms: 'terms', create_account: 'create_account',
                };
                const errors = data.errors || {};
                const mapped = {};
                let codeMessage = null, loose = null;
                Object.keys(errors).forEach((field) => {
                    const message = Array.isArray(errors[field]) ? errors[field][0] : String(errors[field]);
                    if (field === 'verification_code') codeMessage = message;
                    else if (keys[field]) mapped[keys[field]] = mapped[keys[field]] || message;
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
                if (status === 419) this.expired = true;
                this.barMessage = (status === 429 || status === 419) ? this.refusalWords(status) : (loose || data.message || data.error || this.words.error);
                this.$nextTick(() => {
                    const line = document.getElementById('submit-error-box');
                    if (line) line.focus({ preventScroll: true });
                });
            },

            summary() {
                const e = this.event;
                const place = this.isVenue ? '' : (e.in_person ? [e.venue_name, e.venue_city].filter(Boolean).join(', ') : '');
                return {
                    name: (e.name || '').trim() || @json($role->isTalent() ? __('messages.booking_request') : __('messages.submit_event')),
                    when: this.sentWhen(),
                    where: [place, (this.allowOnline && e.is_online) ? @json(__('messages.online')) : ''].filter(Boolean).join(' · '),
                    email: this.userEmail,
                };
            },

            sentWhen() {
                const picker = this.pickers.date;
                const d = picker && picker.selectedDates[0] ? picker.formatDate(picker.selectedDates[0], 'M j, Y') : this.event.event_date;
                let times = [this.timeText.start, this.timeText.end].filter(Boolean).join(' - ');
                // Whose clock, where the visitor's own reads differently.
                if (times && this.timezoneMismatch) times += ' (' + this.zoneName(this.scheduleTimezone) + ')';
                return [d, times].filter(Boolean).join(' · ');
            },

            clearEventFields() {
                const e = this.event;
                e.name = '';
                this.setDate('');
                this.setTime('start', '');
                this.setTime('end', '');
                this.setDescription('');
                e.event_url = '';
                e.is_online = false;
                // By the kind of question, never by the look of the answer: "1" typed into a text box is not a switch.
                this.requestCustomFields.forEach((field) => {
                    this.customFieldValues[field.key] = field.type === 'multiselect' ? [] : (field.type === 'switch' ? '0' : '');
                });
                Object.keys(this.pickers.custom).forEach((key) => this.pickers.custom[key].clear(false));
                this.triedSubmit = false;
                this.serverErrors = {};
                this.barMessage = null;
            },

            // "Send another" keeps the place and the person: the next request is usually theirs too.
            resetForAnother() {
                this.submitted = false;
                this.submissionResult = null;
                this.clearEventFields();
                this.clearDraftStorage();
                document.getElementById('gs-page').classList.remove('gs-done');
                window.scrollTo({ top: 0, behavior: this.glide() });
                this.$nextTick(() => {
                    const el = document.getElementById('submit_event_name');
                    if (el) el.focus({ preventScroll: true });
                });
            },

            // --- what is kept --------------------------------------------------------------
            // The request, in this browser, until it is sent. Who is asking and what they answered,
            // for this tab only. Never the password.
            queueDraftSave() {
                if (this.submitted) return;
                clearTimeout(this.draftTimer);
                this.draftTimer = setTimeout(() => { this.saveDraft(); this.saveSession(); }, 500);
            },

            saveDraft() {
                try {
                    const e = this.event;
                    const hasContent = !!((e.name || '').trim() || e.event_date || e.event_start_time || (e.description || '').trim()
                        || (e.venue_name || '').trim() || (e.venue_address1 || '').trim() || (e.venue_city || '').trim() || (e.event_url || '').trim());
                    if (!hasContent) {
                        localStorage.removeItem(this.draftKey);
                        return;
                    }
                    localStorage.setItem(this.draftKey, JSON.stringify({ v: 1, savedAt: Date.now(), event: Object.assign({}, e), country: this.country() }));
                } catch (err) { /* storage full or blocked - best effort */ }
            },

            saveSession() {
                if (this.submitted) return;
                try {
                    sessionStorage.setItem(this.sessionKey, JSON.stringify({
                        v: 1,
                        email: this.isAuthed ? '' : this.userEmail,
                        name: this.isAuthed ? '' : this.userName,
                        phone: this.userPhone,
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
                    }
                    if (s.phone && this.askPhone) this.userPhone = s.phone;
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
                    // A request left for more than a week is not the one they came back for.
                    if (!draft || draft.v !== 1 || !draft.event || (Date.now() - (draft.savedAt || 0)) > 7 * 24 * 3600 * 1000) {
                        localStorage.removeItem(this.draftKey);
                        return;
                    }
                    const d = draft.event, e = this.event;
                    ['name', 'venue_name', 'venue_address1', 'venue_city', 'venue_state', 'venue_postal_code', 'event_url'].forEach((k) => { if (typeof d[k] === 'string') e[k] = d[k]; });
                    if (typeof d.in_person === 'boolean' && !this.isVenue) e.in_person = d.in_person;
                    if (typeof d.is_online === 'boolean' && this.allowOnline) e.is_online = d.is_online;
                    // With Online switched off there is no box to bring In-person back with: a draft
                    // from before the owner changed that must not leave the venue fields hidden for good.
                    if (!this.allowOnline) e.in_person = true;
                    // Only a day the picker will still show.
                    const now = new Date();
                    const today = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
                    if (/^\d{4}-\d{2}-\d{2}$/.test(d.event_date || '') && d.event_date >= today) this.setDate(d.event_date);
                    if (this.minutesOf(d.event_start_time) !== null) this.setTime('start', d.event_start_time);
                    if (this.minutesOf(d.event_end_time) !== null) this.setTime('end', d.event_end_time);
                    if (typeof d.description === 'string' && d.description) this.setDescription(d.description);
                    if (draft.country && window._countryInputs && window._countryInputs.venue_country_code) window._countryInputs.venue_country_code.setCountry(draft.country);
                    this.draftRestored = true;
                } catch (err) { /* corrupt or blocked - start clean */ }
            },

            clearDraftStorage() {
                clearTimeout(this.draftTimer);
                try {
                    localStorage.removeItem(this.draftKey);
                    sessionStorage.removeItem(this.sessionKey);
                } catch (err) { /* storage blocked */ }
            },

            startFresh() {
                this.clearDraftStorage();
                this.draftRestored = false;
                this.clearEventFields();
                const e = this.event;
                ['venue_name', 'venue_address1', 'venue_city', 'venue_state', 'venue_postal_code'].forEach((k) => { e[k] = ''; });
            },
        },
    });

    window.__submitApp = app.mount('#event-submit-app');
  </script>
