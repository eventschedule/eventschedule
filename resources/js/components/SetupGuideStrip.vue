<template>
  <div class="sg-strip" :class="['sg-strip-' + layout, { 'sg-strip-roomy': roomy }]">
    <div class="sg-strip-head">
      <span class="sg-avatar" aria-hidden="true">
        <img v-if="guide.photo" :src="guide.photo" alt="" />
        <span v-else>{{ guide.initial }}</span>
      </span>
      <span class="sg-strip-who">
        <span class="sg-strip-label">{{ t.your_page }}</span>
        <span class="sg-strip-name"><bdi>{{ guide.name }}</bdi></span>
      </span>
      <span v-if="guide.embedded" class="sg-tag sg-tag-blue" :class="{ 'sg-spring': embeddedNow }">{{ t.on_website }}</span>
      <span class="sg-strip-address" :class="{ 'sg-wipe': announce }">
        <span v-if="guide.live" class="sg-live-dot" :class="{ 'sg-pop': announce }" aria-hidden="true"></span>
        <span v-if="guide.live" class="sr-only">{{ t.live }}</span>
        <bdi dir="ltr" class="sg-strip-url">{{ guide.address }}</bdi>
      </span>
    </div>

    <ul class="sg-slots">
      <li
        v-for="slot in slots"
        :key="slot.key"
        class="sg-slot"
        :class="['sg-slot-' + slot.kind, { 'sg-tear': slot.fresh && ! calm, 'sg-slot-next': slot.next }]"
        :style="slot.span > 1 ? { gridColumn: 'span ' + slot.span } : null"
      >
        <template v-if="slot.filled">
          <span class="sg-tile" aria-hidden="true">
            <template v-if="slot.day">
              <span class="sg-tile-month">{{ slot.month }}</span>
              <span class="sg-tile-day">{{ slot.day }}</span>
            </template>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
          </span>
          <span class="sg-slot-text">
            <span class="sg-slot-name"><bdi>{{ slot.name }}</bdi></span>
            <span v-if="slot.tag" class="sg-tag">{{ slot.tag }}</span>
          </span>
        </template>
        <a v-else-if="guide.urls.add && ! slot.here" :href="guide.urls.add" class="sg-slot-empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
          <span v-if="slot.label" class="sg-slot-name">{{ slot.label }}</span>
          <span v-else class="sr-only">{{ t.add_event }}</span>
        </a>
        <span v-else class="sg-slot-empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
          <span v-if="slot.label" class="sg-slot-name">{{ slot.label }}</span>
        </span>
      </li>
    </ul>

    <a v-if="guide.more > 0" :href="guide.urls.schedule" class="sg-strip-more">{{ moreLabel }}</a>
  </div>
</template>

<script setup>
/*
 * "Your page": a small picture of the organizer's own schedule page, and the thing in the
 * setup guide that pulls people forward. Their photo or initial, the name, the address, and
 * three slots - filled ones are an event's date tile and name, empty ones are dashed, the
 * next one to fill in brand blue. A gap that is visibly theirs to close.
 *
 * The tile is the guest page's own date tile, and it lands the way it does there: tearing off
 * from its top edge (`fresh`). That motion is kept for exactly one thing, an event arriving on
 * their page.
 *
 * `mirror` is the first-event form filling slot one in as it is typed (the docked card). The
 * dates in `guide.events` were rendered on the server in the SCHEDULE's timezone; nothing here
 * converts a date.
 */
import { computed } from 'vue';

const props = defineProps({
  guide: { type: Object, required: true },
  t: { type: Object, required: true },
  // 'row': three across. 'stack': one per line, for the narrow docked card.
  layout: { type: String, default: 'row' },
  mirror: { type: Object, default: null },
  // Ids of tiles that have just landed.
  fresh: { type: Array, default: () => [] },
  // The going-live moment: the live dot pops and the address wipes in.
  announce: { type: Boolean, default: false },
  embeddedNow: { type: Boolean, default: false },
  // Reduced motion: say "Just added" instead of moving.
  calm: { type: Boolean, default: false },
  // On the page that IS the step, an empty slot is not a link back to the same page.
  here: { type: Boolean, default: false },
  // In the page rather than in the 360px card: slots are wide enough for tile and name side by side.
  roomy: { type: Boolean, default: false },
});

const SLOTS = 3;

const slots = computed(() => {
  const list = [];
  const events = props.guide.events || [];

  if (! props.guide.live) {
    if (props.mirror && (props.mirror.name || props.mirror.day)) {
      list.push({
        key: 'mirror',
        kind: props.mirror.day ? 'event' : 'typing',
        filled: true,
        name: props.mirror.name || props.t.first_slot,
        month: props.mirror.month,
        day: props.mirror.day,
        fresh: !! props.mirror.day,
        next: ! props.mirror.day,
      });
    } else if (props.guide.draft) {
      list.push({ key: 'draft', kind: 'draft', filled: true, name: props.guide.draft.name, tag: props.t.draft });
    }
  }

  events.forEach((event) => {
    const fresh = props.fresh.includes(event.id);

    list.push({
      key: event.id,
      kind: 'event',
      filled: true,
      name: event.name,
      month: event.month,
      day: event.day,
      repeats: event.repeats,
      tag: event.repeats ? props.t.repeats : (fresh && props.calm ? props.t.just_added : ''),
      fresh,
    });
  });

  const shown = list.slice(0, SLOTS);
  const empty = SLOTS - shown.length;

  // An event that repeats fills the page on its own: it takes the empty slots too, rather than
  // sitting beside two dashes that ask for more.
  const repeating = shown.find((slot) => slot.repeats);

  if (repeating && empty > 0) {
    repeating.span = 1 + (props.layout === 'row' ? empty : 0);

    return shown;
  }

  // One blue slot at a time: the one being typed into, or else the first empty one.
  const taken = shown.some((slot) => slot.next);

  for (let index = 0; index < empty; index++) {
    const first = index === 0;

    shown.push({
      key: 'empty-' + index,
      kind: 'empty',
      filled: false,
      next: first && ! taken,
      here: props.here,
      label: first && shown.length === 0 ? props.t.first_slot : '',
    });
  }

  return shown;
});

const moreLabel = computed(() => String(props.t.more || '').replace(':count', props.guide.more));
</script>
