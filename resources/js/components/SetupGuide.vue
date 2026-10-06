<template>
  <div class="sr-only" aria-live="polite">{{ spoken }}</div>

  <!-- The dashboard: the guide's home, in the page, and the ONE card for "what next". It holds
       the guide and the suggestions for the person's schedules (SetupGuideSuggestions): the
       one about the guide's own schedule as a plain line inside the guide, the others as a
       band under it. When the guide leaves and rows remain, they stay as the list. -->
  <template v-if="surface === 'section'">
    <SetupGuideNotice
      v-if="off && offNotice"
      :text="t.off_notice"
      :undo="t.undo"
      :link="{ label: t.settings, href: sug.urls.settings }"
      @undo="turnOnAll"
      @hold="sug.holdOff"
      @release="sug.startOff"
    />

    <template v-else-if="! off">
      <div v-if="! hidden" class="sg-root sg-section sg-surface" :class="{ 'sg-enter': ! calm, 'sg-section-quiet': folded, 'sg-section-done': finished, 'sg-section-banded': banded && ! folded }">
        <!-- Quiet (after 72 hours): one list with no label. The guide's line first, then its
             schedule's own suggestion as a row like any other (folded away it would go unseen
             for four weeks), then the other schedules. -->
        <template v-if="folded">
          <div class="sg-quiet-row">
            <button type="button" class="sg-quiet" :aria-label="t.show_all" aria-expanded="false" @click="unfolded = true">
              <SetupGuideRing :size="40" :total="ringTotal" :done="ringDone" :fused="ringFused" :photo="g.photo" :initial="g.initial" />
              <span class="min-w-0 flex-1 text-start">
                <span class="sg-eyebrow">{{ status }}</span>
                <span class="sg-quiet-title">{{ next }}</span>
              </span>
              <svg class="sg-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
            </button>
            <!-- Beside the line, not inside it: the line is itself a button. -->
            <button type="button" class="sg-row-x" :aria-label="t.hide" :title="t.hide" @click="hide">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
          </div>

          <SetupGuideNotice v-if="batch" flat :text="t.suggestions_dismissed" :undo="t.undo" @undo="sug.undoAll" @hold="sug.holdBatch" @release="sug.startBatch" />
          <SetupGuideSuggestions
            v-else-if="sugLive.length"
            class="sg-quiet-rows"
            :rows="sugLive"
            :t="t"
            :st="st"
            :limit="3"
            :foot-all="sugOpen.length > 1"
            @dismiss="sug.dismiss"
            @undo="sug.undo"
            @all="sug.dismissAll(sugLive)"
            @hold="sug.holdStubs"
            @release="sug.startStubs"
          />
        </template>
        <template v-else>
          <div class="sg-head-static sg-section-head">
            <SetupGuideRing :size="44" :total="ringTotal" :done="ringDone" :fused="ringFused" :closed="ended" :check="ended && ! handed" :bump="ended" :photo="g.photo" :initial="g.initial" />
            <span class="min-w-0 flex-1">
              <!-- Finished: the ring and the finish line ARE the header. The name is in that line
                   already, so it is not said a second time above it. -->
              <template v-if="finished">
                <span class="sg-finish-title" role="progressbar" :aria-label="t.progress" :aria-valuemin="0" :aria-valuemax="ringTotal" :aria-valuenow="ringDone" :aria-valuetext="status">{{ fmt(t.set_up) }}</span>
                <span class="sg-step-text">{{ fmt(t.events_count, { count: g.total }) }}</span>
              </template>
              <template v-else>
                <span class="sg-eyebrow" role="progressbar" :aria-label="t.progress" :aria-valuemin="0" :aria-valuemax="ringTotal" :aria-valuenow="ringDone" :aria-valuetext="status">{{ status }}</span>
                <span class="sg-head-name"><bdi>{{ g.name }}</bdi></span>
              </template>
            </span>
          </div>

          <div class="sg-section-steps">
            <div v-if="finished" class="sg-finish sg-finish-inset">
              <div class="sg-buttons">
                <SetupGuideAction :action="{ label: t.view_page, href: g.url, target: '_blank' }" kind="secondary" />
                <SetupGuideAction :action="{ label: t.add_next_event, href: g.urls.add }" kind="primary" />
              </div>
              <!-- What used to be a second panel under "is set up" is its "what next": first
                   under the buttons, above the two links. -->
              <SetupGuideSuggestions v-if="ownRows.length" plain class="sg-own" :rows="ownRows" :t="t" :st="st" @dismiss="sug.dismiss" @undo="sug.undo" @hold="sug.holdStubs" @release="sug.startStubs" />
              <div class="sg-links">
                <SetupGuideAction :action="{ label: t.add_photo, href: g.urls.style }" kind="link" />
                <SetupGuideAction :action="{ label: t.animate_list, href: g.urls.style }" kind="link" />
                <SetupGuideAction :action="{ label: t.dismiss, act: 'dismiss' }" kind="quiet" @act="act" />
              </div>
            </div>
            <SetupGuideSteps v-else :rows="rows" :only="compact && ! allSteps ? current : ''" :guide="g" :t="t" :csrf="csrf" :current="current" :copied="copied" :fresh="freshSteps" @act="act" @copy="copy" />

            <!-- The guide's own schedule: one line, outside the steps (whose one-step view on a
                 phone would filter it out), with no second circle and no second name. -->
            <SetupGuideSuggestions v-if="! finished && ownRows.length" plain class="sg-own sg-own-steps" :rows="ownRows" :t="t" :st="st" @dismiss="sug.dismiss" @undo="sug.undo" @hold="sug.holdStubs" @release="sug.startStubs" />

            <!-- Not on a finished guide: empty, its padding is a blank band under the links, and
                 what a finished guide offers is "Dismiss", in the links above. -->
            <div v-if="! finished" class="sg-section-foot">
              <button v-if="offersAllSteps" type="button" class="sg-hide" aria-expanded="false" @click="allSteps = true">{{ t.show_all }}</button>
              <button type="button" class="sg-hide" @click="hide">{{ t.hide }}</button>
            </div>
          </div>
          <div class="sg-section-page">
            <SetupGuideStrip :guide="g" :t="t" layout="row" roomy :fresh="freshEvents" :calm="calm" :embedded-now="embeddedNow" />
          </div>

          <!-- The person's other schedules. "Dismiss all" here is about these rows and never
               about the guide, which keeps its own "Hide setup guide" above. -->
          <div v-if="banded" class="sg-section-also">
            <SetupGuideNotice v-if="batch" flat :text="t.suggestions_dismissed" :undo="t.undo" @undo="sug.undoAll" @hold="sug.holdBatch" @release="sug.startBatch" />
            <SetupGuideSuggestions
              v-else
              :label="t.other_schedules"
              :rows="otherRows"
              :t="t"
              :st="st"
              :limit="3"
              :foot-all="otherOpen > 1"
              @dismiss="sug.dismiss"
              @undo="sug.undo"
              @all="sug.dismissAll(otherRows)"
              @hold="sug.holdStubs"
              @release="sug.startStubs"
            />
          </div>
        </template>
      </div>

      <!-- Hidden from the dashboard: the way back, for ten seconds, where the guide was. Not the
           corner bar the other pages use: on the dashboard that corner is the support chat's
           launcher, and the bar's Undo button sat exactly under it. "Turn off suggestions" is
           offered only when hiding the guide has emptied the card. -->
      <SetupGuideNotice
        v-else-if="undo"
        ref="undoNotice"
        :text="t.hidden"
        :undo="t.undo"
        :link="sugLive.length ? null : { label: t.turn_off }"
        @link="turnOffAll"
        @undo="restore"
        @hold="holdUndo"
        @release="startUndo"
      />

      <!-- The guide has left and rows remain: they stay, as the list. -->
      <template v-if="hidden">
        <SetupGuideNotice
          v-if="batch"
          class="sg-after"
          :text="t.suggestions_dismissed"
          :undo="t.undo"
          :link="{ label: t.turn_off }"
          @link="turnOffAll"
          @undo="sug.undoAll"
          @hold="sug.holdBatch"
          @release="sug.startBatch"
        />
        <div v-else-if="sugLive.length" class="sg-root sg-list sg-surface sg-after">
          <SetupGuideSuggestions
            :title="finished"
            :label="finished ? st.next_steps : t.other_schedules"
            :rows="sugLive"
            :t="t"
            :st="st"
            :limit="8"
            :foot-all="sugOpen.length > 1"
            :foot-off="sugOpen.length === 0"
            @dismiss="sug.dismiss"
            @undo="sug.undo"
            @all="sug.dismissAll(sugLive)"
            @off="turnOffAll"
            @hold="sug.holdStubs"
            @release="sug.startStubs"
          />
        </div>
      </template>
    </template>
  </template>

  <!-- The Schedule tab after an event lands: one stage, ending on one forward button. -->
  <div v-else-if="surface === 'panel' && ! hidden" class="sg-root sg-panel ap-card rounded-xl p-6" :class="{ 'sg-enter': ! calm }" role="status">
    <div class="flex items-start gap-3">
      <span ref="burst" class="shrink-0">
        <SetupGuideRing :size="44" :total="stageRing.total" :done="stageRing.done" :fused="stageRing.fused" :drawing="stageRing.drawing" :closed="stageRing.closed" :bump="stageRing.closed" :photo="g.photo" :initial="g.initial" />
      </span>
      <div class="min-w-0 flex-1">
        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">{{ panelTitle }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ panelBody }}</p>
      </div>
    </div>

    <SetupGuideStrip class="mt-4" :guide="g" :t="t" layout="row" roomy :fresh="freshEvents" :announce="announce" :calm="calm" :embedded-now="embeddedNow" />

    <div class="mt-4 flex flex-wrap items-center gap-3">
      <SetupGuideAction v-if="panelQuiet" :action="panelQuiet" kind="quiet" />
      <span class="hidden flex-1 sm:block"></span>
      <SetupGuideAction :action="{ label: t.view_page, href: g.url, target: '_blank' }" kind="secondary" />
      <SetupGuideAction :action="forward" kind="primary" :shine="shine" @act="act" />
    </div>
  </div>

  <!-- Inside the import panel: the same picture, in the panel that already says what happened. -->
  <div v-else-if="surface === 'strip' && ! hidden" ref="burst" class="sg-root" :class="{ 'sg-enter': ! calm }">
    <SetupGuideStrip :guide="g" :t="t" layout="row" roomy :fresh="freshEvents" :announce="announce" :calm="calm" />
  </div>

  <!-- The first-event form: the guide open beside the fields, or one line above the title. -->
  <template v-else-if="surface === 'dock' && ! hidden">
    <button data-sg-toggle type="button" class="sg-root sg-line" :aria-expanded="cardOpen ? 'true' : 'false'" @click="openCard">
      <SetupGuideRing :size="20" :total="3" :done="2" :drawing="arrival ? 1 : -1" :initial="''" />
      <span>{{ status }}</span>
      <svg class="sg-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
    </button>

    <aside v-if="dock.fits && ! dock.folded" class="sg-root sg-dock sg-surface" :class="{ 'sg-dock-enter': arrival && ! calm }" :style="dockStyle" :aria-label="t.title">
      <div class="sg-dock-head">
        <SetupGuideRing :size="28" :total="3" :done="2" :drawing="arrival ? 1 : -1" :photo="g.photo" :initial="g.initial" />
        <span class="sg-eyebrow min-w-0 flex-1" role="progressbar" :aria-label="t.progress" :aria-valuemin="0" :aria-valuemax="3" :aria-valuenow="2" :aria-valuetext="status">{{ status }}</span>
        <button type="button" class="sg-icon-button" :aria-label="t.minimize" @click="foldDock">
          <svg class="sg-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
        </button>
      </div>
      <SetupGuideStrip :guide="g" :t="t" layout="stack" :mirror="mirror" :calm="calm" here />
      <p class="sg-dock-promise">{{ fmt(g.type === 'curator' ? t.event_body_curator : t.event_body) }}</p>
      <a v-if="g.type === 'curator'" :href="g.urls.import" class="sg-dock-link">{{ t.import_events }}</a>
    </aside>

    <div v-else-if="wide" class="sg-root sg-corner">
      <div class="sg-pill sg-pill-folded">
        <button data-sg-toggle type="button" class="sg-pill-toggle" :aria-label="pillName" :aria-expanded="cardOpen ? 'true' : 'false'" @click="dock.fits ? unfoldDock() : openCard()">
          <span class="sg-pill-label sg-surface">
            <span class="sg-eyebrow">{{ status }}</span>
            <span class="sg-pill-next">{{ next }}</span>
          </span>
          <span class="sg-pill-ring sg-surface">
            <SetupGuideRing :size="36" :total="3" :done="2" :photo="g.photo" :initial="g.initial" />
          </span>
        </button>
      </div>
    </div>
  </template>

  <!-- Everywhere else on the schedule's pages: the pill in the corner, or a chip when narrow. -->
  <template v-else-if="(surface === 'pill' || surface === 'ring') && ! hidden">
    <button v-if="g.chip" data-sg-toggle type="button" class="sg-root sg-chip ap-card" :aria-expanded="cardOpen ? 'true' : 'false'" :aria-label="pillName" @click="openCard">
      <SetupGuideRing :size="24" :total="ringTotal" :done="ringDone" :fused="ringFused" :photo="g.photo" :initial="g.initial" />
      <span class="truncate">{{ status }}</span>
      <svg class="sg-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
    </button>

    <div v-show="! cardOpen" class="sg-root sg-corner" :class="{ 'sg-enter': ! calm }">
      <div class="sg-pill" :class="{ 'sg-pill-folded': surface === 'ring' || g.quiet }">
        <button data-sg-toggle type="button" class="sg-pill-toggle" :aria-label="pillName" :aria-expanded="cardOpen ? 'true' : 'false'" @click="openCard">
          <span class="sg-pill-label sg-surface">
            <span class="sg-eyebrow">{{ status }}</span>
            <span class="sg-pill-next">{{ next }}</span>
          </span>
          <span class="sg-pill-ring sg-surface">
            <SetupGuideRing :size="36" :total="ringTotal" :done="ringDone" :fused="ringFused" :closed="ended" :check="ended && ! handed" :bump="ended" :photo="g.photo" :initial="g.initial" />
          </span>
        </button>
        <button type="button" class="sg-pill-x" :aria-label="finished ? t.dismiss : t.hide" @click="hide">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
        </button>
      </div>
    </div>
  </template>

  <!-- The card: from the pill, the chip or the line. A bottom sheet on a phone. -->
  <Transition name="sg-fade">
    <div v-if="cardOpen" class="sg-scrim" @click="closeCard"></div>
  </Transition>
  <Transition name="sg-card">
    <section v-if="cardOpen" class="sg-root sg-card sg-surface" :aria-label="t.title" @keydown.esc.stop="closeCard">
      <button ref="head" type="button" class="sg-card-head" aria-expanded="true" :aria-label="t.minimize" @click="closeCard">
        <span class="min-w-0 flex-1 text-start">
          <span class="sg-eyebrow" role="progressbar" :aria-label="t.progress" :aria-valuemin="0" :aria-valuemax="ringTotal" :aria-valuenow="ringDone" :aria-valuetext="status">{{ finished ? t.title : status }}</span>
          <span v-if="finished" class="sg-head-name">{{ fmt(t.set_up) }}</span>
          <span v-else class="sg-head-name"><bdi>{{ g.name }}</bdi></span>
        </span>
        <svg class="sg-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
        <span class="sg-card-ring">
          <SetupGuideRing :size="40" :total="ringTotal" :done="ringDone" :fused="ringFused" :closed="ended" :photo="g.photo" :initial="g.initial" />
        </span>
      </button>

      <div class="sg-card-scroll">
        <SetupGuideStrip :guide="g" :t="t" layout="row" :mirror="surface === 'dock' ? mirror : null" :fresh="freshEvents" :calm="calm" :here="g.here" :embedded-now="embeddedNow" />

        <div v-if="finished" class="sg-finish">
          <p class="sg-step-text">{{ fmt(t.events_count, { count: g.total }) }}</p>
          <div class="sg-buttons sg-buttons-stacked">
            <SetupGuideAction :action="{ label: t.view_page, href: g.url, target: '_blank' }" kind="secondary" block />
            <SetupGuideAction :action="{ label: t.add_next_event, href: g.urls.add }" kind="primary" block />
          </div>
          <div class="sg-links">
            <SetupGuideAction :action="{ label: t.add_photo, href: g.urls.style }" kind="link" />
            <SetupGuideAction :action="{ label: t.animate_list, href: g.urls.style }" kind="link" />
          </div>
        </div>
        <SetupGuideSteps v-else :rows="rows" :guide="g" :t="t" :csrf="csrf" :current="current" :copied="copied" :fresh="freshSteps" @act="act" @copy="copy" />
      </div>

      <div class="sg-card-foot">
        <button type="button" class="sg-hide" @click="hide">{{ finished ? t.dismiss : t.hide }}</button>
      </div>
    </section>
  </Transition>

  <!-- Hidden: the shape itself becomes the way back, for ten seconds. -->
  <Transition name="sg-fade">
    <div v-if="undo && surface !== 'section'" class="sg-root sg-undo sg-surface" role="status" @mouseenter="holdUndo" @mouseleave="startUndo" @focusin="holdUndo" @focusout="startUndo">
      <span>{{ t.hidden }}</span>
      <button ref="undoButton" type="button" class="sg-undo-button" @click="restore">{{ t.undo }}</button>
    </div>
  </Transition>
