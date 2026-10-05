<template>
  <span class="sg-ring" :class="{ 'sg-ring-bump': bump }" :style="{ width: size + 'px', height: size + 'px' }">
    <svg :width="size" :height="size" :viewBox="'0 0 ' + size + ' ' + size" aria-hidden="true" focusable="false">
      <g :transform="'rotate(-90 ' + centre + ' ' + centre + ')'">
        <circle
          v-for="arc in arcs"
          :key="'track-' + arc.key"
          class="sg-arc-track"
          :cx="centre"
          :cy="centre"
          :r="radius"
          fill="none"
          :stroke-width="stroke"
          stroke-linecap="round"
          :stroke-dasharray="arc.dash + ' ' + (round - arc.dash)"
          :stroke-dashoffset="arc.offset"
        />
        <circle
          v-for="arc in filled"
          :key="'done-' + arc.key"
          class="sg-arc-done"
          :class="{ 'sg-arc-draw': arc.drawing }"
          :style="{ '--sg-round': round }"
          :cx="centre"
          :cy="centre"
          :r="radius"
          fill="none"
          :stroke-width="stroke"
          stroke-linecap="round"
          :stroke-dasharray="arc.dash + ' ' + (round - arc.dash)"
          :stroke-dashoffset="arc.offset"
        />
      </g>
    </svg>
    <span class="sg-ring-centre" :style="{ inset: (stroke + 2) + 'px', fontSize: Math.round(size * 0.36) + 'px' }">
      <svg v-if="check" class="sg-ring-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M5 12.5l4.5 4.5L19 7.5" pathLength="24" />
      </svg>
      <img v-else-if="photo" :src="photo" alt="" />
      <span v-else-if="initial">{{ initial }}</span>
      <svg v-else class="sg-ring-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
      </svg>
    </span>
  </span>
</template>

<script setup>
/*
 * The setup guide's ring: one segment per step, clockwise from the top, around the schedule's
 * photo or initial. It IS the count - nothing in the guide prints a fraction beside it.
 *
 * A segment is full or empty and never part-filled. The only motion is a segment drawing in
 * when its step completes (`drawing`), and the gaps closing into one circle at the finish
 * (`closed`). Never mirrored in RTL: a clock runs the same way in Hebrew.
 *
 * partials/setup-guide-ring.blade.php draws the same geometry on the server, for the still
 * version the page shows before this arrives. Keep the two formulas in step.
 */
import { computed } from 'vue';

const props = defineProps({
  size: { type: Number, default: 36 },
  total: { type: Number, default: 3 },
  done: { type: Number, default: 0 },
  // How many leading segments are drawn as one unbroken arc: the first stretch, once live.
  fused: { type: Number, default: 0 },
  // Index of the segment to draw in, or -1.
  drawing: { type: Number, default: -1 },
  closed: { type: Boolean, default: false },
  check: { type: Boolean, default: false },
  bump: { type: Boolean, default: false },
  photo: { type: String, default: '' },
  initial: { type: String, default: '' },
});

const stroke = computed(() => Math.max(2, Math.round(props.size / 9)));
const centre = computed(() => props.size / 2);
const radius = computed(() => (props.size - stroke.value) / 2);
const round = computed(() => 2 * Math.PI * radius.value);

const arcs = computed(() => {
  if (props.closed) {
    return [{ key: 'whole', dash: round.value, offset: 0, done: true, drawing: false }];
  }

  const per = round.value / props.total;
  // Round caps add half the stroke to each end of a dash, hence the subtraction.
  const gap = props.total > 1 ? stroke.value * 0.9 + 2 : 0;
  const list = [];

  const add = (start, span) => {
    list.push({
      key: start + '-' + span,
      dash: Math.max(0.5, per * span - gap - stroke.value),
      offset: -(start * per + gap / 2 + stroke.value / 2),
      done: start + span <= props.done,
      drawing: props.drawing >= start && props.drawing < start + span,
    });
  };

  let index = 0;

  if (props.fused > 1 && props.fused <= props.total) {
    add(0, props.fused);
    index = props.fused;
  }

  for (; index < props.total; index++) {
    add(index, 1);
  }

  return list;
});

const filled = computed(() => arcs.value.filter((arc) => arc.done));
</script>
