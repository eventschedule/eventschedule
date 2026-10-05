<template>
  <div
    ref="root"
    class="sg-sug"
    :class="{ 'sg-sug-plain': plain }"
    @mouseenter="emit('hold')"
    @mouseleave="emit('release')"
    @focusin="emit('hold')"
    @focusout="emit('release')"
  >
    <h2 v-if="label && title" class="sg-sug-title">{{ label }}</h2>
    <p v-else-if="label" class="sg-sug-label">{{ label }}</p>

    <TransitionGroup tag="ul" name="sg-row" class="sg-rows">
      <li v-for="row in shown" :key="row.key" class="sg-row" :data-row="row.key">
        <div class="sg-row-box" :class="{ 'sg-row-stub': row.state === 'dismissed' }">
          <template v-if="row.state === 'dismissed'">
            <span class="sg-row-said" role="status">{{ t.suggestion_dismissed }}</span>
            <button type="button" class="sg-undo-button" @click="emit('undo', row)">{{ t.undo }}</button>
          </template>
          <template v-else>
            <a :href="row.url" class="sg-row-link">
              <span v-if="! plain" class="sg-avatar sg-row-face" aria-hidden="true">
                <img v-if="row.photo" :src="row.photo" alt="" />
                <span v-else>{{ row.initial }}</span>
              </span>
              <span class="sg-row-words">
                <span class="sg-row-title">{{ row.title }}</span>
                <span v-if="! plain" class="sg-row-name"><bdi>{{ row.name }}</bdi></span>
              </span>
            </a>
            <button type="button" class="sg-row-x" :aria-label="dismissLabel(row)" :title="dismissLabel(row)" @click="onDismiss(row, $event)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
          </template>
        </div>
      </li>
    </TransitionGroup>

    <button v-if="more > 0" type="button" class="sg-hide sg-sug-more" aria-expanded="false" @click="expanded = true">{{ moreLabel }}</button>

    <div v-if="footAll || footOff" class="sg-sug-foot">
      <button v-if="footAll" type="button" class="sg-hide" @click="emit('all')">{{ st.dismiss_all }}</button>
      <button v-if="footOff" type="button" class="sg-hide sg-sug-off" @click="emit('off')">{{ t.turn_off }}</button>
    </div>
  </div>
</template>

<script setup>
/*
 * The dashboard's suggestions as rows, in the setup guide's card: with a guide they are the
 * band under it ("Your other schedules"), without one they are the whole card ("Next steps").
 * The same row either way, and it is the guide's own quiet line: a 40px circle with the
 * schedule's photo or initial, the ask, the schedule's name, an X. The whole row is the link.
 *
 * A plain circle, not the guide's ring: the ring means progress, and an empty one on a schedule
 * that has run for two years would read as "nothing done".
 *
 * `plain` is the one line about the guide's OWN schedule, drawn inside the guide: no second
 * circle and no second name under a header that already shows both.
 *
 * An X turns the row, at its own height, into "Suggestion dismissed" with Undo where the X
 * was. The state and the requests live in setup-guide-suggestions.js; this only draws.
 */
import { computed, nextTick, ref } from 'vue';

const props = defineProps({
  rows: { type: Array, required: true },
  t: { type: Object, required: true },
  // The four strings the product already had for this panel (SetupGuide::suggestions()).
  st: { type: Object, required: true },
  // How many before "Show N more": eight in the list, as the old panel had; three in the band.
  limit: { type: Number, default: 8 },
  label: { type: String, default: '' },
  // The label as the card's heading rather than a small one over a band.
  title: { type: Boolean, default: false },
  plain: { type: Boolean, default: false },
  footAll: { type: Boolean, default: false },
  footOff: { type: Boolean, default: false },
});

const emit = defineEmits(['dismiss', 'undo', 'all', 'off', 'hold', 'release']);

const root = ref(null);
const expanded = ref(false);

const shown = computed(() => (expanded.value ? props.rows : props.rows.slice(0, props.limit)));
const more = computed(() => props.rows.length - shown.value.length);

// A function, never a string, as the replacement: a schedule's name is text, and "$&" in a
// string handed to replace() is a pattern.
const fill = (text, key, value) => String(text || '').replace(':' + key, () => String(value));

const moreLabel = computed(() => fill(props.st.show_more, 'count', more.value));
const dismissLabel = (row) => fill(props.st.dismiss_for, 'schedule', row.name);

// Reached by keyboard (a click with no pointer has detail 0), focus follows to the stub's Undo,
// or it would drop to the top of the document with the X that was just removed.
const onDismiss = (row, event) => {
  const byKeyboard = event && event.detail === 0;

  emit('dismiss', row);

  if (byKeyboard) {
    nextTick(() => {
      const stub = root.value && root.value.querySelector('[data-row="' + row.key + '"] .sg-undo-button');

      if (stub) {
        stub.focus({ preventScroll: true });
      }
    });
  }
};
</script>

<style>
/* ---- The dashboard's suggestions, in the guide's card ---- */

.sg-list {
  padding: 8px 0 12px;
  border-radius: 16px;
}