</template>

<script setup>
/*
 * The setup guide: what a new organizer sees between saving a first schedule and having a
 * live, shared one with a few events on it. App\Utils\SetupGuide decides who has one, what it
 * says and which shape belongs on the page (`guide.surface`); this renders that shape and
 * gives it its motion. partials/setup-guide.blade.php printed a still version of the same
 * shape first, which mounting replaces, so nothing on the page moves when this arrives.
 *
 * FOUR SIGNATURES, the same in every shape:
 *   - the segmented ring around the schedule's photo or initial (it IS the count: nothing
 *     here prints a fraction);
 *   - the date tile tearing off its top edge, for an event landing on their page and nothing else;
 *   - two curves: things arrive on --sg-arrive, things complete on --sg-complete;
 *   - one forward button per view, last, and the last beat of any sequence lands on it.
 *
 * GUARDS. Nothing moves while a form field has focus (the form only feeds slot one, on blur
 * and on change). Every reward plays once: going live is remembered on the server, the rest
 * per tab in sessionStorage. Nothing loops. Under reduced motion the words and colours still
 * change; nothing travels and there is no confetti.
 *
 * Never put transform, filter or will-change on an ancestor of the fixed shapes: it would
 * become their containing block and un-fix them (CLAUDE.md).
 */
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import SetupGuideRing from './SetupGuideRing.vue';
import SetupGuideStrip from './SetupGuideStrip.vue';
import SetupGuideSteps from './SetupGuideSteps.vue';
import SetupGuideAction from './SetupGuideAction.vue';
import SetupGuideNotice from './SetupGuideNotice.vue';
import SetupGuideSuggestions from './SetupGuideSuggestions.vue';
import { useSuggestions } from '../setup-guide-suggestions.js';

const props = defineProps({
  guide: { type: Object, required: true },
});

const g = reactive(JSON.parse(JSON.stringify(props.guide)));
const t = g.t;
const surface = g.surface;

const token = document.querySelector('meta[name="csrf-token"]');
const csrf = token ? token.content : '';
const rtl = document.documentElement.dir === 'rtl';
const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  || document.documentElement.classList.contains('es-a11y-reduce-motion');

/* ---- The suggestions the dashboard's card carries with the guide ---- */

// Only the dashboard hands them over. Everywhere else this is an empty list that does nothing.
const sug = useSuggestions(surface === 'section' ? g.suggestions : null, csrf);
const { live: sugLive, open: sugOpen, batch, off, offNotice } = sug;
const st = sug.strings;

// The guide's own schedule is one plain line inside the guide; everybody else's is the band.
const ownRows = computed(() => sugLive.value.filter((row) => row.own));
const otherRows = computed(() => sugLive.value.filter((row) => ! row.own));
const otherOpen = computed(() => otherRows.value.filter((row) => row.state === 'open').length);
// The band is there while it has rows, and while its own Undo line stands in for them.
const banded = computed(() => otherRows.value.length > 0 || !! batch.value);

// A name inside a sentence goes in a bidi isolate, so a Hebrew name in an English line (or the
// reverse) cannot reorder the words around it. The address is always left to right.
const isolate = (text) => '⁨' + text + '⁩';

