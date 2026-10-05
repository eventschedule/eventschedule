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

  <div v-else-if="! off && live.length" class="sg-root sg-list sg-surface">
    <SetupGuideSuggestions
      title
      :label="st.next_steps"
      :rows="live"
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
import { computed, onBeforeUnmount, onMounted, watch } from 'vue';
import SetupGuideNotice from './SetupGuideNotice.vue';
import SetupGuideSuggestions from './SetupGuideSuggestions.vue';
import { useSuggestions } from '../setup-guide-suggestions.js';

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

onMounted(settleHost);
onBeforeUnmount(stop);
</script>
