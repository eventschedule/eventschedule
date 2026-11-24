<template>
  <SetupGuideNotice
    v-if="off && offNotice"
    :text="t.off_notice"
    :undo="t.undo"
    :link="{ label: t.settings, href: urls.settings }"
    @undo="turnOn"
    @hold="holdOff"
    @release="startOff"
  />

  <SetupGuideNotice
    v-else-if="! off && batch"
    :text="t.suggestions_dismissed"
    :undo="t.undo"
    :link="{ label: t.turn_off }"
    @link="turnOff"
    @undo="undoAll"
    @hold="holdBatch"
    @release="startBatch"
  />

  <template v-else-if="! off && live.length">
  <!-- The 2026-11 redesign: the same ask on several schedules ("Connect a payment method to take
       money", one row a schedule) is one amber band that names them, with one way to it and one
       Dismiss for all of them. Whatever else is suggested stays in the card under it. -->
  <section v-if="grouped" class="sg-root sg-group" :aria-label="st.next_steps">
    <div class="sg-group-text">
      <p class="sg-group-kicker">{{ kicker }}</p>
      <p class="sg-group-title">{{ grouped[0].title }}</p>
      <ul class="sg-group-chips">
        <li v-for="row in groupShown" :key="row.key"><a :href="row.url" class="sg-group-chip"><bdi>{{ row.name }}</bdi></a></li>
        <li v-if="grouped.length > groupShown.length"><button type="button" class="sg-group-more" @click="groupAll = true">{{ moreLabel }}</button></li>
      </ul>
    </div>
    <div class="sg-group-actions">
      <a :href="grouped[0].url" class="sg-group-go">{{ goLabel }} <span aria-hidden="true" class="sg-group-arrow">&rarr;</span></a>
      <button type="button" class="sg-group-dismiss" @click="dismissAll(grouped)">{{ t.dismiss }}</button>
    </div>
  </section>

  <div v-if="restRows.length" class="sg-root sg-list sg-surface">
    <SetupGuideSuggestions
      title
      :label="st.next_steps"
      :rows="restRows"
      :t="t"
      :st="st"
      :limit="8"
      :foot-all="open.length > 1"
      :foot-off="offersSwitch"
      @dismiss="dismiss"
      @undo="undo"
      @all="dismissAll(live)"
      @off="turnOff"
      @hold="holdStubs"
      @release="startStubs"
    />
  </div>
  </template>
</template>

<script setup>
/*
 * The dashboard's card for an account with suggestions and no setup guide to show: the list.
 * It wears the guide's card and uses the guide's own row (SetupGuideSuggestions), so an
 * established account and a new one see one family of thing where there used to be the new
 * guide for one and the old "Next steps" panel for the other.
 *
 * "Turn off suggestions" is at the foot from the start only for somebody who has dismissed a
 * suggestion before: that is the person for whom they came back. For everybody else it appears
 * once the card has been emptied, in the Undo line or beside the last stubs.
 */
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import SetupGuideNotice from './SetupGuideNotice.vue';
import SetupGuideSuggestions from './SetupGuideSuggestions.vue';
import { useSuggestions } from '../setup-guide-suggestions.js';
import { choose } from '../plural';

const props = defineProps({
  guide: { type: Object, required: true },
});

const t = props.guide.t;
const token = document.querySelector('meta[name="csrf-token"]');

const {
  live, open, urls, strings: st, dismissedBefore,
  dismiss, undo, holdStubs, startStubs,
  batch, dismissAll, undoAll, holdBatch, startBatch,
  off, offNotice, turnOff, turnOn, holdOff, startOff, stop,
} = useSuggestions(props.guide.suggestions, token ? token.content : '');

const offersSwitch = computed(() => dismissedBefore || open.value.length === 0);

// The largest set of live rows asking the same thing, when it is more than one.
const grouped = computed(() => {
  const byAsk = {};

  live.value.filter((row) => row.state !== 'dismissed').forEach((row) => {
    (byAsk[row.title] = byAsk[row.title] || []).push(row);
  });

  const largest = Object.values(byAsk).sort((a, b) => b.length - a.length)[0];

  return largest && largest.length > 1 ? largest : null;
});
const restRows = computed(() => (grouped.value ? live.value.filter((row) => ! grouped.value.includes(row)) : live.value));
const groupAll = ref(false);
// The 2026-11 design's words: "Next step · 14 schedules", "Connect →" where the ask is a payment
// method (the verb of the thing asked; anything else opens), and "+6 more" as a link.
const kicker = computed(() => (grouped.value ? choose(t.group_kicker, grouped.value.length, props.guide.locale) : ''));
const goLabel = computed(() => (grouped.value && grouped.value[0].type === 'next_step_payments' ? t.group_connect : t.step_open));
const moreLabel = computed(() => (grouped.value ? choose(t.group_more, grouped.value.length - groupShown.value.length, props.guide.locale) : ''));
// Eight names, and three on a phone (the design review of 2026-11), where eight chips took most
// of the first screen. Followed as the window changes, so a turned phone gets the wide count.
const phoneQuery = typeof window !== 'undefined' && window.matchMedia ? window.matchMedia('(max-width: 639px)') : null;
const phone = ref(!! (phoneQuery && phoneQuery.matches));
const onPhoneChange = (e) => { phone.value = e.matches; };
const groupShown = computed(() => (grouped.value ? (groupAll.value ? grouped.value : grouped.value.slice(0, phone.value ? 3 : 8)) : []));

// The host reserved the card's height so nothing under it moved when this took over from the
// still version. From here the card is as tall as what it holds, and when it holds nothing the
// host goes too (`hidden`, so the dashboard's space-y rhythm skips it).
const settleHost = () => {
  const host = document.getElementById('setup-guide');

  if (host) {
    host.style.minHeight = '0';
    host.hidden = off.value ? ! offNotice.value : ! batch.value && live.value.length === 0;
  }
};

watch([off, offNotice, batch, () => live.value.length], settleHost, { flush: 'post' });

onMounted(() => {
  settleHost();
  if (phoneQuery) phoneQuery.addEventListener('change', onPhoneChange);
});
onBeforeUnmount(() => {
  stop();
  if (phoneQuery) phoneQuery.removeEventListener('change', onPhoneChange);
});
</script>

<style scoped>
.sg-group {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px 20px;
  margin-bottom: 16px;
  padding: 16px 20px;
  border-radius: 16px;
  background: rgb(254 243 199 / 0.8);
  border: 1px solid rgb(253 230 138 / 0.7);
}
:global(.dark .sg-group) {
  background: rgb(120 53 15 / 0.25);
  border-color: rgb(146 64 14 / 0.6);
}
.sg-group-text {
  min-width: 0;
  flex: 1 1 24rem;
}
.sg-group-kicker {
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: rgb(146 64 14);
}
:global(.dark .sg-group-kicker) {
  color: rgb(252 211 77);
}
.sg-group-title {
  margin-top: 2px;
  font-size: 16px;
  font-weight: 600;
  color: rgb(var(--ap-ink));
}
.sg-group-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
}
.sg-group-chip {
  display: inline-flex;
  padding: 4px 10px;
  border-radius: 999px;
  background: rgb(var(--ap-surface) / 0.7);
  font-size: 13px;
  font-weight: 500;
  color: rgb(var(--ap-ink));
}
/* "+6 more" is a link in the band's own amber, not another chip. */
.sg-group-more {
  display: inline-flex;
  padding: 4px 6px;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
  color: rgb(146 64 14);
}
:global(.dark .sg-group-more) {
  color: rgb(252 211 77);
}
.sg-group-chip:hover,
.sg-group-more:hover {
  text-decoration: underline;
}
.sg-group-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: none;
}
.sg-group-go {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-height: 44px;
  padding: 0 16px;
  border-radius: 10px;
  background: rgb(20 22 26);
  font-size: 14px;
  font-weight: 600;
  color: #fff;
}
:global([dir="rtl"] .sg-group-arrow) {
  display: inline-block;
  transform: scaleX(-1);
}
.sg-group-go:hover {
  background: rgb(45 48 56);
}
.sg-group-dismiss {
  min-height: 44px;
  padding: 0 10px;
  font-size: 14px;
  font-weight: 500;
  color: rgb(146 64 14);
}
.sg-group-dismiss:hover {
  text-decoration: underline;
}
/* Dark mode, after the rules it changes (a scoped rule carries the same weight, so order decides):
   the dismiss word in the light amber, and the dark button turned light so it stands out. */
:global(.dark .sg-group .sg-group-dismiss) {
  color: rgb(252 211 77);
}
:global(.dark .sg-group .sg-group-go) {
  background: rgb(243 244 246);
  color: rgb(20 22 26);
}
:global(.dark .sg-group .sg-group-go:hover) {
  background: #fff;
}
</style>