// One pass, with a function: a name handed to replace() as a string is a PATTERN, in which
// "$$" is one dollar and "$&" is the placeholder itself ("Ca$$h Bingo" lost a dollar), and a
// second pass would fill a ":event" that somebody typed into their schedule's name.
const fmt = (text, values = {}) => String(text || '').replace(/:(name|address|event|count)/g, (match, key) => {
  if (key === 'name') {
    return isolate(g.name);
  }

  if (key === 'address') {
    return '⁦' + g.address + '⁩';
  }

  if (key === 'event') {
    return isolate(values.event || '');
  }

  return values.count === undefined ? '' : String(values.count);
});

/* ---- Where the person is ---- */

const order = computed(() => Object.keys(g.steps));
const pending = computed(() => order.value.filter((key) => ! g.steps[key].done && ! g.steps[key].skipped));
const current = computed(() => (g.live ? (pending.value[0] || '') : 'event'));
const remaining = computed(() => (g.live ? pending.value.length : 1));
const finished = computed(() => g.live && pending.value.length === 0);

// Three segments until the schedule is live; then one for every step, the first three fused
// into a single arc. The number of filled segments never falls.
const ringTotal = computed(() => (g.live ? 3 + order.value.length - 1 : 3));
const ringFused = computed(() => (g.live ? 3 : 0));
const ringDone = computed(() => {
  if (! g.live) {
    return 2;
  }

  return 3 + order.value.filter((key) => key !== 'event' && (g.steps[key].done || g.steps[key].skipped)).length;
});

const status = computed(() => {
  if (finished.value) {
    return fmt(t.set_up);
  }

  if (! g.live) {
    return t.one_step;
  }

  return [t.one_to_go, t.one_to_go, t.two_to_go, t.three_to_go][remaining.value] || t.three_to_go;
});

const next = computed(() => {
  if (finished.value) {
    return t.add_next_event;
  }

  if (current.value === 'event') {
    return g.draft ? t.publish : (g.type === 'curator' ? t.step_first_events : t.step_event);
  }

  if (current.value === 'events') {
    return g.total >= 2 ? t.next_third : t.next_second;
  }

  if (current.value === 'share') {
    return t.next_share;
  }

  return g.can_sell ? t.next_tickets : t.next_signup;
});

const pillName = computed(() => t.title + '. ' + status.value + '. ' + next.value);

/* ---- The steps ---- */

const stateOf = (key) => {
  const step = g.steps[key];

  if (step.done) {
    return 'done';
  }

  if (step.skipped) {
    return 'skipped';
  }

  return current.value === key ? 'current' : 'todo';
};

const skipLink = (step) => ({ label: t.skip, act: 'skip', step, quiet: true });
const undoSkip = (step) => [{ label: t.undo, act: 'unskip', step, quiet: true }];

const eventRow = () => {
  const curator = g.type === 'curator';
  const row = { key: 'event', state: 'current', title: curator ? t.step_first_events : t.step_event };

  if (g.draft) {
    row.title = g.draft.name;
    row.body = fmt(t.draft_body, { event: g.draft.name });
    row.primary = { label: t.publish, post: g.draft.publish };
    row.links = [{ label: t.open_draft, href: g.draft.edit }];

    return row;
  }

  row.body = fmt(curator ? t.event_body_curator : t.event_body);

  if (g.here) {
    row.here = true;
    row.links = [{ label: t.import_events, href: g.urls.import }];

    return row;
  }

  row.primary = curator ? { label: t.import_events, href: g.urls.import } : { label: t.add_event, href: g.urls.add };
  row.links = [curator ? { label: t.add_by_hand, href: g.urls.add } : { label: t.import_events, href: g.urls.import }];

  return row;
};

const eventsRow = () => {
  const state = stateOf('events');
  const row = { key: 'events', state, title: t.step_events };

  if (state === 'skipped') {
    row.note = t.skipped;
    row.links = undoSkip('events');
  } else if (state !== 'done') {
    row.body = g.total >= 2 ? t.events_body_one : t.events_body_two;
    row.primary = { label: t.add_another, href: g.urls.add };
    row.links = [{ label: t.import_events, href: g.urls.import }];

    if (g.urls.repeat) {
      row.links.push({ label: t.set_repeat, href: g.urls.repeat });
    }

    row.links.push(skipLink('events'));
  }

  return row;
};

const shareRow = () => {
  const state = stateOf('share');
  const row = { key: 'share', state, title: t.step_share };

  if (state === 'skipped') {
    row.note = t.skipped;
    row.links = undoSkip('share');

    return row;
  }

  // Done once the address is copied - but the embed is why most people came, so the row keeps
  // offering it until the embed code has been copied too.
  if (state === 'done' && g.embedded) {
    return row;
  }

  row.address = true;
  row.body = state === 'done' ? '' : t.share_body;
  row.hint = t.embed_body;
  row.primary = { label: t.add_to_website, href: g.urls.embed };

  if (state !== 'done') {
    row.links = [skipLink('share')];
  }

  return row;
};

const ticketsRow = () => {
  const state = stateOf('tickets');
  const row = { key: 'tickets', state, title: g.can_sell || state === 'done' ? t.step_tickets : t.step_signup };

  if (state === 'skipped') {
    row.note = t.skipped;
    row.links = undoSkip('tickets');
  } else if (state !== 'done') {
    const one = g.ticket_event && g.total <= 1;

    if (g.can_sell) {
      row.body = one ? fmt(t.tickets_one, { event: g.ticket_event.name }) : t.tickets_many;
    } else {
      row.body = one ? fmt(t.signup_one, { event: g.ticket_event.name }) : t.signup_many;
    }

    // Two equal answers, stacked: either one closes the question.
    row.stacked = true;
    row.secondary = { label: g.can_sell ? t.no_tickets : t.no_signup, act: 'skip', step: 'tickets' };
    row.primary = {
      label: g.can_sell ? t.add_tickets : t.add_registration,
      href: g.ticket_event ? g.ticket_event.url : g.urls.schedule,
    };
  }

  return row;
};

const rows = computed(() => {
  if (! g.live) {
    return [
      { key: 'account', state: 'done', title: t.step_account },
      { key: 'schedule', state: 'done', title: t.step_schedule },
      eventRow(),
    ];
  }

  // The first stretch, banked: one checked row rather than three.
  const list = [{ key: 'live', state: 'done', title: fmt(t.live_at) }, eventsRow(), shareRow()];

  if (g.steps.tickets) {
    list.push(ticketsRow());
  }

  return list;
});

/* ---- Talking to the server ---- */

const post = (action, step) => fetch(g.urls.endpoint, {
  method: 'POST',
  // keepalive: several of these are followed at once by a navigation.
  keepalive: true,
  credentials: 'same-origin',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-CSRF-TOKEN': csrf,
    'X-Requested-With': 'XMLHttpRequest',
  },
  body: JSON.stringify(step ? { action, step } : { action }),
}).catch(() => {});

// One at a time, in the order they happened. The last answer and "complete" are sent in the
// same instant, and the server only accepts "complete" once that answer is stored: sent side
// by side they can arrive the other way round, the finish is then not recorded, and the next
// page celebrates it a second time.
let writes = Promise.resolve();

const send = (action, step) => {
  writes = writes.then(() => post(action, step));

  return writes;
};

const spoken = ref('');
const say = (text) => {
  spoken.value = '';
  nextTick(() => {
    spoken.value = text;
  });
};

/* ---- What has already been seen, per tab ---- */

const seenKey = 'sg:' + g.role_id;
const doneKeys = () => order.value.filter((key) => g.steps[key].done);

const readSeen = () => {
  try {
    return JSON.parse(sessionStorage.getItem(seenKey) || 'null');
  } catch (error) {
    return null;
  }
};

// Written from every shape, so each field has to be one every shape knows: `folded` is read
// back on every page, not only on the form whose card it folds, or the first other page
// visited would write it back as false.
let arrived = false;

const writeSeen = () => {
  try {
    sessionStorage.setItem(seenKey, JSON.stringify({
      steps: doneKeys(),
      events: g.events.map((event) => event.id),
      folded: dock.folded,
      arrived,
    }));
  } catch (error) {
    // Private mode: every reward simply plays as if for the first time.
  }
};

const freshSteps = ref([]);
const freshEvents = ref([]);

/* ---- Sharing ---- */

const copied = ref(false);
const embeddedNow = ref(false);

const markShared = () => {
  if (g.shared || ! g.steps.share) {
    return;
  }

  g.shared = true;
  g.steps.share.done = true;
  freshSteps.value = ['share'];
  send('share');
  say(t.step_share + '. ' + t.done);
  writeSeen();
};

const markEmbedded = () => {
  if (g.embedded) {
    return;
  }

  g.embedded = true;
  g.shared = true;
  embeddedNow.value = true;

  if (g.steps.share) {
    g.steps.share.done = true;
  }

  send('embed');
  say(t.on_website);
  writeSeen();
};

const copy = () => {
  const done = () => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
    markShared();
  };

  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(g.url).then(done, done);

    return;
  }

  // An http:// selfhost install has no clipboard API. The old way still works there.
  const field = document.createElement('textarea');
  field.value = g.url;
  field.setAttribute('readonly', '');
  field.style.position = 'fixed';
  field.style.opacity = '0';
  document.body.appendChild(field);
  field.select();

  try {
    document.execCommand('copy');
  } catch (error) {
    // Nothing more to try; the address is on screen to copy by hand.
  }

  field.remove();
  done();
};

// The same step, answered anywhere in the admin portal: the schedule's own address copied
// (marked data-setup-share), or the embed dialog's code. In the capture phase, because the
// dialog's own handlers stop the click before it bubbles.
const onPageClick = (event) => {
  const target = event.target;

  if (! target || ! target.closest) {
    return;
  }

  // Hidden on this page a moment ago: the sidebar still shows the link it was rendered with,
  // which only scrolls to where the guide was (or loads a dashboard that still has it hidden).
  // The toast said "Find it in the menu", so the line brings it back: restore, then go there
  // the way the server-rendered restore form does.
  const line = target.closest('a[data-setup-guide-nav]');

  if (line && hidden.value && ! finished.value) {
    event.preventDefault();
    clearTimeout(undoTimer);
    send('restore').then(() => {
      if (line.pathname === window.location.pathname) {
        window.location.hash = 'setup-guide';
        window.location.reload();
      } else {
        window.location.assign(line.href);
      }
    });

    return;
  }

  const marked = target.closest('[data-setup-share]');

  // Only the Copy button counts, not a click that selects the field beside it.
  if (marked && target.closest('button, a') && ! target.closest('.sg-root')) {
    if (marked.getAttribute('data-setup-share') === 'embed') {
      markEmbedded();
    } else {
      markShared();
    }
  }

  if (target.closest('.js-copy-iframe-code, .js-copy-embed-url')) {
    // The dialog's other widget is the newsletter signup form, which is not the schedule.
    const widget = document.getElementById('embed-widget');

    if (! widget || widget.value !== 'subscribe') {
      markEmbedded();
    }
  }
};

