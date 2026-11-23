<template>
    <ClaimHome v-if="screen === 'home'" :config="config" :initial="config.home" :t="t" :post="post" @stopped="screen = 'stopped'" />

    <div v-else class="vmc-wrap">
        <p v-if="!stepped" class="vmc-kicker"><bdi>{{ config.marketName }}</bdi></p>

        <p v-if="error" class="vmc-note vmc-note-err" role="alert" style="margin-bottom: 16px">{{ error }}</p>

        <!-- Stopped: the season's place given up. -->
        <template v-if="screen === 'stopped'">
            <h1 class="vmc-title">{{ t('stopped') }}</h1>
            <a class="vmc-btn" :href="config.publicUrl">{{ t('see_map') }}</a>
        </template>

        <!-- Sent, or opened again while it waits. -->
        <template v-else-if="screen === 'waiting' && waiting">
            <div class="vmc-steps">
                <div class="vmc-steps-top">
                    <span class="vmc-steps-where"><bdi>{{ config.marketName }}</bdi></span>
                    <span v-if="waiting.justSent" class="vmc-step-of">{{ t('step_of', { step: totalSteps, total: totalSteps }) }}</span>
                </div>
                <div v-if="waiting.justSent" class="vmc-progress" aria-hidden="true"><i v-for="n in totalSteps" :key="n" :class="{ 'is-on': n <= totalSteps }"></i></div>
                <h1 class="vmc-title">{{ waiting.justSent ? t('sent_title', { name: config.ownerFirstName }) : t('waiting_title', { name: config.ownerFirstName }) }}</h1>
                <p class="vmc-lead">{{ t('sent_lead', { name: config.ownerFirstName }) }}</p>
            </div>

            <div class="vmc-status">
                <div class="vmc-status-top">
                    <div>
                        <p class="vmc-status-spot">{{ waiting.spot }}</p>
                        <p v-if="waiting.pageName" class="vmc-status-name"><bdi>{{ waiting.pageName }}</bdi></p>
                    </div>
                    <span class="vmc-pill">{{ t('status_waiting') }}</span>
                </div>
                <ol class="vmc-track">
                    <li class="is-done"><i aria-hidden="true"></i>{{ t('status_asked') }}</li>
                    <li class="is-now" aria-current="step"><i aria-hidden="true"></i>{{ t('status_confirms', { name: config.ownerFirstName }) }}</li>
                    <li><i aria-hidden="true"></i>{{ t('status_on_map') }}</li>
                </ol>
            </div>

            <div class="vmc-linkback">
                <p class="vmc-label" id="vmc-linkback">{{ t('link_back') }}</p>
                <div class="vmc-copyrow">
                    <span class="vmc-copyrow-url" dir="ltr">{{ linkBackShort }}</span>
                    <button type="button" class="vmc-copyrow-btn" :aria-label="t('copy') + ': ' + t('link_back')" @click="copyBack">{{ copiedBack ? t('copied') : t('copy') }}</button>
                </div>
                <p class="vmc-hint">{{ t('save_link') }}</p>
            </div>

            <div class="vmc-foot vmc-stack">
                <button type="button" class="vmc-btn vmc-btn-primary" @click="share(waiting.share)">{{ copied ? t('copied') : t('share') }}</button>
                <a v-if="waiting.pageUrl" class="vmc-btn" :href="waiting.pageUrl">{{ t('preview_page') }}</a>
                <button type="button" class="vmc-btn vmc-btn-quiet" @click="startOver">{{ t('choose_another') }}</button>
            </div>
        </template>

        <!-- 1. Tap where you set up. The map first: a vendor knows the place better than the
             number. The list is there for somebody who knows the number. -->
        <template v-else-if="screen === 'list'">
            <h1 class="vmc-title">{{ strings.find_title }}</h1>
            <p class="vmc-lead">{{ layered && showMap ? (config.areaMap || {}).tapArea : t('tap_your_spot') }}</p>

            <div class="vmc-toolbar">
                <label v-if="!showMap" class="vmc-search">
                    <span class="sr-only">{{ t('search') }}</span>
                    <input v-model="query" class="vmc-field" type="search" :placeholder="t('search')" autocomplete="off" enterkeyhint="search">
                </label>
                <span v-else class="vmc-legend"><i class="is-free" aria-hidden="true"></i>{{ t('free') }}<i class="is-taken" aria-hidden="true"></i>{{ t('taken_short') }}</span>
                <button type="button" class="vmc-btn vmc-toggle" @click="showMap = !showMap">
                    {{ showMap ? t('list') : t('map') }}
                </button>
            </div>
            <p class="sr-only" aria-live="polite">{{ resultsSay }}</p>

            <div v-if="showMap" class="vmc-schematic">
                <!-- A big market: its areas first, then the area close up (AreaMap.vue). -->
                <AreaMap v-if="layered" :schematic="claimSchematic" :rtl="config.rtl" :spot-label="spotAriaById" :selectable="selectableId"
                         :selected-id="pickedId" :t="{ ...(config.areaMap || {}), tapArea: '' }" :foot-text="freeIn" :pressable="false" @select="pickById" />
                <SchematicMap v-else :schematic="claimSchematic" :rtl="config.rtl" :spot-label="spotAriaById" :selectable="selectableId" :pressable="false" @select="pickById" />
            </div>

            <ul v-else class="vmc-list">
                <li v-for="s in filtered" :key="s.id">
                    <button type="button" class="vmc-list-row" :disabled="s.waiting" @click="pick(s)">
                        <span class="vmc-num">{{ s.label }}</span>
                        <span class="vmc-list-text">
                            <span v-if="s.name" class="vmc-list-name"><bdi>{{ s.name }}</bdi></span>
                            <span v-else class="vmc-list-name vmc-muted">{{ t('free') }}</span>
                            <span v-if="s.waiting" class="vmc-list-sub">{{ t('someone_asked') }}</span>
                            <span v-else-if="s.page && !s.mine" class="vmc-list-sub">{{ t('taken_short') }}</span>
                            <span v-else-if="s.area" class="vmc-list-sub"><bdi>{{ s.area }}</bdi></span>
                        </span>
                    </button>
                </li>
                <li v-if="!filtered.length" class="vmc-muted vmc-center" style="padding: 16px">{{ t('search_empty') }}</li>
            </ul>
        </template>

        <!-- 2. The map again, the chosen stall marked: "Is this your stall?" -->
        <template v-else-if="screen === 'confirm' && picked">
            <h1 class="vmc-title">{{ picked.page && !picked.mine ? spotLabel(picked) : strings.confirm_title }}</h1>
            <p v-if="picked.name" class="vmc-confirm-name"><bdi>{{ picked.name }}</bdi></p>
            <p class="vmc-lead"><template v-if="!(picked.page && !picked.mine)">{{ spotLabel(picked) }}<template v-if="picked.area"> · </template></template><bdi v-if="picked.area">{{ picked.area }}</bdi></p>
            <div class="vmc-schematic is-confirm">
                <SchematicMap :schematic="confirmSchematic" :rtl="config.rtl" :selected-id="picked.id" :spot-label="spotAriaById" :selectable="() => false" />
            </div>
            <template v-if="picked.page && !picked.mine">
                <p class="vmc-lead">{{ t('spot_has_page') }}</p>
                <div class="vmc-stack">
                    <a v-if="!config.signedIn" class="vmc-btn vmc-btn-primary" :href="config.loginUrl">{{ t('sign_in') }}</a>
                    <button type="button" class="vmc-btn" @click="back('list')">{{ t('choose_another') }}</button>
                </div>
            </template>
            <div v-else class="vmc-row">
                <button type="button" class="vmc-btn" @click="back('list')">{{ t('choose_another') }}</button>
                <button type="button" class="vmc-btn vmc-btn-primary" @click="startAbout(picked.name ? 'me' : 'free')">{{ strings.this_is_mine }}</button>
            </div>
        </template>

        <!-- 3. About you -->
        <template v-else-if="screen === 'about' && picked">
            <div class="vmc-steps">
                <div class="vmc-steps-top">
                    <button type="button" class="vmc-back" @click="back('confirm')"><svg class="vmc-chev" viewBox="0 0 20 20" aria-hidden="true"><path d="M12.5 4.5L7 10l5.5 5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg><span><bdi>{{ config.marketName }}</bdi> · {{ spotLabel(picked) }}</span></button>
                    <span v-if="true" class="vmc-step-of">{{ t('step_of', { step: 1, total: totalSteps }) }}</span>
                </div>
                <div v-if="true" class="vmc-progress" aria-hidden="true"><i v-for="n in totalSteps" :key="n" :class="{ 'is-on': n <= 1 }"></i></div>
                <h1 class="vmc-title">{{ t('about_title') }}</h1>
            </div>

            <form class="vmc-stack vmc-form" novalidate @submit.prevent="aboutDone">
                <div v-if="config.signedIn && config.pages.length">
                    <p class="vmc-label">{{ t('which_page') }}</p>
                    <div class="vmc-stack">
                        <label v-for="p in config.pages" :key="p.id" :class="['vmc-check', { 'vmc-check-on': form.page === p.id }]">
                            <input v-model="form.page" type="radio" name="page" :value="p.id" @change="pageChosen(p)">
                            <bdi>{{ p.name }}</bdi>
                        </label>
                        <label :class="['vmc-check', { 'vmc-check-on': form.page === 'new' }]">
                            <input v-model="form.page" type="radio" name="page" value="new">
                            <span>{{ t('new_page') }}</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="vmc-label" for="vmc-name">{{ strings.name_label }}</label>
                    <input id="vmc-name" v-model="form.name" class="vmc-field" type="text" maxlength="120" autocomplete="organization" required>
                </div>

                <div>
                    <div class="vmc-label-row">
                        <p class="vmc-label" id="vmc-cats">{{ strings.sells_label }}</p>
                        <span class="vmc-hint" aria-live="polite">{{ t('picked', { count: form.categories.length, max: maxCategories }) }}</span>
                    </div>
                    <div class="vmc-tiles" role="group" aria-labelledby="vmc-cats">
                        <button v-for="(label, slug) in config.categories" :key="slug" type="button"
                                :class="['vmc-tile', { 'is-on': form.categories.includes(slug), 'is-full': !form.categories.includes(slug) && form.categories.length >= maxCategories }]"
                                :aria-pressed="form.categories.includes(slug) ? 'true' : 'false'"
                                :aria-disabled="!form.categories.includes(slug) && form.categories.length >= maxCategories ? 'true' : 'false'"
                                @click="toggleCategory(slug)">
                            <span>{{ label }}</span>
                            <svg v-if="form.categories.includes(slug)" class="vmc-tile-check" viewBox="0 0 20 20" aria-hidden="true"><path d="M4.5 10.5l3.5 3.5 7.5-8" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="vmc-label" for="vmc-sells">{{ t('few_words') }}</label>
                    <input id="vmc-sells" v-model="form.sells" class="vmc-field" type="text" maxlength="200" :placeholder="t('sells_placeholder')">
                    <p class="vmc-hint">{{ t('few_words_hint') }}</p>
                </div>

                <div>
                    <p class="vmc-label">{{ t('photo_label') }}</p>
                    <label v-if="!photo.preview" class="vmc-photo-drop">
                        <input type="file" accept="image/*" class="sr-only" @change="photoPicked">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 8.5A2.5 2.5 0 015.5 6h1.9l1.3-2h6.6l1.3 2h1.9A2.5 2.5 0 0121 8.5v9a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 17.5v-9z" fill="none" stroke="currentColor" stroke-width="1.6" /><circle cx="12" cy="13" r="3.5" fill="none" stroke="currentColor" stroke-width="1.6" /></svg>
                        {{ t('photo_add') }}
                    </label>
                    <div v-else class="vmc-photo">
                        <div v-if="photo.preview" class="vmc-photo-preview">
                            <img :src="photo.preview" alt="">
                            <svg v-if="photo.uploading" class="vmc-ring" viewBox="0 0 36 36" aria-hidden="true">
                                <circle cx="18" cy="18" r="15" class="vmc-ring-track" />
                                <circle cx="18" cy="18" r="15" class="vmc-ring-bar" :stroke-dasharray="ringDash" />
                            </svg>
                        </div>
                        <div class="vmc-stack" style="flex: 1">
                            <label class="vmc-btn">
                                <input ref="photoInput" type="file" accept="image/*" class="sr-only" @change="photoPicked">
                                {{ photo.preview ? t('photo_change') : t('photo_add') }}
                            </label>
                            <button v-if="photo.preview && !photo.uploading" type="button" class="vmc-btn vmc-btn-quiet" @click="clearPhoto">{{ t('photo_remove') }}</button>
                            <p v-if="photo.uploading" class="vmc-hint" aria-live="polite">{{ t('uploading') }} {{ photo.progress }}%</p>
                        </div>
                    </div>
                    <p v-if="photo.error" class="vmc-note vmc-note-err" role="alert" style="margin-top: 8px">{{ photo.error }}</p>
                    <p class="vmc-hint">{{ t('photo_hint') }}</p>
                </div>

                <div>
                    <label class="vmc-label" for="vmc-phone">{{ t('phone_label') }}</label>
                    <input id="vmc-phone" v-model="form.phone" class="vmc-field" type="tel" inputmode="tel" autocomplete="tel" maxlength="32" dir="ltr">
                    <p class="vmc-hint">{{ t('phone_hint', { name: config.ownerFirstName }) }}</p>
                </div>

                <div v-if="!config.oneDay">
                    <label class="vmc-switch">
                        <span>{{ strings.every_date }}</span>
                        <input v-model="form.every" type="checkbox" role="switch">
                        <span class="vmc-switch-track" aria-hidden="true"><span class="vmc-switch-knob"></span></span>
                    </label>
                    <template v-if="!form.every">
                        <button v-if="!showDates" type="button" class="vmc-btn vmc-btn-quiet" @click="showDates = true">{{ t('choose_dates') }}</button>
                        <div v-else class="vmc-stack" style="margin-top: 8px">
                            <label v-for="d in config.dates" :key="d.date" :class="['vmc-check', { 'vmc-check-on': form.dates.includes(d.date) }]">
                                <input v-model="form.dates" type="checkbox" :value="d.date">
                                <span>{{ d.label }}</span>
                            </label>
                        </div>
                    </template>
                </div>

                <div class="vmc-foot">
                    <button type="submit" class="vmc-btn vmc-btn-primary" :disabled="busy || photo.uploading">{{ config.signedIn ? t('send') : t('continue') }}</button>
                </div>
            </form>
        </template>

        <!-- 4. Account, last. -->
        <template v-else-if="screen === 'account'">
            <div class="vmc-steps">
                <div class="vmc-steps-top">
                    <button type="button" class="vmc-back" @click="back('about')"><svg class="vmc-chev" viewBox="0 0 20 20" aria-hidden="true"><path d="M12.5 4.5L7 10l5.5 5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg><span>{{ t('back') }}</span></button>
                    <span v-if="true" class="vmc-step-of">{{ t('step_of', { step: 2, total: totalSteps }) }}</span>
                </div>
                <div v-if="true" class="vmc-progress" aria-hidden="true"><i v-for="n in totalSteps" :key="n" :class="{ 'is-on': n <= 2 }"></i></div>
                <h1 class="vmc-title">{{ t('keep_title') }}</h1>
                <p class="vmc-lead">{{ form.name ? t('keep_lead', { name: form.name }) : t('keep_lead_plain') }}</p>
            </div>

            <div class="vmc-stack">
                <template v-if="config.google && !codeSent">
                    <div v-if="inApp" class="vmc-note vmc-note-warn vmc-stack">
                        <p style="margin: 0"><strong>{{ t('in_app_title') }}</strong></p>
                        <p style="margin: 0">{{ t('in_app_body') }}</p>
                        <a v-if="chromeIntent" class="vmc-btn" :href="chromeIntent">{{ t('open_chrome') }}</a>
                        <button type="button" class="vmc-btn" @click="copyLink">{{ copied ? t('copied') : t('copy_link') }}</button>
                    </div>
                    <button v-else type="button" class="vmc-btn vmc-google" :disabled="busy" @click="google">
                        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        {{ t('google') }}
                    </button>
                    <p class="vmc-or"><span>{{ t('or') }}</span></p>
                </template>

                <form v-if="!codeSent" class="vmc-stack" novalidate @submit.prevent="sendCode">
                    <div style="display: none" aria-hidden="true">
                        <input v-model="website" type="text" :name="config.honeypotField" tabindex="-1" autocomplete="off">
                    </div>
                    <div>
                        <label class="vmc-label" for="vmc-email">{{ t('email_label') }}</label>
                        <input id="vmc-email" v-model="email" class="vmc-field" type="email" inputmode="email" autocomplete="email" dir="ltr" placeholder="you@example.com" required>
                        <p class="vmc-hint">{{ t('code_hint', { minutes: config.codeMinutes || 10 }) }}</p>
                    </div>
                    <button type="submit" class="vmc-btn vmc-btn-primary" :disabled="busy || !emailLooksRight">{{ t('send_code') }}</button>
                </form>

                <form v-else class="vmc-stack" novalidate @submit.prevent="verifyCode">
                    <p class="vmc-note">{{ t('code_sent', { email: sentTo }) }}</p>
                    <div>
                        <label class="vmc-label" for="vmc-code">{{ t('code_label') }}</label>
                        <input id="vmc-code" ref="codeInput" v-model="code" class="vmc-field vmc-code" type="text" inputmode="numeric"
                               autocomplete="one-time-code" maxlength="6" pattern="[0-9]*" dir="ltr" required>
                    </div>
                    <button type="submit" class="vmc-btn vmc-btn-primary" :disabled="busy || code.length < 6">{{ t('send') }}</button>
                    <div class="vmc-row">
                        <button type="button" class="vmc-btn vmc-btn-quiet" :disabled="busy" @click="codeSent = false">{{ t('other_email') }}</button>
                        <button type="button" class="vmc-btn vmc-btn-quiet" :disabled="busy" @click="sendCode">{{ t('resend') }}</button>
                    </div>
                </form>

                <!-- Outside both forms, so it stays for "Send a new code" and "Use another email". -->
                <div v-if="config.turnstileSiteKey" ref="turnstile"></div>

                <p v-if="config.terms" class="vmc-hint">
                    {{ termsBefore }}<a :href="config.terms.terms" target="_blank" rel="noopener" class="vmc-link">{{ t('terms_link') }}</a>{{ termsMiddle }}<a :href="config.terms.privacy" target="_blank" rel="noopener" class="vmc-link">{{ t('privacy_link') }}</a>{{ termsAfter }}
                </p>

            </div>
        </template>
    </div>
