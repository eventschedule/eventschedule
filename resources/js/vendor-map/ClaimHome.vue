<template>
    <div class="vmc-wrap">
        <p class="vmc-kicker"><bdi>{{ config.marketName }}</bdi></p>
        <h1 class="vmc-title">
            <span class="vmc-big">{{ home.spot }}</span>
        </h1>
        <p class="vmc-lead">{{ home.every }}</p>

        <div v-if="message" class="vmc-note vmc-note-ok" role="status" style="margin-bottom: 16px">
            <p style="margin: 0">{{ message }}</p>
            <button v-if="messageDate" type="button" class="vmc-btn vmc-btn-quiet" style="margin-top: 6px"
                    :disabled="busy" @click="present(messageDate)">{{ t('coming_after_all') }}</button>
        </div>
        <p v-if="error" class="vmc-note vmc-note-err" role="alert" style="margin-bottom: 16px">{{ error }}</p>

        <div class="vmc-stack">
            <button v-if="next && !next.absent" type="button" class="vmc-btn vmc-btn-primary" :disabled="busy" @click="absent(next)">
                {{ t('not_coming_on', { date: next.label }) }}
            </button>

            <div class="vmc-card">
                <p class="vmc-label">{{ t('coming_dates') }}</p>
                <ul class="vmc-dates">
                    <li v-for="d in home.dates" :key="d.date">
                        <span :class="{ 'vmc-muted': d.absent }">
                            {{ d.label }}<template v-if="d.absent"> · {{ t('not_coming') }}</template>
                        </span>
                        <button v-if="d.absent" type="button" class="vmc-btn vmc-btn-quiet vmc-inline" :disabled="busy" @click="present(d.date)">
                            {{ t('coming_after_all') }}
                        </button>
                        <button v-else type="button" class="vmc-btn vmc-btn-quiet vmc-inline" :disabled="busy" @click="absent(d)">
                            {{ t('not_coming') }}
                        </button>
                    </li>
                </ul>
            </div>

            <button type="button" class="vmc-btn" @click="share">{{ copied ? t('copied') : t('share') }}</button>

            <button v-if="!editing" type="button" class="vmc-btn" @click="startEdit">{{ t('change_details') }}</button>
            <div v-else class="vmc-card vmc-stack">
                <p class="vmc-label" style="margin: 0">{{ strings.sells_label }}</p>
                <div class="vmc-chips" role="group" :aria-label="strings.sells_label">
                    <button v-for="(label, slug) in config.categories" :key="slug" type="button"
                            :class="['vmc-chip', { 'is-on': edit.categories.includes(slug) }]"
                            :aria-pressed="edit.categories.includes(slug) ? 'true' : 'false'" @click="toggle(slug)">
                        <ClaimIcon :name="slug" cls="vmc-chip-icon" />
                        <span>{{ label }}</span>
                    </button>
                </div>
                <input v-model="edit.sells" class="vmc-field" type="text" maxlength="200" :aria-label="strings.sells_label">
                <div class="vmc-row">
                    <button type="button" class="vmc-btn" @click="editing = false">{{ t('cancel') }}</button>
                    <button type="button" class="vmc-btn vmc-btn-primary" :disabled="busy || !edit.categories.length" @click="saveEdit">{{ t('save') }}</button>
                </div>
            </div>

            <a v-if="home.pageUrl" class="vmc-btn" :href="home.pageUrl">{{ t('your_page') }}</a>

            <button v-if="!confirmStop" type="button" class="vmc-btn vmc-btn-quiet vmc-btn-danger" @click="confirmStop = true">{{ t('stop') }}</button>
            <div v-else class="vmc-note vmc-note-warn vmc-stack" role="alertdialog" aria-modal="false">
                <p style="margin: 0">{{ t('stop_confirm', { name: config.ownerFirstName }) }}</p>
                <div class="vmc-row">
                    <button type="button" class="vmc-btn" @click="confirmStop = false">{{ t('cancel') }}</button>
                    <button type="button" class="vmc-btn vmc-btn-danger" :disabled="busy" @click="stop">{{ t('stop_yes') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ClaimIcon from './ClaimIcon.vue';

/** The vendor link for a vendor with a stall for the season: their home at this market. */
export default {
    name: 'ClaimHome',
    components: { ClaimIcon },
    props: {
        config: { type: Object, required: true },
        initial: { type: Object, required: true },
        t: { type: Function, required: true },
        post: { type: Function, required: true },
    },
    emits: ['stopped'],
    data() {
        return {
            home: this.initial,
            busy: false,
            error: '',
            message: '',
            messageDate: null,
            copied: false,
            editing: false,
            confirmStop: false,
            edit: { sells: '', categories: [] },
        };
    },
    computed: {
        strings() {
            return this.config.strings || {};
        },
        next() {
            return (this.home.dates || [])[0] || null;
        },
    },
    methods: {
        async send(body) {
            this.busy = true;
            this.error = '';
            try {
                const res = await this.post(this.config.urls.home, body);
                if (res.home) {
                    this.home = res.home;
                }
                return res;
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.busy = false;
            }
        },
        async absent(d) {
            const res = await this.send({ action: 'absent', date: d.date });
            if (res) {
                this.message = this.t('absent_done', { name: this.config.ownerFirstName, date: d.label });
                this.messageDate = d.date;
            }
        },
        async present(date) {
            const res = await this.send({ action: 'present', date });
            if (res) {
                this.message = '';
                this.messageDate = null;
            }
        },
        startEdit() {
            this.edit = { sells: this.home.sells || '', categories: [...(this.home.categories || [])] };
            this.editing = true;
        },
        toggle(slug) {
            const i = this.edit.categories.indexOf(slug);
            if (i >= 0) {
                this.edit.categories.splice(i, 1);
            } else if (this.edit.categories.length < 2) {
                this.edit.categories.push(slug);
            } else {
                this.edit.categories.splice(0, 1, slug);
            }
        },
        async saveEdit() {
            const res = await this.send({ action: 'update', sells: this.edit.sells, categories: this.edit.categories });
            if (res) {
                this.editing = false;
                this.message = this.t('saved');
                this.messageDate = null;
            }
        },
        async stop() {
            const res = await this.send({ action: 'stop' });
            if (res && res.stopped) {
                this.$emit('stopped');
            }
        },
        async share() {
            const s = this.home.share;
            if (navigator.share) {
                try {
                    await navigator.share({ text: s.text, url: s.url });
                    return;
                } catch (e) {
                    if (e && e.name === 'AbortError') return;
                }
            }
            try {
                await navigator.clipboard.writeText(s.text + ' ' + s.url);
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            } catch (e) {
                window.prompt('', s.text + ' ' + s.url);
            }
        },
    },
};
</script>