/* ---- Answering, skipping, hiding ---- */

const hidden = ref(false);
const undo = ref(false);
const undoButton = ref(null);
// The dashboard's Undo line (SetupGuideNotice); the corner bar on other pages is undoButton.
const undoNotice = ref(null);
let undoTimer = 0;

// True for the instant this code moves focus to Undo after a click. That focus is not somebody
// reaching for the button, and it must not hold the bar open: it used to cancel the ten
// seconds the line before had started, so for anyone using a mouse the bar never left.
let placing = false;

const holdUndo = () => {
  if (! placing) {
    clearTimeout(undoTimer);
  }
};

const startUndo = () => {
  clearTimeout(undoTimer);
  undoTimer = setTimeout(() => {
    undo.value = false;
  }, 10000);
};

const hide = (event) => {
  cardOpen.value = false;
  hidden.value = true;
  send('dismiss');

  // A finished guide is put away, not hidden: there is nothing to bring back, and no line in
  // the sidebar to bring it back with, so no Undo that promises one. It takes only itself:
  // its schedule's own line is then a row like any other, as the next page load would show.
  if (finished.value) {
    sug.release();

    return;
  }

  // Hidden, the guide holds everything about its schedule (SetupGuide::withoutCoveredSteps()),
  // so its own line leaves with it. The other schedules' rows stay.
  sug.holdOwn(true);
  undo.value = true;
  startUndo();

  // Where it went: the sidebar line brightens once.
  document.querySelectorAll('[data-setup-guide-nav]').forEach((line) => {
    line.classList.remove('sg-nav-flash');
    line.getBoundingClientRect();
    line.classList.add('sg-nav-flash');
  });

  // Focus goes to Undo so a keyboard keeps its place, and there it does hold the bar open.
  // After a pointer's click (detail is the click count; a key press makes it zero) it is
  // only a courtesy.
  const byPointer = !! (event && event.detail > 0);

  nextTick(() => {
    if (undoNotice.value) {
      undoNotice.value.focus(byPointer);
    } else if (undoButton.value) {
      placing = byPointer;
      undoButton.value.focus({ preventScroll: true });
      placing = false;
    }
  });
};

const restore = () => {
  clearTimeout(undoTimer);
  undo.value = false;
  hidden.value = false;
  sug.holdOwn(false);
  send('restore');

  focusToggle();
};

const act = (action) => {
  if (action.act === 'copy') {
    copy();
  } else if (action.act === 'skip' && g.steps[action.step]) {
    g.steps[action.step].skipped = true;
    send('skip', action.step);
    writeSeen();

    // "No tickets needed" is an answer: its schedule is not then asked for tickets by a row.
    if (action.step === 'tickets') {
      sug.holdOwnTickets(true);
    }
  } else if (action.act === 'unskip' && g.steps[action.step]) {
    g.steps[action.step].skipped = false;
    send('unskip', action.step);

    if (action.step === 'tickets') {
      sug.holdOwnTickets(false);
    }
  } else if (action.act === 'dismiss') {
    hide();
  }
};

/* ---- The account-wide switch ---- */

// The sidebar's "Setup guide" line was printed by the server; with suggestions off it is gone
// on the next page, so it goes on this one too, and comes back with Undo.
const showNavLines = (show) => {
  document.querySelectorAll('[data-setup-guide-nav]').forEach((line) => {
    const item = line.closest('li');

    if (item) {
      item.hidden = ! show;
    }
  });
};

// "Turn off suggestions": everything, now and later. It takes the place of whatever on screen
// could still be undone - a guide hidden a moment ago is brought back first - so that turning
// suggestions on again later shows what was there.
const turnOffAll = () => {
  if (hidden.value && undo.value) {
    clearTimeout(undoTimer);
    undo.value = false;
    hidden.value = false;
    sug.holdOwn(false);
    send('restore');
  }

  sug.turnOff();
  showNavLines(false);
};

const turnOnAll = () => {
  sug.turnOn();
  showNavLines(true);
};

/* ---- The card ---- */

const cardOpen = ref(false);
const head = ref(null);

// The chip and the pill are both in the page; which one shows is a matter of width. Focus goes
// back to whichever is on screen, so it never drops to the top of the document.
const focusToggle = () => nextTick(() => {
  const toggles = document.querySelectorAll('[data-sg-toggle]');

  for (let index = 0; index < toggles.length; index++) {
    if (toggles[index].getClientRects().length) {
      toggles[index].focus({ preventScroll: true });

      return;
    }
  }
});

const openCard = () => {
  cardOpen.value = true;
  nextTick(() => {
    if (head.value) {
      head.value.focus({ preventScroll: true });
    }
  });
};

const closeCard = () => {
  cardOpen.value = false;
  focusToggle();
};

/* ---- The dashboard section ---- */

const unfolded = ref(false);
const folded = computed(() => g.quiet && ! finished.value && ! unfolded.value);

// On a phone the section shows the page and the one open step; the rest are a tap away, so the
// dashboard does not open on a full screen of checklist.
const compact = ref(window.innerWidth < 640);
const allSteps = ref(false);
const offersAllSteps = computed(() => compact.value && ! allSteps.value && ! finished.value && rows.value.length > 1);

/* ---- The docked card on the first-event form ---- */

// Below this the gutter beside the form's fields is under 178px on any layout, so the line above
// the title is shown instead. The same number is in the CSS below and in the still version
// (partials/setup-guide.blade.php), so nothing shifts when this takes over from it.
const DOCK_FROM = 1408;

const dock = reactive({ fits: false, folded: false, top: 0, side: 0, width: 216 });
const wide = ref(window.innerWidth >= DOCK_FROM);
const arrival = ref(false);
const mirror = reactive({ name: '', month: '', day: '' });

// The field column of whichever section of the form is showing.
const fieldColumn = () => {
  const sections = document.querySelectorAll('.section-content');

  for (let index = 0; index < sections.length; index++) {
    if (sections[index].getClientRects().length) {
      // The first column that is actually on screen. A section can hold a max-w-xl inside
      // something folded (the event form's About), which has no box: measured, it read as a
      // column of zero width at the window's edge, the dock was placed under the navigation, and
      // the ring was held back because the dock "fitted". So the guide vanished.
      const columns = sections[index].querySelectorAll('.max-w-xl');

      for (let at = 0; at < columns.length; at++) {
        if (columns[at].getClientRects().length) {
          return columns[at];
        }
      }

      return null;
    }
  }

  return null;
};

// Measured, not a breakpoint: the gutter beside the fields depends on the sidebar, the zoom
// level and the direction of the page. Under 178px there is no room, and it folds to the ring.
const measure = () => {
  wide.value = window.innerWidth >= DOCK_FROM;

  const column = fieldColumn();
  const main = document.getElementById('main-content');

  if (! wide.value || ! column || ! main) {
    dock.fits = false;

    return;
  }

  const fields = column.getBoundingClientRect();
  const pane = main.getBoundingClientRect();
  const gutter = 32;
  const free = rtl ? fields.left - (pane.left + gutter) : (pane.right - gutter) - fields.right;

  if (free < 178) {
    dock.fits = false;

    return;
  }

  const section = column.closest('.section-content');

  // 18px clear of the fields, 12px in from the edge of the panel the gutter belongs to, and
  // level with the section's heading rather than the panel's border.
  dock.width = Math.min(232, Math.max(160, free - 30));
  dock.side = rtl ? window.innerWidth - fields.left + 18 : fields.right + 18;
  dock.top = Math.max(88, (section || column).getBoundingClientRect().top + 16);
  dock.fits = true;
};

let frame = 0;
const scheduleMeasure = () => {
  if (frame) {
    return;
  }

  frame = requestAnimationFrame(() => {
    frame = 0;
    measure();
  });
};

const dockStyle = computed(() => ({
  top: dock.top + 'px',
  width: dock.width + 'px',
  [rtl ? 'right' : 'left']: dock.side + 'px',
}));

const foldDock = () => {
  dock.folded = true;
  writeSeen();
  focusToggle();
};

const unfoldDock = () => {
  dock.folded = false;
  writeSeen();
};

// Slot one, from what has been typed. Read on leaving the name field and when the date or the
// time changes - never on a keystroke - and built from the wall-clock parts as entered, with
// no timezone arithmetic: the form's own preview does the same.
const readForm = () => {
  const name = document.getElementById('event_name');
  const date = document.getElementById('event_date');
  const time = document.getElementById('start_time');
  const allDay = document.getElementById('is_multi_day');
  const picked = date && date._flatpickr ? date._flatpickr.selectedDates[0] : null;
  const timed = (time && time.value) || (allDay && allDay.checked);

  mirror.name = name ? name.value.trim() : '';

  if (picked && timed) {
    mirror.month = picked.toLocaleDateString(g.locale || undefined, { month: 'short' });
    mirror.day = String(picked.getDate());
  } else {
    mirror.month = '';
    mirror.day = '';
  }
};

// Any field of the event form being left or changed. A beat later, because the time picker
// writes its value after the click that chose it.
const onFormChange = (event) => {
  const target = event.target;

  if (target && target.closest && target.closest('#app')) {
    setTimeout(readForm, 60);
  }
};

/* ---- Going live, and the finish ---- */

const burst = ref(null);
const announce = ref(false);
const shine = ref(false);
const stage = ref(g.celebrate ? 'before' : 'idle');
const ended = ref(false);
const handed = ref(false);

// The panel's ring during the going-live moment: its third segment draws, then the three
// fuse into one arc. Otherwise it is simply the guide's ring.
const stageRing = computed(() => {
  if (stage.value === 'before') {
    return { total: 3, done: 2, fused: 0, drawing: -1, closed: false };
  }

  if (stage.value === 'drawing') {
    return { total: 3, done: 3, fused: 0, drawing: 2, closed: false };
  }

  if (stage.value === 'fused') {
    return { total: 3, done: 3, fused: 0, drawing: -1, closed: true };
  }

  return { total: ringTotal.value, done: ringDone.value, fused: ringFused.value, drawing: -1, closed: ended.value };
});

const newest = computed(() => g.events.reduce((latest, event) => (! latest || event.created > latest.created ? event : latest), null));

const panelTitle = computed(() => {
  if (stage.value !== 'idle') {
    return fmt(t.is_live);
  }

  // The event that just landed, by name. It may not be one of the three on the strip, which
  // shows the next three coming up.
  return g.landed ? fmt(t.on_your_page, { event: g.landed }) : t.events_on_page;
});

