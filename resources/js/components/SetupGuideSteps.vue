<template>
  <ol class="sg-steps">
    <li
      v-for="(row, index) in shown"
      :key="row.key"
      class="sg-step"
      :class="['sg-step-' + row.state, { 'sg-step-open': isOpen(row), 'sg-step-fresh': fresh.includes(row.key) }]"
      :style="{ '--sg-i': index }"
    >
      <span v-if="index < shown.length - 1" class="sg-spine" aria-hidden="true"><span class="sg-spine-fill"></span></span>

      <component
        :is="hasBody(row) ? 'button' : 'div'"
        class="sg-step-head"
        :type="hasBody(row) ? 'button' : null"
        :aria-expanded="hasBody(row) ? (isOpen(row) ? 'true' : 'false') : null"
        @click="toggle(row)"
      >
        <span class="sg-node" aria-hidden="true">
          <svg v-if="row.state === 'done'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12.5l4.5 4.5L19 7.5" pathLength="24" />
          </svg>
        </span>
        <span class="sg-step-title">
          {{ row.title }}
          <span v-if="row.state === 'done'" class="sr-only">{{ t.done }}</span>
        </span>
        <span v-if="row.note" class="sg-step-note"><bdi>{{ row.note }}</bdi></span>
        <!-- What the row is, in a word at its end (the 2026-11 redesign): done, or one to open. -->
        <span v-if="row.state === 'done'" class="sg-step-state" aria-hidden="true">{{ t.done }}</span>
        <span v-else-if="hasBody(row) && ! isOpen(row)" class="sg-step-state is-open" aria-hidden="true">{{ t.step_open }}</span>
      </component>

      <div v-if="hasBody(row)" class="sg-step-body">
        <div class="sg-step-inner">
          <!-- The share step, as the 2026-11 design lays it out: the link is the thing, so its
               box and a filled Copy link button lead; everything else is "Other ways to share",
               a row of outlined chips, with Skip for now quiet at their end. -->
          <template v-if="isShare(row)">
            <p v-if="row.body" class="sg-step-text">{{ row.body }}</p>
            <div class="sg-share-link">
              <bdi dir="ltr" class="sg-share-address" :class="{ 'sg-ghost': copied }">{{ guide.address }}</bdi>
              <button type="button" class="sg-share-copy" @click="emit('copy')">
                <svg v-if="copied" class="sg-check-inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M5 12.5l4.5 4.5L19 7.5" pathLength="24" />
                </svg>
                {{ copied ? t.copied : t.copy_link }}
              </button>
            </div>
            <div v-if="row.primary || (row.links && row.links.length)" class="sg-share-more">
              <p class="sg-share-label">{{ t.other_ways }}</p>
              <div class="sg-share-chips">
                <SetupGuideAction v-if="row.primary" :action="row.primary" kind="chip" :csrf="csrf" @act="emit('act', $event)" />
                <SetupGuideAction v-for="link in (row.links || [])" :key="link.label" :action="link" :kind="link.quiet ? 'quiet' : 'chip'" :csrf="csrf" @act="emit('act', $event)" />
              </div>
            </div>
          </template>

          <template v-else>
            <p v-if="row.body" class="sg-step-text">{{ row.body }}</p>
            <p v-if="row.here" class="sg-here">{{ t.you_are_here }}</p>

            <div v-if="row.address" class="sg-address-row">
              <bdi dir="ltr" class="sg-address-text" :class="{ 'sg-ghost': copied }">{{ guide.address }}</bdi>
              <button type="button" class="sg-copy" @click="emit('copy')">
                <svg v-if="copied" class="sg-check-inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M5 12.5l4.5 4.5L19 7.5" pathLength="24" />
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                </svg>
                {{ copied ? t.copied : t.copy_link }}
              </button>
            </div>

            <p v-if="row.hint" class="sg-step-hint">{{ row.hint }}</p>

            <div v-if="row.primary || row.secondary" class="sg-buttons" :class="{ 'sg-buttons-stacked': row.stacked }">
              <SetupGuideAction v-if="row.secondary" :action="row.secondary" kind="secondary" :csrf="csrf" :block="row.stacked" @act="emit('act', $event)" />
              <SetupGuideAction v-if="row.primary" :action="row.primary" kind="primary" :csrf="csrf" :block="row.stacked" @act="emit('act', $event)" />
            </div>

            <div v-if="row.links && row.links.length" class="sg-links">
              <SetupGuideAction v-for="link in row.links" :key="link.label" :action="link" :kind="link.quiet ? 'quiet' : 'link'" :csrf="csrf" @act="emit('act', $event)" />
            </div>
          </template>
        </div>
      </div>
    </li>
  </ol>
</template>

