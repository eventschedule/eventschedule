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
      </component>

      <div v-if="hasBody(row)" class="sg-step-body">
        <div class="sg-step-inner">
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

const isOpen = (row) => hasBody(row) && (chosen.value === null ? props.current : chosen.value) === row.key;

const toggle = (row) => {
  if (hasBody(row)) {
    chosen.value = isOpen(row) ? '' : row.key;
  }
};
</script>