const panelBody = computed(() => {
  if (finished.value) {
    return fmt(t.events_count, { count: g.total });
  }

  if (current.value === 'events') {
    return g.total >= 2 ? t.events_body_one : t.events_body_two;
  }

  return current.value === 'share' ? t.share_body : next.value;
});

// One forward button. While the page wants events it adds one; then it copies the address,
// and having copied it becomes "Add to your website" where it stands.
const forward = computed(() => {
  if (current.value === 'events') {
    return { label: t.add_another, href: g.urls.add };
  }

  if (g.steps.share && ! g.steps.share.skipped) {
    if (! g.shared) {
      return { label: copied.value ? t.copied : t.copy_link, act: 'copy', done: copied.value };
    }

    if (! g.embedded) {
      return { label: t.add_to_website, href: g.urls.embed };
    }
  }

  return { label: t.add_next_event, href: g.urls.add };
});

// On the first panel only: the embed is why most people came, so it is offered from the start.
const panelQuiet = computed(() => {
  if (stage.value === 'idle' || g.embedded || forward.value.href === g.urls.embed) {
    return null;
  }

  return { label: t.add_to_website, href: g.urls.embed };
});

// The library is fetched at the moment it is needed, from this install's own files, and never
// otherwise: most page views of the guide celebrate nothing.
const fetchConfetti = (resolve) => {
  if (typeof window.confetti === 'function') {
    resolve(window.confetti);

    return;
  }

  const script = document.createElement('script');
  script.src = g.confetti;
  script.onload = () => resolve(typeof window.confetti === 'function' ? window.confetti : null);
  script.onerror = () => resolve(null);
  document.head.appendChild(script);
};

const loadConfetti = () => new Promise(fetchConfetti);

// From the middle of `source`, on a canvas of its own attached to <body>: never inside one of
// the guide's shapes, which carry transforms.
const confetti = (source, amount) => {
  if (calm || ! source) {
    return;
  }

  loadConfetti().then((library) => {
    if (! library || ! library.create) {
      return;
    }

    const box = source.getBoundingClientRect();
    const canvas = document.createElement('canvas');
    canvas.setAttribute('aria-hidden', 'true');
    canvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:65';
    document.body.appendChild(canvas);

    const blue = window.getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim() || '#4E81FA';
    const small = window.innerWidth < 640;
    // useWorker false: the library's default worker is a blob URL the CSP does not allow.
    const fire = library.create(canvas, { resize: true, useWorker: false, disableForReducedMotion: true });

    fire({
      particleCount: Math.round(amount * (small ? 0.5 : 1)),
      spread: 62,
      startVelocity: 32,
      gravity: 0.9,
      ticks: 160,
      scalar: 0.9,
      origin: {
        x: (box.left + box.width / 2) / window.innerWidth,
        y: (box.top + box.height / 2) / window.innerHeight,
      },
      colors: [blue, '#0EA5E9', '#22D3EE', '#22C55E', '#FCC73D'],
    });

    setTimeout(() => canvas.remove(), 3200);
  });
};

const whenVisible = (run) => {
  if (document.visibilityState === 'visible') {
    run();

    return;
  }

  const once = () => {
    if (document.visibilityState === 'visible') {
      document.removeEventListener('visibilitychange', once);
      run();
    }
  };

  document.addEventListener('visibilitychange', once);
};

// One stage, in series, ending on the forward button: the ring completes and fuses, confetti
// from the ring, the live dot and the address, the tiles tear off, one sheen on the button.
const goLive = () => {
  const ids = g.events.map((event) => event.id);

  whenVisible(() => {
    send('celebrated');
    g.celebrate = false;

    if (calm) {
      stage.value = 'fused';
      freshEvents.value = ids;
      say(fmt(t.is_live));

      return;
    }

    setTimeout(() => {
      stage.value = 'drawing';
    }, 180);
    setTimeout(() => {
      stage.value = 'fused';
      confetti(burst.value, 90);
    }, 800);
    setTimeout(() => {
      announce.value = true;
    }, 1000);
    setTimeout(() => {
      freshEvents.value = ids;
    }, 1200);
    setTimeout(() => {
      shine.value = true;
      say(fmt(t.is_live));
    }, 2400);
  });
};

// The last step answered: the ring's gaps close into one circle, the check draws, a second,
// smaller confetti, and the check hands back to their photo. Then it is recorded, and the
// finished guide stays for the rest of the session.
const finish = () => {
  if (ended.value) {
    return;
  }

  ended.value = true;

  if (g.retired) {
    handed.value = true;

    return;
  }

  whenVisible(() => {
    send('complete');
    say(fmt(t.set_up));
    // Finished guides have nothing left to hide: they are gone with the session.
    g.retired = true;

    if (calm) {
      handed.value = true;

      return;
    }

    const source = document.querySelector('.sg-root .sg-ring');

    setTimeout(() => confetti(source, 45), 500);
    setTimeout(() => {
      handed.value = true;
    }, 2400);
  });
};

/* ---- The dashboard's host ---- */

// The section's host reserved the height the server expected this card to have, so the page
// under it did not jump while the component loaded. That is the reservation's whole job, and it
// is over at mount: from here the card is as tall as what it holds. Kept, it was a blank band
// under a finished guide, under a card whose rows had been dismissed, and a 300px hole in the
// dashboard after "Hide setup guide".
//
// The host itself goes only when the card holds NOTHING: no guide, no Undo row, no row and no
// stub (`hidden`, not a zero height, so the dashboard's space-y rhythm skips it).
const settleHost = () => {
  const host = surface === 'section' ? document.getElementById('setup-guide') : null;

  if (! host) {
    return;
  }

  host.style.minHeight = '0';
  host.hidden = off.value
    ? ! offNotice.value
    : hidden.value && ! undo.value && ! batch.value && sugLive.value.length === 0;
};

/* ---- The cookie banner ---- */

// The banner sits along the bottom edge and, below about 1336px, reaches the corner the pill
// lives in. While it is on screen the pill (and its card, and the Undo bar) ride above it;
// when it is answered they settle back. Measured, since the banner's height follows its text.
let bannerWatch = null;

const liftAboveBanner = () => {
  const banner = document.querySelector('[data-cookie-consent]');
  const showing = banner && ! banner.hidden && banner.getClientRects().length;
  let lift = 0;

  if (showing) {
    // Only when it actually reaches the guide's corner: 400px is the card's width and margins.
    // On a wide screen the banner ends well short of it and nothing needs to move.
    const box = banner.getBoundingClientRect();
    const reaches = rtl ? box.left < 400 : box.right > window.innerWidth - 400;

    lift = reaches ? Math.round(window.innerHeight - box.top) : 0;
  }

  document.documentElement.style.setProperty('--sg-lift', lift + 'px');
};

/* ---- Life ---- */

const onKey = (event) => {
  if (event.key === 'Escape' && cardOpen.value) {
    closeCard();
  }
};

// "Add to your website", followed on the Schedule tab itself, is a link to #embed on the page
// already open: the embed dialog opens with no navigation. On a phone the guide's sheet is
// stacked above that dialog, so the sheet gets out of its way. Focus is the dialog's to take.
const onHash = () => {
  if (window.location.hash === '#embed') {
    cardOpen.value = false;
  }
};

watch([hidden, undo, off, offNotice, batch, () => sugLive.value.length], settleHost, { flush: 'post' });

watch(finished, (now) => {
  if (now && surface !== 'dock') {
    finish();
  }
});

onMounted(() => {
  const seen = readSeen();

  dock.folded = !! (seen && seen.folded);
  arrived = !! (seen && seen.arrived);

  if (surface === 'dock') {
    measure();
    readForm();
    window.addEventListener('resize', scheduleMeasure);
    window.addEventListener('scroll', scheduleMeasure, { passive: true });
    document.addEventListener('click', scheduleMeasure);
    document.addEventListener('focusout', onFormChange);
    document.addEventListener('change', onFormChange);

    // The reward for the schedule just saved: the card rises and its segment draws. Once.
    if (g.arrived && ! arrived) {
      arrival.value = true;
      arrived = true;
    }
  }

  if (g.celebrate && (surface === 'panel' || surface === 'strip')) {
    goLive();
  } else if (g.saved) {
    // A later event landing: its tile tears off. Which one is new is the difference from what
    // this tab last saw; a tab that has seen nothing takes the most recently created.
    const known = seen ? seen.events || [] : null;
    const fresh = known ? g.events.filter((event) => ! known.includes(event.id)).map((event) => event.id) : [];

    freshEvents.value = fresh.length || ! newest.value ? fresh : [newest.value.id];
  } else if (seen) {
    freshSteps.value = doneKeys().filter((key) => ! (seen.steps || []).includes(key));
  }

  if (finished.value && surface !== 'dock') {
    finish();
  }

  document.addEventListener('click', onPageClick, true);
  document.addEventListener('keydown', onKey);
  window.addEventListener('hashchange', onHash);
  settleHost();
  writeSeen();

  const banner = document.querySelector('[data-cookie-consent]');

  if (banner) {
    liftAboveBanner();
    bannerWatch = new MutationObserver(liftAboveBanner);
    bannerWatch.observe(banner, { attributes: true, attributeFilter: ['hidden', 'class', 'style'] });
    window.addEventListener('resize', liftAboveBanner);
    // The banner slides in; its resting place is only known once that has finished.
    banner.addEventListener('animationend', liftAboveBanner);
    setTimeout(liftAboveBanner, 600);
  }
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', scheduleMeasure);
  window.removeEventListener('scroll', scheduleMeasure);
  document.removeEventListener('click', scheduleMeasure);
  document.removeEventListener('focusout', onFormChange);
  document.removeEventListener('change', onFormChange);
  document.removeEventListener('click', onPageClick, true);
  document.removeEventListener('keydown', onKey);
  window.removeEventListener('hashchange', onHash);
  window.removeEventListener('resize', liftAboveBanner);
  clearTimeout(undoTimer);
  sug.stop();

  if (bannerWatch) {
    bannerWatch.disconnect();
  }
});
</script>

<style>
.sg-root {
  --sg-arrive: cubic-bezier(.22, 1, .36, 1);
  --sg-complete: cubic-bezier(.34, 1.36, .64, 1);
}

/* The card surface, from the design system's own tokens (as .ap-card and .ap-dropdown are),
   without their position: relative, which would beat the positioning these shapes need. */
