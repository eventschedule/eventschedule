<template>
  <div
    class="sg-undo-row"
    :class="flat ? 'sg-undo-flat' : 'sg-root sg-surface'"
    role="status"
    @mouseenter="emit('hold')"
    @mouseleave="emit('release')"
    @focusin="onFocus"
    @focusout="emit('release')"
  >
    <span class="sg-notice-words">
      <span>{{ text }}</span>
      <a v-if="link && link.href" :href="link.href" class="sg-notice-link">{{ link.label }}</a>
      <button v-else-if="link" type="button" class="sg-notice-link" @click="emit('link')">{{ link.label }}</button>
    </span>
    <button ref="button" type="button" class="sg-undo-button" @click="emit('undo')">{{ undo }}</button>
  </div>
</template>

<script setup>
/*
 * One line where something of the setup guide's card just was: what happened, and Undo.
 *
 * Undo is the pill at the far end. The other thing a person may want here is the opposite of
 * Undo ("Turn off suggestions"), so it is a quiet link straight after the sentence, away from
 * the pill, and on a line of its own on a phone: the two must never sit side by side.
 */
import { ref } from 'vue';

defineProps({
  text: { type: String, required: true },
  undo: { type: String, required: true },
  // { label, href } for a link, { label } for a button that emits `link`.
  link: { type: Object, default: null },
  // Inside the guide's card rather than standing in for it: no surface of its own.
  flat: { type: Boolean, default: false },
});

const emit = defineEmits(['undo', 'link', 'hold', 'release']);

const button = ref(null);

// True for the instant the parent moves focus here after a pointer's click. That focus is a
// courtesy, not somebody reaching for the button, and must not hold the line open: it would
// cancel the ten seconds the line was just given.
let placing = false;

const onFocus = () => {
  if (! placing) {
    emit('hold');
  }
};

const focus = (courtesy) => {
  if (button.value) {
    placing = !! courtesy;
    button.value.focus({ preventScroll: true });
    placing = false;
  }
};

defineExpose({ focus });
</script>