</template>

<script>
import ClaimHome from './ClaimHome.vue';
import ClaimIcon from './ClaimIcon.vue';
import SchematicMap from './SchematicMap.vue';
import { choose } from '../plural';
import AreaMap from './AreaMap.vue';

/**
 * The vendor link, phone first: "Is one of these you?", then about you, then (last) an account by
 * Google or an emailed code, then "Sent to Dana". Signed in with a stall for the season, it is the
 * vendor's home instead (ClaimHome).
 *
 * Answers are kept in this browser (localStorage, best effort) so a lost signal or a trip to the
 * mail app loses nothing; they are cleared once the request is sent.
 */
const IN_APP = /Instagram|FBAN|FBAV|FB_IAB|FBIOS|FB4A/i;

function fold(s) {
    return String(s || '')
        .normalize('NFKD')
        // Latin accents and Hebrew points and cantillation marks, so "קפה" finds "קָפֶה".
        .replace(/[̀-֑ͯ-ׇً-ٟ]/g, '')
        .toLocaleLowerCase();
}

export default {
    name: 'ClaimApp',
    components: { ClaimHome, ClaimIcon, SchematicMap, AreaMap },
    props: {
        config: { type: Object, required: true },
    },
    data() {
        const c = this.config;
        return {
            screen: c.home ? 'home' : (c.waiting ? 'waiting' : 'list'),
            waiting: c.waiting,
            error: c.error || '',
            query: '',
            showMap: true,
            pickedId: null,
            mode: null,
            form: { name: '', categories: [], sells: '', phone: '', every: true, dates: [], page: c.pages && c.pages.length ? c.pages[0].id : '' },
            photo: { ref: null, preview: null, progress: 0, uploading: false, error: '' },
            showDates: false,
            email: '',
            code: '',
            codeSent: false,
            sentTo: '',
            website: '',
            turnstileToken: '',
            turnstileId: null,
            busy: false,
            copied: false,
            copiedBack: false,
            photoXhr: null,
            // A screen change made by the phone's back button is not pushed again.
            fromHistory: false,
            prefillFor: null,
            // Joining is three steps, or two for somebody already signed in when the page opened.
            totalSteps: this.config.signedIn && !(this.config.waiting && this.config.waiting.justSent) ? 2 : 3,
            linkBack: this.config.linkBack || '',
        };
    },
    computed: {
        /** The three steps of joining (two when already signed in) draw a header of their own. */
        stepped() {
            return ['about', 'account'].includes(this.screen) || (this.screen === 'waiting' && !!this.waiting);
        },
        maxCategories() {
            return 2;
        },
        emailLooksRight() {
            return /^\S+@\S+\.\S+$/.test(this.email.trim());
        },
        linkBackShort() {
            return String(this.linkBack).replace(/^https?:\/\//, '');
        },
        strings() {
            return this.config.strings || {};
        },
        spots() {
            return [...(this.config.spots || [])].sort((a, b) => String(a.label).localeCompare(String(b.label), undefined, { numeric: true }));
        },
        picked() {
            return this.spots.find((s) => s.id === this.pickedId) || null;
        },
        filtered() {
            const q = fold(this.query.trim());
            if (!q) return this.spots;
            return this.spots.filter((s) => fold(s.label).startsWith(q) || fold(s.label) === q || (s.name && fold(s.name).includes(q)));
        },
        resultsSay() {
            return this.query ? choose((this.config.areaMap || {}).results || ':count', this.filtered.length) : '';
        },
        flags() {
            const out = {};
            this.spots.forEach((s) => { out[s.id] = s; });
            return out;
        },
        spotNames() {
            const out = {};
            // What a stall is, said with it: a screen reader hears "Stall 14, Free".
            this.spots.forEach((s) => {
                const state = s.waiting ? this.t('someone_asked') : (s.page && !s.mine ? this.t('taken_short') : (s.name ? '' : this.t('free')));
                out[s.id] = [this.spotLabel(s), s.name, state].filter(Boolean).join(', ');
            });
            return out;
        },
        /**
         * The market as blocks, each stall marked for a vendor choosing: free, taken (a vendor
         * with a page), asked for by somebody else, or a name the organizer put there.
         */
        /** Every area of the market, in reading order. */
        claimZones() {
            const out = [];
            (this.claimSchematic.sections || []).forEach((sec) => {
                if (sec.type === 'full') out.push(sec.zone);
                else if (sec.type === 'stack') out.push(...sec.zones);
                else out.push(...sec.a, ...sec.b);
            });
            return out;
        },
        /** More than one area, or more stalls than read as numbers at a phone's width. */
        layered() {
            const stalls = this.claimZones.reduce((n, z) => n + z.cells.filter(Boolean).length, 0);
            return this.claimZones.length > 1 || stalls > 30;
        },
        /** "Is this your stall?" shows the area it is in, not the whole market. */
        confirmSchematic() {
            if (!this.layered || !this.picked) return this.claimSchematic;
            const zone = this.claimZones.find((z) => z.cells.some((c) => c && String(c.id) === String(this.picked.id)));
            return zone ? { sections: [{ type: 'full', zone }], amenities: [], here: false } : this.claimSchematic;
        },
        claimSchematic() {
            const src = this.config.schematic || { sections: [], amenities: [], here: false };
            const state = (c) => {
                const s = this.flags[c.id];
                if (!s) return c.state;
                if (s.waiting) return 'waiting';
                if (s.page && !s.mine) return 'taken';
                if (s.name) return 'named';
                return 'open';
            };
            const mapZone = (z) => ({ ...z, cells: z.cells.map((c) => (c ? { ...c, state: state(c) } : c)) });
            return {
                ...src,
                here: false,
                sections: src.sections.map((sec) => (sec.type === 'full'
                    ? { ...sec, zone: mapZone(sec.zone) }
                    : sec.type === 'stack' ? { ...sec, zones: sec.zones.map(mapZone) } : { ...sec, a: sec.a.map(mapZone), b: sec.b.map(mapZone) })),
            };
        },
        inApp() {
            return IN_APP.test(navigator.userAgent || '');
        },
        chromeIntent() {
            if (!/Android/i.test(navigator.userAgent || '')) return null;
            const l = window.location;
            return `intent://${l.host}${l.pathname}${l.search}#Intent;scheme=https;package=com.android.chrome;end`;
        },
        ringDash() {
            const c = 2 * Math.PI * 15;
            return `${(c * this.photo.progress) / 100} ${c}`;
        },
        termsParts() {
            const s = this.t('terms_line');
            const a = s.indexOf(':terms');
            const b = s.indexOf(':privacy');
            if (a < 0 || b < 0 || b < a) return [s + ' ', ' ', ''];
            return [s.slice(0, a), s.slice(a + 6, b), s.slice(b + 8)];
        },
        termsBefore() { return this.termsParts[0]; },
        termsMiddle() { return this.termsParts[1]; },
        termsAfter() { return this.termsParts[2]; },
    },
    watch: {
        form: { deep: true, handler() { this.save(); } },
        screen(now, before) {
            this.save();
            window.scrollTo({ top: 0 });
            // The phone's back button steps back through the screens, not off the page.
            if (this.fromHistory) {
                this.fromHistory = false;
            } else if (!(history.state && history.state.vmcScreen === now)) {
                try { history.pushState({ vmcScreen: now }, ''); } catch (e) { /* not allowed */ }
            }
            // A screen reader starts on the new screen's title.
            this.$nextTick(() => {
                const title = this.$el && this.$el.querySelector && this.$el.querySelector('.vmc-title');
                if (title) { title.setAttribute('tabindex', '-1'); title.focus({ preventScroll: true }); }
            });
            if (before === 'account' && now !== 'waiting') {
                // A code sent for these answers is not used for different ones: the vendor sends
                // a new one when they come back.
                this.codeSent = false;
                this.code = '';
            }
            if (now !== 'account') this.turnstileId = null;
            if (now === 'account') this.$nextTick(this.mountTurnstile);
        },
        error(now) {
            // The message is at the top of a long form; the button that caused it is at the foot.
            if (now) this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
        },
    },
    mounted() {
        this.restore();
        // Back from Google with the request sent: what was kept for a lost signal is done with.
        if (this.config.waiting && this.config.waiting.justSent) this.forget();
        try { history.replaceState({ vmcScreen: this.screen }, ''); } catch (e) { /* not allowed */ }
        this.onPop = (e) => {
            const to = e.state && e.state.vmcScreen;
            if (!to || to === this.screen) return;
            // Never back into a step that needs something gone (a sent request, a picked stall).
            if (['waiting', 'stopped'].includes(to) || (['confirm', 'about', 'account'].includes(to) && !this.picked)) return;
            this.fromHistory = true;
            this.screen = to;
        };
        window.addEventListener('popstate', this.onPop);
    },
    beforeUnmount() {
        window.removeEventListener('popstate', this.onPop);
    },
    methods: {
        freeIn(zone) {
            return choose((this.config.areaMap || {}).areaFree || ':count', zone.cells.filter((c) => c && c.state === 'open').length);
        },
        spotAriaById(id) {
            return this.spotNames[id] || String(id);
        },
        selectableId(id) {
            const s = this.flags[id];
            return !!s && !s.waiting;
        },
        t(key, repl = {}) {
            let s = (this.config.t || {})[key] || key;
            Object.keys(repl).forEach((k) => { s = s.split(':' + k).join(repl[k]); });
            return s;
        },
        spotLabel(s) {
            return String(this.strings.spot || ':number').split(':number').join(s.label);
        },

        /* Answers kept through a lost signal. */
        save() {
            if (!['about', 'account', 'confirm'].includes(this.screen)) return;
            try {
                localStorage.setItem(this.config.storageKey, JSON.stringify({
                    spot: this.pickedId, mode: this.mode, form: this.form, screen: this.screen,
                    photo: this.photo.ref, email: this.email, at: Date.now(),
                }));
            } catch (e) { /* private mode */ }
        },
        restore() {
            if (this.screen !== 'list') return;
            let saved = null;
            try { saved = JSON.parse(localStorage.getItem(this.config.storageKey) || 'null'); } catch (e) { saved = null; }
            if (!saved || !saved.spot || Date.now() - (saved.at || 0) > 86400000) return;
            if (!this.spots.find((s) => s.id === saved.spot && !s.waiting)) return;
            this.pickedId = saved.spot;
            this.mode = saved.mode;
            this.form = Object.assign({}, this.form, saved.form || {});
            if (!this.config.signedIn) this.form.page = '';
            // A photo is not restored: its preview is gone, and one nobody can see is not sent.
            this.email = saved.email || '';
            this.screen = saved.screen === 'account' && this.config.signedIn ? 'about' : (saved.screen || 'about');
        },
        forget() {
            try { localStorage.removeItem(this.config.storageKey); } catch (e) { /* nothing kept */ }
        },

        pick(s) {
            if (s.waiting) return;
            this.error = '';
            this.pickedId = s.id;
            this.screen = 'confirm';
        },
        pickById(id) {
            const s = this.spots.find((x) => x.id === id);
            if (s) this.pick(s);
        },
        back(to) {
            this.error = '';
            this.screen = to;
        },
        startAbout(mode) {
            this.mode = mode;
            const s = this.picked;
            const first = this.config.pages && this.config.pages[0];
            if (mode === 'me') {
                // Filled from the stall's name once; what the vendor then types is theirs.
                if (this.prefillFor !== s.id) {
                    this.form.name = s.name;
                    this.form.sells = s.sells || '';
                    this.form.categories = (s.categories || []).filter((c) => c in this.config.categories).slice(0, 2);
                    this.prefillFor = s.id;
                }
            } else {
                // Another vendor's name and goods never carry over to a free stall.
                if (this.prefillFor !== null) {
                    this.form.name = '';
                    this.form.sells = '';
                    this.form.categories = [];
                    this.prefillFor = null;
                }
                if (!this.form.name) this.form.name = first ? first.name : (this.config.userName || '');
            }
            this.screen = 'about';
        },
        pageChosen(p) {
            if (this.mode !== 'me') this.form.name = p.name;
        },
        toggleCategory(slug) {
            const list = this.form.categories;
            const i = list.indexOf(slug);
            if (i >= 0) list.splice(i, 1);
            else if (list.length < this.maxCategories) list.push(slug);
        },
        startOver() {
            this.waiting = null;
            this.pickedId = null;
            if (this.config.signedIn) this.totalSteps = 2;
            this.screen = 'list';
        },

        payload(extra) {
            return Object.assign({
                spot: this.pickedId,
                name: this.form.name,
                categories: this.form.categories,
                sells: this.form.sells,
                phone: this.form.phone,
                photo: this.photo.ref,
                every: this.config.oneDay ? true : this.form.every,
                dates: this.form.every ? [] : this.form.dates,
                page: this.config.signedIn ? this.form.page : null,
            }, extra);
        },
        aboutDone() {
            this.error = '';
            if (!this.form.name.trim()) {
                this.error = this.t('name_required');
                return;
            }
            if (!this.form.categories.length) {
                this.error = this.t('pick_category');
                return;
            }
            if (!this.config.oneDay && !this.form.every && !this.form.dates.length) {
                this.error = this.t('pick_date');
                this.showDates = true;
                return;
            }
            if (this.config.signedIn) {
                this.submit('claim');
            } else {
                this.screen = 'account';
            }
        },
        async submit(action, extra = {}) {
            this.busy = true;
            this.error = '';
            try {
                const res = await this.post(this.config.urls.submit, this.payload(Object.assign({ action }, extra)));
                return this.handle(res);
            } catch (e) {
                this.error = e.message;
                if (e.closed) window.location.reload();
                return null;
            } finally {
                this.busy = false;
            }
        },
        handle(res) {
            if (res.redirect) {
                window.location.assign(res.redirect);
                return res;
            }
            if (res.signedIn) this.config.signedIn = true;
            if (res.error) {
                // Signed in, but the stall went to somebody else meanwhile.
                this.error = res.error;
                this.codeSent = false;
                this.code = '';
                if (res.back) this.screen = res.back;
                return res;
            }
            if (res.waiting) {
                this.forget();
                // The photo went with this request; the next one starts without it.
                this.photo.ref = null;
                this.photo.preview = null;
                this.waiting = res.waiting;
                this.screen = 'waiting';
                if (res.signedIn) this.config.signedIn = true;
            }
            return res;
        },
        google() {
            this.submit('google', { [this.config.honeypotField]: this.website });
        },
        async sendCode() {
            this.error = '';
            if (this.config.turnstileSiteKey && !this.turnstileToken) {
                this.error = this.t('turnstile_wait');
                return;
            }
            const res = await this.submit('send_code', {
                email: this.email.trim(),
                [this.config.honeypotField]: this.website,
                'cf-turnstile-response': this.turnstileToken,
            });
            if (window.turnstile && this.turnstileId !== null) {
                try { window.turnstile.reset(this.turnstileId); } catch (e) { /* gone */ }
                this.turnstileToken = '';
            }
            if (res && res.sent) {
                this.sentTo = res.email;
                this.codeSent = true;
                this.code = '';
                this.$nextTick(() => this.$refs.codeInput && this.$refs.codeInput.focus());
            }
        },
        verifyCode() {
            this.submit('verify_code', { code: this.code.replace(/\D/g, '') });
        },
        mountTurnstile() {
            const key = this.config.turnstileSiteKey;
            if (!key || this.turnstileId !== null) return;
            const tryRender = (attempt) => {
                const el = this.$refs.turnstile;
                if (!el) return;
                if (window.turnstile && typeof window.turnstile.render === 'function') {
                    el.innerHTML = '';
                    this.turnstileId = window.turnstile.render(el, {
                        sitekey: key,
                        callback: (token) => { this.turnstileToken = token; },
                        'expired-callback': () => { this.turnstileToken = ''; },
                    });
                } else if (attempt < 50) {
                    setTimeout(() => tryRender(attempt + 1), 200);
                }
            };
            tryRender(0);
        },

        /* The photo: shrunk in the browser, uploaded on pick, kept private until confirmed. */
        async photoPicked(ev) {
            const file = ev.target.files && ev.target.files[0];
            ev.target.value = '';
            if (!file) return;
            this.photo.error = '';
            const name = (file.name || '').toLowerCase();
            if (/hei[cf]/.test(file.type || '') || /\.hei[cf]$/.test(name)) {
                this.photo.error = this.t('photo_heic');
                return;
            }
            let blob;
            try {
                blob = await this.shrink(file);
            } catch (e) {
                this.photo.error = this.t('photo_heic');
                return;
            }
            if (this.photo.preview) URL.revokeObjectURL(this.photo.preview);
            this.photo.preview = URL.createObjectURL(blob);
            this.photo.ref = null;
            this.upload(blob);
        },
        shrink(file) {
            return new Promise((resolve, reject) => {
                const url = URL.createObjectURL(file);
                const img = new Image();
                img.onload = () => {
                    const max = 1600;
                    const scale = Math.min(1, max / Math.max(img.naturalWidth, img.naturalHeight));
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(img.naturalWidth * scale));
                    canvas.height = Math.max(1, Math.round(img.naturalHeight * scale));
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#fff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    URL.revokeObjectURL(url);
                    canvas.toBlob((b) => (b ? resolve(b) : reject(new Error('blob'))), 'image/jpeg', 0.85);
                };
                img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('decode')); };
                img.src = url;
            });
        },
        upload(blob) {
            const fd = new FormData();
            fd.append('photo', blob, 'photo.jpg');
            // A newer photo replaces an upload still on its way: the older answer is ignored.
            if (this.photoXhr) { try { this.photoXhr.abort(); } catch (e) { /* done */ } }
            const xhr = new XMLHttpRequest();
            this.photoXhr = xhr;
            const current = () => this.photoXhr === xhr;
            this.photo.uploading = true;
            this.photo.progress = 0;
            xhr.open('POST', this.config.urls.photo);
            xhr.setRequestHeader('X-CSRF-TOKEN', this.csrf());
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) this.photo.progress = Math.round((e.loaded / e.total) * 100);
            };
            xhr.onload = () => {
                if (!current()) return;
                this.photoXhr = null;
                this.photo.uploading = false;
                let body = {};
                try { body = JSON.parse(xhr.responseText || '{}'); } catch (e) { body = {}; }
                if (xhr.status >= 200 && xhr.status < 300 && body.ref) {
                    this.photo.ref = body.ref;
                    this.photo.progress = 100;
                    this.save();
                } else {
                    this.photo.error = this.firstError(body) || this.t('photo_failed');
                    this.clearPhoto(true);
                }
            };
            xhr.onerror = () => {
                if (!current()) return;
                this.photoXhr = null;
                this.photo.uploading = false;
                this.photo.error = this.t('no_signal');
                this.clearPhoto(true);
            };
            xhr.send(fd);
        },
        clearPhoto(keepError) {
            if (this.photo.preview) URL.revokeObjectURL(this.photo.preview);
            this.photo.preview = null;
            this.photo.ref = null;
            if (keepError !== true) this.photo.error = '';
        },

        /* Sharing. */
        async share(s) {
            if (!s) return;
            if (navigator.share) {
                try {
                    await navigator.share({ text: s.text, url: s.url });
                    return;
                } catch (e) {
                    if (e && e.name === 'AbortError') return;
                }
            }
            this.copy(s.text + ' ' + s.url);
        },
        async copyBack() {
            try {
                await navigator.clipboard.writeText(this.linkBack || window.location.href);
                this.copiedBack = true;
                setTimeout(() => { this.copiedBack = false; }, 1600);
            } catch (e) { /* nothing to do */ }
        },
        copyLink() {
            this.copy(window.location.href);
        },
        async copy(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            } catch (e) {
                window.prompt('', text);
            }
        },

        /* One way to talk to the server. Throws an Error whose message can be shown. */
        csrf() {
            const m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute('content') : '';
        },
        firstError(body) {
            if (body && body.errors) {
                const first = Object.values(body.errors)[0];
                if (Array.isArray(first) && first.length) return String(first[0]);
            }
            return body && body.message ? String(body.message) : '';
        },
        async post(url, body) {
            let res;
            try {
                res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });
            } catch (e) {
                throw new Error(this.t('no_signal'));
            }
            let data = {};
            try { data = await res.json(); } catch (e) { data = {}; }
            if (res.ok) return data;
            const err = new Error(res.status === 419 ? this.t('expired') : (this.firstError(data) || this.t('failed')));
            err.closed = res.status === 410;
            throw err;
        },
    },
};
</script>