.sg-surface {
  background: linear-gradient(135deg, rgb(var(--ap-card-from)) 0%, rgb(var(--ap-card-mid)) 30%, rgb(var(--ap-card-to)) 100%);
  box-shadow: var(--ap-shadow-dropdown);
  color: rgb(var(--ap-ink));
}

.sg-eyebrow {
  display: block;
  font-size: 12px;
  font-weight: 600;
  line-height: 16px;
  color: var(--brand-blue);
}

.sg-chevron {
  width: 16px;
  height: 16px;
  flex: none;
  color: rgb(var(--ap-ink-3));
}

/* ---- The ring ---- */

.sg-ring {
  position: relative;
  display: inline-flex;
  flex: none;
}

.sg-ring svg {
  display: block;
}

.sg-arc-track {
  stroke: rgb(var(--ap-border-strong));
}

.sg-arc-done {
  stroke: var(--brand-blue);
}

.sg-arc-draw {
  animation: sg-arc 600ms var(--sg-arrive) both;
}

.sg-ring-bump {
  animation: sg-bump 300ms var(--sg-complete) both;
}

.sg-ring-centre {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-radius: 9999px;
  font-weight: 700;
  line-height: 1;
  color: rgb(var(--ap-ink));
}

.sg-ring-centre img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.sg-ring-icon {
  width: 60%;
  height: 60%;
  color: rgb(var(--ap-ink-3));
}

.sg-ring-check {
  width: 62%;
  height: 62%;
  color: var(--brand-blue);
}

.sg-ring-check path,
.sg-check-inline path,
.sg-step-fresh .sg-node path {
  stroke-dasharray: 24;
  stroke-dashoffset: 24;
  animation: sg-check 350ms var(--sg-arrive) 100ms forwards;
}

.sg-check-inline {
  width: 16px;
  height: 16px;
  flex: none;
}

/* ---- The pill, the chip, the line ---- */

/* --sg-bar: the height of a bar the PAGE keeps along its bottom edge (the event form's save bar).
   The page sets it; without it the corner would sit on that bar's last button, which on the event
   form is Save. --sg-lift is the cookie banner, measured below. */
.sg-corner {
  position: fixed;
  bottom: calc(1rem + var(--sg-lift, 0px) + var(--sg-bar, 0px));
  transition: bottom 320ms var(--sg-arrive);
  inset-inline-end: 1rem;
  z-index: 45;
  display: none;
}

@media (min-width: 1280px) {
  .sg-corner {
    display: block;
  }
}

.sg-pill {
  position: relative;
  display: flex;
  justify-content: flex-end;
  pointer-events: none;
}

.sg-pill-toggle {
  display: flex;
  align-items: center;
  max-width: 24rem;
  border-radius: 9999px;
  pointer-events: none;
  transition: transform 200ms ease;
}

.sg-pill:hover .sg-pill-toggle {
  transform: translateY(-2px);
}

.sg-pill-toggle:active {
  transform: scale(.97);
}

.sg-pill-toggle:focus-visible {
  outline: 2px solid var(--brand-blue);
  outline-offset: 3px;
}

/* The label is a capsule that runs under the ring, so the two read as one shape, and it grows
   toward the page while the ring stays pinned to the corner. */
.sg-pill-label {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-width: 0;
  height: 48px;
  margin-inline-end: -48px;
  padding-inline: 18px 58px;
  border-radius: 9999px;
  text-align: start;
  pointer-events: auto;
  clip-path: inset(0 round 24px);
  transition: clip-path 420ms var(--sg-arrive), opacity 200ms ease;
}

.sg-pill-next {
  display: block;
  overflow: hidden;
  font-size: 14px;
  font-weight: 600;
  line-height: 18px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sg-pill-ring {
  position: relative;
  display: flex;
  flex: none;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 9999px;
  pointer-events: auto;
}

/* Ring alone, on forms and once the guide has gone quiet: the label unrolls on hover and focus. */
.sg-pill-folded .sg-pill-label {
  opacity: 0;
  pointer-events: none;
  clip-path: inset(0 0 0 100% round 24px);
}

[dir="rtl"] .sg-pill-folded .sg-pill-label {
  clip-path: inset(0 100% 0 0 round 24px);
}

.sg-pill-folded:hover .sg-pill-label,
.sg-pill-folded:focus-within .sg-pill-label {
  opacity: 1;
  clip-path: inset(0 round 24px);
  transition-delay: 150ms;
}

.sg-pill-x {
  position: absolute;
  top: -8px;
  inset-inline-start: -8px;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 9999px;
  background: rgb(var(--ap-surface));
  box-shadow: var(--ap-shadow-dropdown);
  color: rgb(var(--ap-ink-3));
  opacity: 0;
  pointer-events: auto;
  transition: opacity 200ms ease 300ms, color 200ms ease;
}

/* A 44px target around a 24px glyph. */
.sg-pill-x::after {
  position: absolute;
  inset: -10px;
  content: '';
}

.sg-pill-x svg {
  width: 12px;
  height: 12px;
}

.sg-pill-x:hover {
  color: rgb(var(--ap-ink));
}

.sg-pill:hover .sg-pill-x,
.sg-pill:focus-within .sg-pill-x {
  opacity: 1;
}

.sg-pill-folded .sg-pill-x {
  inset-inline-start: auto;
  inset-inline-end: 36px;
}

@media (hover: none) {
  .sg-pill-x {
    opacity: 1;
  }
}

.sg-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  max-width: 100%;
  height: 40px;
  margin-bottom: 1rem;
  padding-inline: 12px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  color: rgb(var(--ap-ink));
}

@media (min-width: 1280px) {
  .sg-chip {
    display: none;
  }
}

.sg-line {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 28px;
  margin-bottom: 1rem;
  font-size: 14px;
  font-weight: 600;
  color: var(--brand-blue);
}

.sg-line .sg-chevron {
  color: var(--brand-blue);
}

@media (min-width: 1408px) {
  .sg-line {
    display: none;
  }
}

.sg-chip:focus-visible,
.sg-line:focus-visible,
.sg-quiet:focus-visible,
.sg-card-head:focus-visible,
.sg-step-head:focus-visible,
.sg-icon-button:focus-visible,
.sg-copy:focus-visible,
.sg-hide:focus-visible,
.sg-undo-button:focus-visible {
  outline: 2px solid var(--brand-blue);
  outline-offset: 2px;
  border-radius: 8px;
}

/* ---- The docked card beside the first-event form ---- */

.sg-dock {
  position: fixed;
  z-index: 30;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: calc(100dvh - 7rem);
  padding: 14px;
  overflow-y: auto;
  border-radius: 16px;
}

.sg-dock-enter {
  animation: sg-rise-16 280ms var(--sg-arrive) both;
}