.sg-sug-title {
  padding: 12px 20px 6px;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
  color: rgb(var(--ap-ink));
}

.sg-sug-label {
  padding: 4px 20px 6px;
  font-size: 10px;
  font-weight: 700;
  line-height: 12px;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: rgb(var(--ap-ink-3));
}

/* A row opens and closes on grid-template-rows, like a step's body: nothing is measured. */
.sg-row {
  display: grid;
  grid-template-rows: 1fr;
}

.sg-row-box {
  display: flex;
  align-items: center;
  min-height: 0;
  height: 64px;
  overflow: hidden;
  transition: background-color 200ms ease;
}

/* The whole row lights, so the X at its far end plainly belongs to it. */
.sg-row-box:hover {
  background: var(--ap-tint-1);
}

.sg-row-link {
  display: flex;
  flex: 1;
  align-items: center;
  gap: 12px;
  min-width: 0;
  height: 100%;
  padding-inline: 20px 4px;
  text-decoration: none;
  color: inherit;
}

.sg-row-link:focus-visible,
.sg-row-x:focus-visible,
.sg-notice-link:focus-visible {
  outline: 2px solid var(--brand-blue);
  outline-offset: -2px;
  border-radius: 8px;
}

/* With .sg-avatar in the selector: that rule (32px, in SetupGuide.vue) is emitted after this
   file's, and at equal weight it would win. */
.sg-avatar.sg-row-face {
  width: 40px;
  height: 40px;
  font-size: 16px;
}

.sg-row-words {
  flex: 1;
  min-width: 0;
}

.sg-row-title,
.sg-row-name {
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sg-row-title {
  font-size: 14px;
  font-weight: 600;
  line-height: 20px;
  color: rgb(var(--ap-ink));
}

/* w-fit, so an RTL name is not pushed against the far edge of an LTR row. */
.sg-row-name {
  width: fit-content;
  max-width: 100%;
  font-size: 12px;
  line-height: 16px;
  color: rgb(var(--ap-ink-3));
}

.sg-row-x {
  display: flex;
  flex: none;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  margin-inline-end: 12px;
  border-radius: 9999px;
  color: rgb(var(--ap-ink-3));
  transition: background-color 200ms ease, color 200ms ease;
}

.sg-row-x svg {
  width: 16px;
  height: 16px;
}

.sg-row-x:hover {
  background: var(--ap-tint-2);
  color: rgb(var(--ap-ink));
}

/* The stub: the same 64px, so no row under it moves. Undo sits where the X was. */
.sg-row-stub {
  justify-content: space-between;
  gap: 12px;
  padding-inline: 20px 8px;
}

.sg-row-said {
  font-size: 13px;
  color: rgb(var(--ap-ink-3));
}

.sg-row-leave-active {
  transition: grid-template-rows 240ms var(--sg-arrive), opacity 160ms ease;
}

.sg-row-leave-to {
  grid-template-rows: 0fr;
  opacity: 0;
}

.sg-row-leave-active .sg-row-box {
  height: auto;
  min-height: 0;
}

.sg-sug-more {
  display: block;
  margin: 8px 20px 0;
}

.sg-sug-foot {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 4px 16px;
  padding: 10px 20px 0;
}

.sg-sug-off {
  margin-inline-start: auto;
}

/* The guide's own schedule: one line inside the guide, at the guide's text edge. */
.sg-sug-plain .sg-row-box {
  height: 40px;
  border-radius: 10px;
}

.sg-sug-plain .sg-row-link {
  padding-inline: 10px 4px;
}

.sg-sug-plain .sg-row-x {
  margin-inline-end: 4px;
}

.sg-sug-plain .sg-row-stub {
  padding-inline: 10px 4px;
}

.sg-sug-plain .sg-row-title {
  font-weight: 500;
  color: var(--brand-blue);
}

/* ---- The Undo line ---- */

.sg-notice-words {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 2px 10px;
  min-width: 0;
}

.sg-notice-link {
  color: rgb(var(--ap-ink-3));
  text-decoration: underline;
  transition: color 200ms ease;
}

.sg-notice-link:hover {
  color: rgb(var(--ap-ink));
}

.sg-undo-flat {
  padding-inline: 20px 8px;
  border-radius: 0;
}

/* A thumb, not a pointer: 44px targets, a title that may take two lines, and the switch on a
   line of its own under the sentence it follows. */
@media (hover: none) {
  .sg-row-x {
    width: 44px;
    height: 44px;
    margin-inline-end: 4px;
  }

  .sg-sug-foot .sg-hide,
  .sg-sug-more {
    min-height: 44px;
  }

  .sg-notice-words {
    flex-direction: column;
    align-items: flex-start;
  }

  .sg-notice-link {
    display: inline-flex;
    align-items: center;
    min-height: 44px;
  }
}

@media (max-width: 639px) {
  .sg-row-title {
    display: -webkit-box;
    white-space: normal;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
  }
}

@media (prefers-reduced-motion: reduce) {
  .sg-row-leave-active {
    transition: opacity 150ms ease;
  }
}
</style>
