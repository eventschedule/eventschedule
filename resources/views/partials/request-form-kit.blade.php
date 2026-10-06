{{-- What the public request pages share in script (the submit page and the booking form), as one
     Vue mixin: the type-or-pick time boxes, the note about whose clock a time is read on, the bar
     that says what is still needed, the emailed-code step, and the small readers both use. Each
     page keeps its own fields, its own list of problems and its own submit. Included before the
     page's script; it reads $role for the code endpoint.

     A page that uses it provides: event.event_date / event_start_time / event_end_time, timeText,
     timeBad, timeOpen, timeHighlight, pickers ({ date, custom }), words, problems, started,
     triedSubmit, serverErrors, barMessage, saving, submitted, step, and for the code step
     userEmail, honeypot, accountMode, requiresCode, isAuthed, codeEmail, codeSentAt, codeError,
     codeFocused, codeFrom, codeSending, resendCountdown, resendTimer, verificationCode, the
     turnstile fields, summary(), submitEvent() and saveSession(). --}}
  <script {!! nonce_attr() !!}>
    window.RequestFormKit = {
        computed: {
            // The clock reading of each zone at the event's date (or now), in minutes from UTC.
            // Names are not compared: Toronto and New York, or Calcutta and Kolkata, are one clock.
            timezoneMismatch() {
                if (!this.scheduleTimezone || !this.deviceTimezone) return false;
                const at = this.event.event_date ? new Date(this.event.event_date + 'T12:00:00Z') : new Date();
                const here = this.zoneOffset(this.deviceTimezone, at);
                const there = this.zoneOffset(this.scheduleTimezone, at);
                return here !== null && there !== null && here !== there;
            },

            timezoneMismatchMessage() {
                return this.words.timezone_mismatch
                    .replace(':from', this.zoneName(this.deviceTimezone))
                    .replace(':to', this.zoneName(this.scheduleTimezone));
            },

            // "New York (GMT-4)" rather than "America/New_York".
            timezoneLabel() {
                const zone = this.scheduleTimezone;
                if (!zone) return '';
                const at = this.event.event_date ? new Date(this.event.event_date + 'T12:00:00Z') : new Date();
                let offset = '';
                try {
                    const part = new Intl.DateTimeFormat('en-US', { timeZone: zone, timeZoneName: 'shortOffset' })
                        .formatToParts(at).find((p) => p.type === 'timeZoneName');
                    offset = part ? part.value : '';
                } catch (e) { /* older browsers: the name alone */ }
                return this.zoneName(zone) + (offset ? ' (' + offset + ')' : '');
            },

            // Half hours to pick from. For the end time they begin just after the start; what is
            // being typed narrows them.
            timeChoices() {
                const which = this.timeOpen;
                if (!which) return [];
                const from = which === 'end' && this.minutesOf(this.event.event_start_time) !== null
                    ? Math.ceil((this.minutesOf(this.event.event_start_time) + 1) / 30) * 30
                    : 0;
                const all = [];
                for (let i = 0; i < 48; i++) {
                    const m = (from + i * 30) % 1440;
                    all.push({ value: this.timeOf(m), label: this.formatTime(m) });
                }
                const typed = (this.timeText[which] || '').toLowerCase().replace(/\s+/g, '');
                if (!typed || this.parseTime(this.timeText[which]) !== null) return all;
                return all.filter((o) => o.label.toLowerCase().replace(/\s+/g, '').indexOf(typed) === 0);
            },

            endsNextDay() {
                const s = this.minutesOf(this.event.event_start_time);
                const e = this.minutesOf(this.event.event_end_time);
                return s !== null && e !== null && e < s;
            },

            problemKeys() {
                return this.problems.map((p) => p.key).join(',');
            },

            // What the bar says: the refusal, by name; or that it is ready; or what Submit does.
            bar() {
                if (this.barMessage) return { kind: 'bad', items: [], text: this.barMessage };
                // A flyer being read holds Submit for a moment; the bar says why.
                if (this.flyerBusy) return { kind: 'todo', items: [], text: this.words.processing };
                if (this.triedSubmit && this.problems.length) {
                    const all = this.problems;
                    // "Still needed" for what is empty; "Check" for what is there and wrong.
                    const missing = all.every((p) => p.message === this.words.required);
                    return {
                        kind: 'bad',
                        lead: (missing ? this.words.still_needed : this.words.check) + ' ',
                        items: all.slice(0, 3),
                        more: Math.max(0, all.length - 3),
                        next: all[3] || null,
                        text: '',
                    };
                }
                if (!this.problems.length) return { kind: 'ready', items: [], text: this.words.ready };
                if (!this.started) return { kind: 'quiet', items: [], text: '' };
                // Begun, not yet refused: what is left, as a plain list that shortens as they go.
                return {
                    kind: 'todo',
                    lead: this.words.still_needed + ' ',
                    items: this.problems.slice(0, 3),
                    more: Math.max(0, this.problems.length - 3),
                    next: this.problems[3] || null,
                    text: '',
                };
            },
        },

        methods: {
            // The message under a field, once Submit has been pressed.
            msg(key) {
                if (!this.triedSubmit) return '';
                const p = this.problems.find((x) => x.key === key);
                return p ? p.message : '';
            },

            bad(key) {
                return this.msg(key) ? 'gs-bad' : '';
            },

            // The box a person sees. On a phone Flatpickr shows the system's own date box and
            // hides its copy, so the copy is the wrong one to ring or to scroll to there.
            shownInput(picker) {
                if (!picker) return null;
                return picker.isMobile ? (picker.mobileInput || picker.input) : (picker.altInput || picker.input);
            },

            paintPickers() {
                const paint = (picker, key) => {
                    const el = this.shownInput(picker);
                    if (!el) return;
                    const message = this.msg(key);
                    el.classList.toggle('gs-bad', !!message);
                    el.setAttribute('aria-invalid', message ? 'true' : 'false');
                    message ? el.setAttribute('aria-describedby', 'err_' + key) : el.removeAttribute('aria-describedby');
                };
                paint(this.pickers.date, 'event_date');
                Object.keys(this.pickers.custom).forEach((key) => paint(this.pickers.custom[key], 'cf_' + key));
            },

            // An answer put back into the form is not yet in its box: a date box is Flatpickr's,
            // which shows what it was told and not what the model holds. Left unshown, the answer
            // was sent with a box that looked empty.
            showRestoredDates() {
                Object.keys(this.pickers.custom).forEach((key) => {
                    const value = this.customFieldValues[key];
                    if (value) this.pickers.custom[key].setDate(value, false);
                });
            },

            // "7:15 PM", "7pm", "19:15": the forms the event form and the booking form read, and
            // the shorter ones people type ("7p", "7:15p"), France's "19h30", and an hour alone
            // when it can only be one thing ("19"). A bare "7:15" is taken as written, on the
            // 24-hour clock; a bare "7" could be either, so it is refused. Anything else is
            // refused rather than guessed at.
            parseTime(text) {
                // Digits as an Arabic or a Persian keyboard types them are the same digits.
                const t = String(text || '').trim()
                    .replace(/[٠-٩]/g, (d) => String(d.charCodeAt(0) - 0x0660))
                    .replace(/[۰-۹]/g, (d) => String(d.charCodeAt(0) - 0x06F0));
                if (!t) return null;
                const half = (h, min, letter) => {
                    if (h < 1 || h > 12 || min > 59) return null;
                    if (/a/i.test(letter)) { if (h === 12) h = 0; } else if (h !== 12) { h += 12; }
                    return h * 60 + min;
                };
                let m = /^(\d{1,2})[:.](\d{2})\s*([ap])\.?m?\.?$/i.exec(t);
                if (m) return half(parseInt(m[1], 10), parseInt(m[2], 10), m[3]);
                m = /^(\d{1,2})\s*([ap])\.?m?\.?$/i.exec(t);
                if (m) return half(parseInt(m[1], 10), 0, m[2]);
                m = /^(\d{1,2})(?:[:.]|\s*h\s*)(\d{2})$/i.exec(t) || /^(\d{1,2})\s*h()$/i.exec(t);
                if (m) {
                    const h = parseInt(m[1], 10), min = parseInt(m[2] || '0', 10);
                    return (h > 23 || min > 59) ? null : h * 60 + min;
                }
                m = /^(\d{2})$/.exec(t);
                if (m && parseInt(m[1], 10) >= 13 && parseInt(m[1], 10) <= 23) return parseInt(m[1], 10) * 60;
                return null;
            },

            formatTime(minutes) {
                const h = Math.floor(minutes / 60) % 24, m = minutes % 60;
                const mm = (m < 10 ? '0' : '') + m;
                if (this.use24hr) return (h < 10 ? '0' : '') + h + ':' + mm;
                return (h % 12 || 12) + ':' + mm + ' ' + (h < 12 ? 'AM' : 'PM');
            },

            timeExample(which) {
                return this.formatTime(which === 'end' ? 21 * 60 : 19 * 60);
            },

            timeHelp() {
                return this.words.time_like.replace(':example', this.formatTime(19 * 60 + 15));
            },

            openTime(which) {
                const opening = this.timeOpen !== which;
                this.timeOpen = which;
                this.timeHighlight = -1;
                if (!opening) return;
                // Open on the evening, or on the time already there: nobody wants midnight first.
                this.$nextTick(() => {
                    const list = document.getElementById((which === 'start' ? 'submit_event_time' : 'submit_event_end_time') + '_list');
                    if (!list) return;
                    const current = this.event[which === 'start' ? 'event_start_time' : 'event_end_time'];
                    const target = current || (which === 'start' ? '19:00' : null);
                    const index = this.timeChoices.findIndex((o) => o.value === target);
                    if (index > 0 && list.children[index]) list.scrollTop = list.children[index].offsetTop - 4;
                });
            },

            pickTime(which, value) {
                this.setTime(which, value);
                this.timeOpen = null;
            },

            // Leaving the box settles it: a time that reads is rewritten the way the page shows
            // times; one that does not stays as typed and says so underneath.
            commitTime(which) {
                const key = which === 'start' ? 'event_start_time' : 'event_end_time';
                const text = (this.timeText[which] || '').trim();
                this.timeOpen = null;
                if (!text) {
                    this.event[key] = '';
                    this.timeBad[which] = false;
                    return;
                }
                const minutes = this.parseTime(text);
                if (minutes === null) {
                    this.event[key] = '';
                    this.timeBad[which] = true;
                    return;
                }
                this.setTime(which, this.timeOf(minutes));
            },

            timeKey(e, which) {
                const list = this.timeChoices;
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (this.timeOpen !== which) return this.openTime(which);
                    if (!list.length) return;
                    const step = e.key === 'ArrowDown' ? 1 : -1;
                    this.timeHighlight = (this.timeHighlight + step + list.length) % list.length;
                    this.$nextTick(() => {
                        const el = document.querySelector('#' + e.target.id + '_list li.is-on');
                        if (el) el.scrollIntoView({ block: 'nearest' });
                    });
                } else if (e.key === 'Enter') {
                    // Enter in a time box settles the time; it is not the Submit press.
                    e.preventDefault();
                    if (this.timeOpen === which && this.timeHighlight >= 0 && list[this.timeHighlight]) this.pickTime(which, list[this.timeHighlight].value);
                    else this.commitTime(which);
                } else if (e.key === 'Escape') {
                    this.timeOpen = null;
                }
            },

            // Any change is a new attempt: what the server said last time no longer stands.
            touched() {
                if (Object.keys(this.serverErrors).length) this.serverErrors = {};
                // A page left open past its session stays that way whatever is typed: the bar
                // keeps saying so, beside the Reload button.
                if (!this.expired) this.barMessage = null;
            },

            // Bring a problem on screen, opening the row it is in, and put the caret there.
            goTo(problem) {
                if (problem.row && !this.rowOpen(problem.row)) this.openRow = problem.row;
                this.$nextTick(() => {
                    let el = problem.id ? document.getElementById(problem.id) : null;
                    if (!el) return;
                    if (problem.id === 'submit_event_date') el = this.shownInput(this.pickers.date) || el;
                    // A schedule's own date question: the box that shows is Flatpickr's copy, and
                    // the id belongs to the hidden one, where the link landed on nothing.
                    const own = String(problem.key || '').indexOf('cf_') === 0 ? this.pickers.custom[problem.key.slice(3)] : null;
                    if (own) el = this.shownInput(own) || el;
                    // The description textarea is hidden behind EasyMDE: scroll its wrapper, and put
                    // the caret in the editor itself, which is the box a person types in.
                    const editor = problem.id === 'submit_description' && el._easyMDE ? el._easyMDE : null;
                    if (editor && el.parentElement) el = el.parentElement;
                    el.scrollIntoView({ block: 'center', behavior: this.glide() });
                    if (editor) {
                        try { editor.codemirror.focus(); } catch (e) { /* the editor is not ready */ }
                        return;
                    }
                    if (['INPUT', 'SELECT', 'TEXTAREA', 'BUTTON'].includes(el.tagName)) {
                        try { el.focus({ preventScroll: true }); } catch (e) { /* older browsers */ }
                    }
                });
            },

            // Smooth, unless the visitor has asked for less motion.
            glide() {
                try {
                    return window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
                } catch (e) {
                    return 'auto';
                }
            },

            // Lets the accessibility button and the credit chip stand clear of the bar, through
            // the same variable the event page's ticket bar publishes.
            publishBarHeight() {
                this.$nextTick(() => {
                    const bar = document.getElementById('submit-bar');
                    const root = document.documentElement;
                    const height = (bar && !this.submitted && this.step === 'form') ? bar.offsetHeight : 0;
                    if (height > 0) {
                        root.style.setProperty('--es-a11y-cta-clearance', (height + 8) + 'px');
                        root.classList.add('es-a11y-cta-offset');
                    } else {
                        root.classList.remove('es-a11y-cta-offset');
                    }
                    // A field reached with Tab is scrolled only just into view, which in Firefox
                    // is under the bar. This is the room the browser leaves below it.
                    root.style.scrollPaddingBottom = height > 0 ? (height + 16) + 'px' : '';
                });
            },

            // A request that never answers must not hold the form for ever.
            async post(url, options, seconds) {
                const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
                const timer = controller ? setTimeout(() => controller.abort(), (seconds || 60) * 1000) : null;
                try {
                    return await fetch(url, Object.assign({}, options, controller ? { signal: controller.signal } : {}));
                } finally {
                    if (timer) clearTimeout(timer);
                }
            },

            // What to say for a status with no message of its own. 419 is a page left open past
            // its session, and "Error" tells nobody that reloading is the cure.
            refusalWords(status, fallback) {
                if (status === 419) return this.words.expired;
                if (status === 429) return this.words.too_many;
                return fallback || this.words.error;
            },

            minutesOf(time) {
                if (!time) return null;
                const m = /^(\d{1,2}):(\d{2})/.exec(time);
                return m ? (parseInt(m[1], 10) * 60 + parseInt(m[2], 10)) : null;
            },

            timeOf(minutes) {
                const m = ((Math.round(minutes) % 1440) + 1440) % 1440;
                const h = Math.floor(m / 60), min = m % 60;
                return (h < 10 ? '0' : '') + h + ':' + (min < 10 ? '0' : '') + min;
            },

            zoneOffset(zone, at) {
                try {
                    const parts = new Intl.DateTimeFormat('en-US', {
                        timeZone: zone, hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit',
                        hour: '2-digit', minute: '2-digit', second: '2-digit',
                    }).formatToParts(at);
                    const v = {};
                    parts.forEach((p) => { v[p.type] = p.value; });
                    return Math.round((Date.UTC(+v.year, +v.month - 1, +v.day, +v.hour, +v.minute, +v.second) - at.getTime()) / 60000);
                } catch (e) {
                    return null;
                }
            },

            zoneName(zone) {
                return String(zone || '').split('/').pop().replace(/_/g, ' ');
            },

            // A link as a browser would open it, or '' when it is not one. "example.com/tickets"
            // is a link people type; "tickets at the door" is not.
            cleanUrl(value) {
                let v = String(value || '').trim();
                if (!v) return '';
                if (!/^[a-z][a-z0-9+.-]*:/i.test(v)) v = 'https://' + v;
                try {
                    const u = new URL(v);
                    if (!/^https?:$/.test(u.protocol) || u.hostname.indexOf('.') < 1 || /\s/.test(v)) return '';
                    return u.href;
                } catch (e) {
                    return '';
                }
            },

            customFieldIsMissing(field) {
                if (! field.required) {
                    return false;
                }

                const value = this.customFieldValues[field.key];

                if (field.type === 'multiselect') {
                    return !(value || []).length;
                }

                // A switch always holds '0' or '1', so it is never "missing".
                return field.type !== 'switch' && !String(value ?? '').trim();
            },

            customFieldFailsPattern(field) {
                if (! field.regex || (field.type !== 'string' && field.type !== 'multiline_string')) {
                    return false;
                }

                const text = String(this.customFieldValues[field.key] ?? '');
                if (! text) {
                    return false;
                }

                try {
                    return ! new RegExp('^(?:' + field.regex + ')$', 'u').test(text);
                } catch (e) {
                    // An uncompilable pattern is rejected when the schedule saves it; if one slips
                    // through anyway, let the server have the final say rather than blocking here.
                    return false;
                }
            },

            maybeRenderTurnstile() {
                if (!this.turnstileEnabled || !this.turnstileSiteKey) return;
                if (this.isAuthed || this.accountMode !== 'register' || !this.requiresCode) return;
                this.renderImportTurnstile();
            },

            renderImportTurnstile() {
                const el = document.getElementById('turnstile-import-widget');
                if (!el || el.childElementCount > 0) return;
                if (typeof turnstile === 'undefined') {
                    // The script is async, and on a slow line it can be many seconds away. Look
                    // often at first, then once a second for as long as the page is open: giving
                    // up after ten seconds left a form whose every Submit was refused until it
                    // was reloaded.
                    setTimeout(() => this.renderImportTurnstile(), ++this.turnstileTries < 50 ? 200 : 1000);
                    return;
                }
                this.turnstileWidgetId = turnstile.render('#turnstile-import-widget', {
                    sitekey: this.turnstileSiteKey,
                    callback: (token) => { this.turnstileToken = token; },
                    'expired-callback': () => { this.turnstileToken = ''; },
                    'error-callback': () => { this.turnstileToken = ''; },
                });
            },

            resetTurnstile() {
                this.turnstileToken = '';
                if (this.turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
                    turnstile.reset(this.turnstileWidgetId);
                }
            },

            // true when a code is on its way (or one sent to this address is still good).
            async sendCode(force) {
                const email = (this.userEmail || '').trim().toLowerCase();
                const fresh = this.codeEmail === email && (Date.now() - this.codeSentAt) < 9 * 60 * 1000;
                if (fresh && !force) return true;

                this.codeSending = true;
                this.codeError = null;
                try {
                    const response = await this.post('{{ route("event.guest_send_code", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        // `form` says which request page is asking, so each page's funnel counts its own codes.
                        body: JSON.stringify({ email, website: this.honeypot, 'cf-turnstile-response': this.turnstileToken, form: this.formKind || 'submit' })
                    }, 30);
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.success === false) {
                        // The route's own "Too Many Attempts." and a session that ran out get our
                        // words; the server's sentences (already registered, five codes an hour) stand.
                        const routeThrottle = response.status === 429 && !data.success && !data.reason && /too many attempts/i.test(data.message || '');
                        const message = (response.status === 419 || routeThrottle) ? this.refusalWords(response.status) : (data.message || this.words.error);
                        if (response.status === 419) this.expired = true;
                        // The check Cloudflare runs lives on the form. If it wants the visitor,
                        // they have to be able to see it.
                        if (data.errors && data.errors['cf-turnstile-response']) {
                            // A resend refused here sent nothing, and the old code is the one the
                            // visitor said never came. Forget it was sent, so the next Submit asks
                            // for a new one instead of opening the code step on the old.
                            if (force) {
                                this.codeSentAt = 0;
                                this.saveSession();
                            }
                            if (this.step === 'code') this.backToForm('check');
                        }
                        // Someone who has an account after all: the form's sign-in half is for them.
                        if (data.reason === 'registered') {
                            this.emailExists = true;
                            this.emailStub = false;
                            this.accountMode = 'login';
                        }
                        if (this.step === 'code') this.codeError = message; else this.barMessage = message;
                        return false;
                    }
                    this.codeEmail = email;
                    this.codeSentAt = Date.now();
                    this.saveSession();
                    this.startResendCountdown();
                    return true;
                } catch (e) {
                    if (this.step === 'code') this.codeError = this.words.error; else this.barMessage = this.words.error;
                    return false;
                } finally {
                    this.codeSending = false;
                    this.resetTurnstile();
                }
            },

            async resendCode() {
                await this.sendCode(true);
            },

            startResendCountdown() {
                this.resendCountdown = 30;
                if (this.resendTimer) clearInterval(this.resendTimer);
                this.resendTimer = setInterval(() => {
                    this.resendCountdown--;
                    if (this.resendCountdown <= 0) {
                        clearInterval(this.resendTimer);
                        this.resendTimer = null;
                    }
                }, 1000);
            },

            showCodeStep() {
                this.step = 'code';
                this.verificationCode = '';
                this.codeError = null;
                this.sent = this.summary();
                document.getElementById('gs-page').classList.add('gs-coding');
                window.scrollTo({ top: 0, behavior: this.glide() });
                this.$nextTick(() => {
                    const el = document.getElementById('verification_code');
                    if (!el) return;
                    el.focus({ preventScroll: true });
                    // Do not wait for the focus event to light the first box: it is not sent to a
                    // window that is itself in the background.
                    this.codeFocused = document.activeElement === el;
                });
            },

            // The button under the code box. With six digits it sends; with fewer it asks for them.
            submitCode() {
                if (this.verificationCode.length === 6) return this.submitEvent();
                this.codeError = this.words.required;
                const el = document.getElementById('verification_code');
                if (el) el.focus();
            },

            // Back to the form from the code step: to the email ("Use a different email"), to the
            // event ("Edit event"), or to the check Cloudflare runs, which sits above Submit.
            // Focus goes too: left on a button that is no longer on screen, the next Tab started
            // from nowhere.
            backToForm(where) {
                this.step = 'form';
                this.codeError = null;
                document.getElementById('gs-page').classList.remove('gs-coding');
                this.$nextTick(() => {
                    const bar = document.getElementById('submit-bar');
                    const el = where === 'email' ? document.getElementById('account_email')
                        : (where === 'check' ? (document.getElementById('turnstile-import-widget') || bar) : document.getElementById('submit_event_name'));
                    if (!el) return;
                    el.scrollIntoView({ block: 'center' });
                    const focus = where === 'check' ? (bar && bar.querySelector('button[type="submit"]')) : el;
                    if (focus) focus.focus({ preventScroll: true });
                });
            },

            // Keep the caret after the last digit, as the sign-up page does: a click on the third
            // box of a four-digit code would otherwise type into the middle and push the rest along.
            keepCodeCaretAtEnd() {
                const input = document.getElementById('verification_code');
                if (!input || document.activeElement !== input) return;
                const end = input.value.length;
                try {
                    if (input.selectionStart !== end || input.selectionEnd !== end) input.setSelectionRange(end, end);
                } catch (e) { /* not a text selection */ }
            },

            onCodeInput(event) {
                this.codeFrom = this.verificationCode.length;
                this.verificationCode = (event.target.value || '').replace(/\D/g, '').slice(0, 6);
                // When sanitizing leaves the model unchanged Vue skips the DOM patch, so a
                // rejected character would stay visible; write the clean value back.
                event.target.value = this.verificationCode;
                this.codeError = null;
                // The sixth digit is the Submit press: there is nothing else on this step.
                if (this.verificationCode.length === 6) this.submitEvent();
            },

            setDate(value) {
                this.event.event_date = value;
                if (this.pickers.date) value ? this.pickers.date.setDate(value, false) : this.pickers.date.clear(false);
            },

            setTime(which, value) {
                const minutes = this.minutesOf(value);
                this.event[which === 'start' ? 'event_start_time' : 'event_end_time'] = minutes === null ? '' : this.timeOf(minutes);
                this.timeText[which] = minutes === null ? '' : this.formatTime(minutes);
                this.timeBad[which] = false;
            },

            // End time is optional; when set, it becomes the duration (hours, float) the
            // backend already persists. Crossing midnight rolls to the next day.
            durationHours() {
                const s = this.minutesOf(this.event.event_start_time);
                const e = this.minutesOf(this.event.event_end_time);
                if (s === null || e === null) return null;
                let diff = e - s;
                if (diff < 0) diff += 1440;
                if (diff === 0) return null;
                return Math.round((diff / 60) * 100) / 100;
            },
        },
    };
  </script>