.sg-dock-head {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sg-icon-button {
  display: flex;
  flex: none;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  transition: background-color 200ms ease;
}

.sg-icon-button:hover {
  background: rgb(var(--ap-surface-hover));
}

.sg-dock-promise {
  font-size: 13px;
  line-height: 18px;
  color: rgb(var(--ap-ink-2));
}

.sg-dock-link {
  font-size: 13px;
  font-weight: 600;
  color: var(--brand-blue);
}

.sg-dock-link:hover {
  text-decoration: underline;
}

/* ---- The card ---- */

.sg-scrim {
  position: fixed;
  inset: 0;
  z-index: 54;
  display: none;
  background: rgba(0, 0, 0, .4);
}

.sg-card {
  position: fixed;
  bottom: calc(1rem + var(--sg-lift, 0px) + var(--sg-bar, 0px));
  inset-inline-end: 1rem;
  z-index: 46;
  display: flex;
  flex-direction: column;
  width: 360px;
  max-width: calc(100vw - 2rem);
  max-height: calc(100dvh - 5.5rem - var(--sg-lift, 0px) - var(--sg-bar, 0px));
  overflow: hidden;
  border-radius: 16px;
}

.sg-card-head:focus-visible {
  outline-offset: -3px;
  border-radius: 14px;
}

.sg-card-head {
  display: flex;
  flex: none;
  align-items: center;
  gap: 10px;
  width: 100%;
  min-height: 64px;
  padding: 12px 12px 12px 16px;
}

[dir="rtl"] .sg-card-head {
  padding: 12px 16px 12px 12px;
}

.sg-head-name {
  display: block;
  overflow: hidden;
  font-size: 15px;
  font-weight: 600;
  line-height: 20px;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: rgb(var(--ap-ink));
}

.sg-card-scroll {
  flex: 1;
  min-height: 0;
  padding: 0 16px 8px;
  overflow-y: auto;
}


.sg-card-foot {
  flex: none;
  padding: 8px 16px 12px;
  border-top: 1px solid var(--ap-hairline);
}

.sg-hide {
  font-size: 13px;
  color: rgb(var(--ap-ink-3));
  transition: color 200ms ease;
}

.sg-hide:hover {
  color: rgb(var(--ap-ink));
  text-decoration: underline;
}

/* The card unfolds out of the pill's own shape, in its corner, and its ring travels up the
   edge from where the pill's was. */
.sg-card-enter-active {
  transition: clip-path 280ms var(--sg-arrive), opacity 160ms ease;
}

.sg-card-leave-active {
  transition: clip-path 200ms ease, opacity 200ms ease;
}

.sg-card-enter-from,
.sg-card-leave-to {
  opacity: 0;
  clip-path: inset(calc(100% - 48px) 0 0 calc(100% - 48px) round 24px);
}

[dir="rtl"] .sg-card-enter-from,
[dir="rtl"] .sg-card-leave-to {
  clip-path: inset(calc(100% - 48px) calc(100% - 48px) 0 0 round 24px);
}

.sg-card-enter-to,
.sg-card-leave-from {
  opacity: 1;
  clip-path: inset(0 round 16px);
}

.sg-card-enter-active .sg-card-ring {
  animation: sg-ring-travel 280ms var(--sg-arrive) both;
}

.sg-card-enter-active .sg-strip,
.sg-card-enter-active .sg-step,
.sg-card-enter-active .sg-finish {
  animation: sg-rise 420ms var(--sg-arrive) both;
  animation-delay: calc(140ms + var(--sg-i, 0) * 45ms);
}

.sg-fade-enter-active,
.sg-fade-leave-active {
  transition: opacity 200ms ease;
}

.sg-fade-enter-from,
.sg-fade-leave-to {
  opacity: 0;
}

/* A phone: a bottom sheet, behind a scrim. */
@media (max-width: 639px) {
  .sg-scrim {
    display: block;
  }

  .sg-card {
    inset-inline: 0;
    bottom: 0;
    z-index: 55;
    width: auto;
    max-width: none;
    max-height: 80dvh;
    padding-bottom: env(safe-area-inset-bottom);
    border-radius: 16px 16px 0 0;
  }

  .sg-card-enter-active,
  .sg-card-leave-active {
    transition: transform 280ms var(--sg-arrive), opacity 160ms ease;
  }

  .sg-card-enter-from,
  .sg-card-leave-to,
  [dir="rtl"] .sg-card-enter-from,
  [dir="rtl"] .sg-card-leave-to {
    opacity: 1;
    clip-path: none;
    transform: translateY(100%);
  }

  .sg-card-enter-to,
  .sg-card-leave-from {
    clip-path: none;
  }

  .sg-card-enter-active .sg-card-ring {
    animation: none;
  }
}

/* ---- The steps ---- */

.sg-steps {
  margin: 12px 0 0;
  padding: 0;
  list-style: none;
}

.sg-step {
  position: relative;
}

.sg-step-head {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  min-height: 44px;
  text-align: start;
}

.sg-node {
  position: relative;
  z-index: 1;
  display: flex;
  flex: none;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border: 2px solid rgb(var(--ap-border-strong));
  border-radius: 9999px;
  background: rgb(var(--ap-surface));
  color: #fff;
}

.sg-node svg {
  width: 14px;
  height: 14px;
}

.sg-step-done .sg-node {
  border-color: var(--brand-button-bg);
  background: var(--brand-button-bg);
}

.sg-step-current .sg-node {
  border-color: var(--brand-blue);
  box-shadow: 0 0 0 3px var(--brand-blue-a20);
}

.sg-step-current .sg-node::after {
  width: 8px;
  height: 8px;
  border-radius: 9999px;
  background: var(--brand-blue);
  content: '';
}

.sg-step-skipped .sg-node {
  border-style: dashed;
}

.sg-step-fresh .sg-node {
  animation: sg-pop 300ms var(--sg-complete) both;
}

/* The spine: a positioned line between two nodes, never a border down the side of a row. */
.sg-spine {
  position: absolute;
  top: 34px;
  bottom: -10px;
  inset-inline-start: 11px;
  width: 2px;
  overflow: hidden;
  border-radius: 2px;
  background: rgb(var(--ap-border));
}

.sg-spine-fill {
  display: block;
  width: 100%;
  height: 100%;
  background: var(--brand-button-bg);
  transform: scaleY(0);
  transform-origin: top;
  transition: transform 400ms var(--sg-arrive) 250ms;
}

.sg-step-done .sg-spine-fill {
  transform: scaleY(1);
}

.sg-step-title {
  flex: 1;
  min-width: 0;
  font-size: 14px;
  font-weight: 500;
  line-height: 20px;
  color: rgb(var(--ap-ink-2));
  transition: color 200ms ease;
}

.sg-step-current .sg-step-title {
  font-weight: 600;
  color: rgb(var(--ap-ink));
}

.sg-step-done .sg-step-title,
.sg-step-skipped .sg-step-title {
  color: rgb(var(--ap-ink-3));
}

.sg-step-note {
  flex: none;
  max-width: 45%;
  overflow: hidden;
  font-size: 12px;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: rgb(var(--ap-ink-3));
}

/* Opens on grid-template-rows, so nothing is measured in script. */
.sg-step-body {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 280ms var(--sg-arrive);
}

.sg-step-open > .sg-step-body {
  grid-template-rows: 1fr;
}

.sg-step-inner {
  min-height: 0;
  padding-inline-start: 36px;
  overflow: hidden;
}

.sg-step-inner > * + * {
  margin-top: 10px;
}

.sg-step-inner::after {
  display: block;
  height: 12px;
  content: '';
}

.sg-step-text {
  font-size: 13px;
  line-height: 19px;
  color: rgb(var(--ap-ink-2));
}

.sg-step-hint {
  font-size: 12px;
  line-height: 17px;
  color: rgb(var(--ap-ink-3));
}

.sg-here {
  display: inline-flex;
  align-items: center;
  height: 24px;
  padding-inline: 10px;
  border-radius: 9999px;
  background: var(--brand-blue-a10);
  font-size: 12px;
  font-weight: 600;
  color: var(--brand-blue);
}

.sg-address-row {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 40px;
  padding-inline: 12px 4px;
  border: 1px solid rgb(var(--ap-border));
  border-radius: 10px;
  background: rgb(var(--ap-surface));
  box-shadow: var(--ap-inset-input);
}

.sg-address-text {
  position: relative;
  flex: 1;
  min-width: 0;
  overflow: hidden;
  font-size: 13px;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: rgb(var(--ap-ink));
}

.sg-copy {
  display: inline-flex;
  flex: none;
  align-items: center;
  gap: 6px;
  height: 32px;
  padding-inline: 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--brand-blue);
  transition: background-color 200ms ease;
}

.sg-copy:hover {
  background: var(--brand-blue-a10);
}

.sg-copy svg {
  width: 16px;
  height: 16px;
}

/* The address lifts off the field as it is copied. */
.sg-ghost {
  animation: sg-ghost 400ms var(--sg-arrive);
}

/* At the text edge, under the sentence they belong to, the forward one last. Pushed to the far
   end of the column they made every open step a zig-zag: sentence at the start, button at the
   end, links back at the start. */
.sg-buttons {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  gap: 8px;
}

.sg-buttons-stacked {
  flex-direction: column;
}

.sg-action-form {
  display: contents;
}

.sg-links {
  display: flex;
  flex-wrap: wrap;
  gap: 4px 14px;
}

.sg-finish {
  margin-top: 14px;
}

.sg-finish > * + * {
  margin-top: 10px;
}

.sg-finish-title {
  display: block;
  font-size: 18px;
  font-weight: 600;
  line-height: 24px;
  color: rgb(var(--ap-ink));
}

.sg-head-static .sg-step-text {
  display: block;
}

/* One sheen, once, across the forward button when the schedule goes live. */
.sg-shine {
  position: relative;
  overflow: hidden;
}

.sg-shine::after {
  position: absolute;
  inset: 0;
  background: linear-gradient(100deg, transparent 30%, rgba(255, 255, 255, .45) 50%, transparent 70%);
  content: '';
  transform: translateX(-101%);
  animation: sg-shine 1100ms cubic-bezier(.4, 0, .2, 1) 1 both;
}

[dir="rtl"] .sg-shine::after {
  animation-direction: reverse;
}

/* ---- Your page ---- */

.sg-strip {
  padding: 12px;
  border: 1px solid rgb(var(--ap-border));
  border-radius: 12px;
  background: rgb(var(--ap-surface-sunken));
}

/* Photo, then the name; the address on the same column under the name. In the narrow docked
   card the address takes the full width instead, so it is not cut to a few letters. */
.sg-strip-head {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  grid-template-areas: "photo who tag" "photo address address";
  align-items: center;
  column-gap: 10px;
}

.sg-strip-stack .sg-strip-head {
  grid-template-areas: "photo who tag" "address address address";
  row-gap: 6px;
}

.sg-avatar {
  grid-area: photo;
}

.sg-strip-who {
  grid-area: who;
  min-width: 0;
}

.sg-strip-head > .sg-tag {
  grid-area: tag;
  align-self: start;
}

.sg-avatar {
  display: flex;
  flex: none;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  overflow: hidden;
  border-radius: 9999px;
  background: var(--brand-blue-a20);
  font-size: 14px;
  font-weight: 700;
  color: var(--brand-blue);
}

.sg-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.sg-strip-label {
  display: block;
  font-size: 10px;
  font-weight: 700;
  line-height: 12px;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: rgb(var(--ap-ink-3));
}

.sg-strip-name {
  display: block;
  overflow: hidden;
  font-size: 13px;
  font-weight: 600;
  line-height: 18px;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: rgb(var(--ap-ink));
}

.sg-strip-address {
  grid-area: address;
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 0;
  font-size: 12px;
  line-height: 16px;
  color: rgb(var(--ap-ink-3));
}