<script setup>
/*
 * The setup guide's steps, down a vertical spine: the old step band's horizontal line turned on
 * its side. Done steps are one quiet line; the open one has a sentence, one full-size button
 * (forward, last) and its other actions as text links. Any step with something to say can be
 * opened, in any order.
 *
 * The spine is a positioned line between the nodes, never a border down the side of a row.
 * A row's body opens and closes on grid-template-rows, so nothing is measured in script.
 */
import { computed, ref } from 'vue';
import SetupGuideAction from './SetupGuideAction.vue';

const props = defineProps({
  rows: { type: Array, required: true },
  guide: { type: Object, required: true },
  t: { type: Object, required: true },
  csrf: { type: String, default: '' },
  // The key of the step that is open unless the person opens another.
  current: { type: String, default: '' },
  copied: { type: Boolean, default: false },
  // Keys of steps that have just completed.
  fresh: { type: Array, default: () => [] },
  // Show this one step alone (the dashboard section on a phone).
  only: { type: String, default: '' },
});

const shown = computed(() => (props.only ? props.rows.filter((row) => row.key === props.only) : props.rows));

const emit = defineEmits(['act', 'copy']);

// null follows `current`; a key is the person's own choice; '' is "all closed".
const chosen = ref(null);

const hasBody = (row) => !! (row.body || row.here || row.address || row.primary || row.secondary || (row.links && row.links.length));

// The share step gets the design's own layout (the link first, then the other ways as chips).
const isShare = (row) => row.key === 'share' && !! row.address;

const isOpen = (row) => hasBody(row) && (chosen.value === null ? props.current : chosen.value) === row.key;

const toggle = (row) => {
  if (hasBody(row)) {
    chosen.value = isOpen(row) ? '' : row.key;
  }
};
</script>

<style>
/*
 * The 2026-11 design's step markers and weights, laid over the guide's own (SetupGuide.vue) with
 * one more class so they win whatever order the two blocks load in: a pending step is a grey
 * ring with its title in full ink at 500; the open one is an empty blue ring and 600; a done one
 * is filled blue with a tick and its title quieter.
 */
.sg-steps .sg-step-title {
  font-size: 15px;
  font-weight: 500;
  color: rgb(var(--ap-ink));
}
.sg-steps .sg-step-open .sg-step-title {
  font-weight: 600;
}
.sg-steps .sg-step-done .sg-step-title,
.sg-steps .sg-step-skipped .sg-step-title {
  color: rgb(var(--ap-ink-2));
}
.sg-steps .sg-step-current .sg-node {
  box-shadow: none;
}
.sg-steps .sg-step-current .sg-node::after {
  display: none;
}
.sg-steps .sg-step-open .sg-node,
.sg-steps .sg-step-current:not(.sg-step-done) .sg-node {
  border-color: var(--brand-blue);
}

/* The share step. */
.sg-share-link {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  max-width: 520px;
}
.sg-share-address {
  display: flex;
  flex: 1 1 220px;
  align-items: center;
  min-width: 0;
  height: 48px;
  padding-inline: 14px;
  overflow: hidden;
  border: 1px solid rgb(var(--ap-border-strong));
  border-radius: 12px;
  background: rgb(var(--ap-surface));
  font-size: 15px;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: rgb(var(--ap-ink));
}
.sg-share-copy {
  display: inline-flex;
  flex: none;
  align-items: center;
  justify-content: center;
  gap: 6px;
  height: 48px;
  padding-inline: 18px;
  border-radius: 12px;
  background: var(--brand-button-bg);
  font-size: 15px;
  font-weight: 600;
  white-space: nowrap;
  color: #fff;
  transition: background-color 200ms ease;
}
.sg-share-copy:hover {
  background: var(--brand-button-bg-hover);
}
.sg-share-copy:focus-visible {
  outline: 2px solid var(--brand-blue);
  outline-offset: 2px;
}
.sg-share-copy .sg-check-inline {
  width: 16px;
  height: 16px;
}
.sg-step-inner > .sg-share-link {
  margin-top: 16px;
}
.sg-step-inner > .sg-share-more {
  margin-top: 16px;
}
.sg-share-label {
  margin-bottom: 8px;
  font-size: 13px;
  color: rgb(var(--ap-ink-2));
}
.sg-share-chips {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}
.sg-share-chip {
  display: inline-flex;
  align-items: center;
  padding: 8px 14px;
  border: 1px solid rgb(var(--ap-border-strong));
  border-radius: 9999px;
  background: rgb(var(--ap-surface));
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
  color: rgb(var(--ap-ink));
  text-decoration: none;
  transition: border-color 200ms ease, color 200ms ease;
}
.sg-share-chip:hover {
  border-color: var(--brand-blue);
  color: var(--brand-blue);
}
.sg-share-chip:focus-visible {
  outline: 2px solid var(--brand-blue);
  outline-offset: 2px;
}
.sg-share-chips .sg-action-form {
  display: inline-flex;
}
</style>