.sg-strip-url {
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Always left to right, in either direction: it is an address. */
.sg-wipe {
  animation: sg-wipe 480ms var(--sg-arrive) both;
}

.sg-live-dot {
  flex: none;
  width: 8px;
  height: 8px;
  border-radius: 9999px;
  background: #22c55e;
  box-shadow: 0 0 6px rgba(34, 197, 94, .45);
}

.sg-pop {
  animation: sg-pop 300ms var(--sg-complete) both;
}

.sg-spring {
  animation: sg-pop 300ms var(--sg-complete) both;
}

.sg-slots {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  margin: 10px 0 0;
  padding: 0;
  list-style: none;
}

.sg-strip-stack .sg-slots {
  grid-template-columns: minmax(0, 1fr);
  gap: 6px;
}

.sg-slot {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
  min-height: 68px;
  padding: 8px;
  border: 1px solid rgb(var(--ap-border));
  border-radius: 10px;
  background: rgb(var(--ap-surface));
}

.sg-strip-stack .sg-slot {
  flex-direction: row;
  align-items: center;
  gap: 8px;
  min-height: 40px;
}

.sg-strip-stack .sg-slot:first-child {
  min-height: 56px;
}

/* One per line, or three across: either way a name runs to two lines rather than being cut at
   eight letters (or at one, in the dashboard's narrow column on a tablet). */
.sg-strip-row .sg-slot-name,
.sg-strip-stack .sg-slot-name {
  display: -webkit-box;
  white-space: normal;
  overflow-wrap: anywhere;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.sg-strip-stack.sg-strip {
  padding: 10px;
}

/* In the page there is room for the tile and the name side by side - when the strip itself is
   wide enough, which is a matter of the column it sits in, not of the window: on a tablet the
   dashboard's two columns leave each slot about 80px, and the name was cut to one letter. */
.sg-strip {
  container-type: inline-size;
}

@container (min-width: 480px) {
  .sg-strip-roomy .sg-slot {
    flex-direction: row;
    align-items: center;
    gap: 10px;
    min-height: 60px;
    padding: 10px 12px;
  }

  .sg-strip-roomy .sg-slot-name {
    font-size: 13px;
  }
}

.sg-slot-empty,
.sg-slot-typing {
  border-style: dashed;
  border-color: rgb(var(--ap-border-strong));
  background: transparent;
}

.sg-slot-empty {
  padding: 0;
}

.sg-slot-empty > .sg-slot-empty {
  display: flex;
  flex: 1;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-height: inherit;
  padding: 8px;
  border: 0;
  border-radius: 10px;
  color: rgb(var(--ap-ink-4));
  transition: background-color 200ms ease, color 200ms ease;
}

.sg-slot-empty svg {
  width: 16px;
  height: 16px;
  flex: none;
}

a.sg-slot-empty:hover {
  background: var(--brand-blue-a10);
  color: var(--brand-blue);
}

/* The next one to fill: the gap that is theirs to close. */
.sg-slot-next {
  border-color: var(--brand-blue);
  background: var(--brand-blue-a10);
}

.sg-slot-next > .sg-slot-empty,
.sg-slot-next .sg-slot-name {
  color: var(--brand-blue);
}

.sg-slot-draft {
  opacity: .6;
  border-style: dashed;
}

.sg-slot-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.sg-slot-name {
  display: block;
  overflow: hidden;
  font-size: 12px;
  font-weight: 600;
  line-height: 16px;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: rgb(var(--ap-ink));
}

/* The guest page's date tile: month over day. */
.sg-tile {
  display: flex;
  flex: none;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 34px;
  overflow: hidden;
  border: 1px solid rgb(var(--ap-border));
  border-radius: 7px;
  background: rgb(var(--ap-surface));
  color: rgb(var(--ap-ink-3));
  transform-origin: top center;
}

.sg-tile svg {
  width: 16px;
  height: 16px;
}

.sg-tile-month {
  width: 100%;
  background: var(--brand-button-bg);
  font-size: 8px;
  font-weight: 700;
  line-height: 12px;
  text-align: center;
  text-transform: uppercase;
  color: #fff;
}

.sg-tile-day {
  font-size: 13px;
  font-weight: 700;
  line-height: 20px;
  color: rgb(var(--ap-ink));
}

/* An event landing on their page: the tile tears off its top edge and the name rises. */
.sg-tear .sg-tile {
  animation: sg-tear 600ms var(--sg-complete) both;
}

.sg-tear .sg-slot-text {
  animation: sg-rise 700ms var(--sg-arrive) 120ms both;
}

.sg-slot.sg-tear:nth-child(2) .sg-tile,
.sg-slot.sg-tear:nth-child(2) .sg-slot-text {
  animation-delay: 90ms;
}

.sg-slot.sg-tear:nth-child(3) .sg-tile,
.sg-slot.sg-tear:nth-child(3) .sg-slot-text {
  animation-delay: 180ms;
}

.sg-tag {
  align-self: flex-start;
  flex: none;
  padding: 0 6px;
  border-radius: 9999px;
  background: rgb(var(--ap-surface-active));
  font-size: 10px;
  font-weight: 600;
  line-height: 16px;
  color: rgb(var(--ap-ink-2));
}

.sg-tag-blue {
  background: var(--brand-blue-a10);
  color: var(--brand-blue);
}

.sg-strip-more {
  display: inline-block;
  margin-top: 8px;
  font-size: 12px;
  color: rgb(var(--ap-ink-3));
}

.sg-strip-more:hover {
  text-decoration: underline;
}

/* ---- The dashboard section ---- */

.sg-section {
  display: grid;
  grid-template-areas: "head" "page" "steps";
  gap: 16px;
  padding: 20px;
  border-radius: 16px;
}

.sg-section-head {
  grid-area: head;
}

.sg-section-steps {
  grid-area: steps;
}

.sg-section-page {
  grid-area: page;
}

@media (min-width: 1024px) {
  .sg-section {
    grid-template-areas: "head page" "steps page";
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    grid-template-rows: auto 1fr;
    gap: 4px 28px;
    padding: 24px;
  }
}

@media (min-width: 1400px) {
  .sg-section {
    grid-template-columns: minmax(0, 420px) minmax(0, 1fr);
  }
}

.sg-section-quiet {
  display: block;
  min-height: 0;
  padding: 0;
}

/* The quiet card as one list: the guide's line, its X beside it (the line is itself a button,
   so the X cannot be inside it), then the rows. */
.sg-quiet-row {
  display: flex;
  align-items: center;
}

.sg-quiet-row .sg-quiet {
  flex: 1;
  min-width: 0;
  padding-inline-end: 4px;
}

.sg-quiet-rows {
  padding-bottom: 12px;
}

/* The band under both columns: the person's other schedules. It runs to the card's edges, so
   its rows line up with the list an account without a guide gets. */
.sg-section-banded {
  grid-template-areas: "head" "page" "steps" "also";
}

.sg-section-banded.sg-section-done {
  grid-template-areas: "head" "steps" "page" "also";
}

.sg-section-also {
  grid-area: also;
  margin: 4px -20px -12px;
  padding-top: 12px;
  border-top: 1px solid var(--ap-hairline);
}

@media (min-width: 1024px) {
  .sg-section-banded,
  .sg-section-banded.sg-section-done {
    grid-template-areas: "head page" "steps page" "also also";
    grid-template-rows: auto 1fr auto;
  }

  .sg-section-banded.sg-section-done {
    grid-template-rows: auto auto auto;
  }

  .sg-section-also {
    margin: 16px -24px -16px;
  }
}

/* The guide's own schedule: one line under the steps, at the steps' text edge; in the finished
   state between the buttons and the links, where the finish block's own spacing places it. */
.sg-own-steps {
  margin-top: 8px;
  margin-inline-start: 36px;
}

.sg-own .sg-row-box {
  margin-inline: -10px;
}

/* What follows the guide's Undo line when the guide has left and rows remain. */
.sg-undo-row + .sg-after {
  margin-top: 16px;
}

/* Finished: as tall as what it says, everything on one text edge. The ring and the finish line
   are the header; the buttons and links sit under the line, at the edge the line starts on.
   On a phone the actions come before the page, full width, the forward one last. */
.sg-section-done {
  grid-template-areas: "head" "steps" "page";
  align-content: center;
  min-height: 0;
}

.sg-section-done .sg-finish {
  margin-top: 0;
}

.sg-section-done .sg-buttons {
  flex-direction: column;
}

@media (min-width: 640px) {
  .sg-section-done .sg-buttons {
    flex-direction: row;
  }

  /* The 44px ring and its 12px gap. */
  .sg-finish-inset {
    padding-inline-start: 56px;
  }
}

@media (min-width: 1024px) {
  .sg-section-done {
    grid-template-areas: "head page" "steps page";
    grid-template-rows: auto auto;
    row-gap: 14px;
  }
}

.sg-quiet {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  min-height: 64px;
  padding: 12px 20px;
}

.sg-quiet-title {
  display: block;
  overflow: hidden;
  font-size: 14px;
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: rgb(var(--ap-ink));
}

.sg-head-static {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sg-section-steps {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.sg-section-foot {
  display: flex;
  flex-wrap: wrap;
  gap: 4px 16px;
  margin-top: auto;
  padding-top: 12px;
}

.sg-section-page {
  min-width: 0;
  align-self: center;
}

.sg-section-page .sg-strip {
  padding: 16px;
}

.sg-section-page .sg-slot {
  min-height: 84px;
}

.sg-enter {
  animation: sg-rise 500ms var(--sg-arrive) both;
}

/* ---- Hidden: the way back ---- */

.sg-undo {
  position: fixed;
  bottom: calc(1rem + var(--sg-lift, 0px) + var(--sg-bar, 0px));
  inset-inline: 1rem;
  z-index: 46;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 48px;
  padding: 8px 8px 8px 16px;
  border-radius: 14px;
  font-size: 13px;
}

[dir="rtl"] .sg-undo {
  padding: 8px 16px 8px 8px;
}

/* The same words on the dashboard, in the page where the guide was. */
.sg-undo-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 48px;
  padding-block: 8px;
  padding-inline: 20px 8px;
  border-radius: 16px;
  font-size: 13px;
}

@media (min-width: 640px) {
  .sg-undo {
    inset-inline: auto 1rem;
    max-width: 26rem;
    border-radius: 9999px;
  }
}

.sg-undo-button {
  flex: none;
  height: 32px;
  padding-inline: 12px;
  border-radius: 9999px;
  font-weight: 600;
  color: var(--brand-blue);
  transition: background-color 200ms ease;
}

.sg-undo-button:hover {
  background: var(--brand-blue-a10);
}

/* The sidebar line, when the guide is hidden: once, to show where it went. */
.sg-nav-flash {
  animation: sg-nav-flash 1200ms ease 1;
}

/* The support chat opens in the same corner; the guide steps aside while it is open. */
body[data-support-open] .sg-corner,
body[data-support-open] .sg-card,
body[data-support-open] .sg-dock {
  display: none;
}

/* ---- Keyframes ---- */

@keyframes sg-arc {
  from {
    stroke-dasharray: .01 var(--sg-round);
  }
}

@keyframes sg-check {
  to {
    stroke-dashoffset: 0;
  }
}

@keyframes sg-bump {
  50% {
    transform: scale(1.06);
  }
}

@keyframes sg-pop {
  from {
    opacity: 0;
    transform: scale(.6);
  }
}

@keyframes sg-rise {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
}

@keyframes sg-rise-16 {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
}

@keyframes sg-ring-travel {
  from {
    transform: translateY(var(--sg-travel, 320px));
  }
}

@keyframes sg-tear {
  from {
    opacity: 0;
    transform: perspective(500px) rotateX(-90deg);
  }

  35% {
    opacity: 1;
  }

  to {
    opacity: 1;
    transform: perspective(500px) rotateX(0);
  }
}

@keyframes sg-wipe {
  from {
    clip-path: inset(0 100% 0 0);
  }

  to {
    clip-path: inset(0);
  }
}

@keyframes sg-ghost {
  from {
    opacity: .9;
    transform: translateY(0);
  }

  to {
    opacity: 0;
    transform: translateY(-10px);
  }
}

@keyframes sg-shine {
  to {
    transform: translateX(101%);
  }
}

@keyframes sg-nav-flash {
  50% {
    color: #fff;
  }
}

/* Reduced motion: the words and colours still change; nothing travels, draws or bursts. */
@media (prefers-reduced-motion: reduce) {
  .sg-root,
  .sg-root *,
  .sg-root *::after,
  .sg-nav-flash {
    animation: none !important;
    transition-duration: 150ms !important;
    transition-property: opacity, color, background-color, border-color !important;
  }

  .sg-ring-check path,
  .sg-check-inline path,
  .sg-step-fresh .sg-node path {
    stroke-dashoffset: 0;
  }

  .sg-pill:hover .sg-pill-toggle,
  .sg-pill-toggle:active {
    transform: none;
  }

  .sg-shine::after {
    display: none;
  }
}

@media print {
  .sg-root {
    display: none !important;
  }
}
</style>
