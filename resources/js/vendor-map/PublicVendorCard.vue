<!--
  One vendor's card on the visitors' map, opened by a stall on the map or a row of the list.

  A name only (somebody typed it on the stall, or the organizer added a vendor for the day) has
  no page: it gets no link and no picture, only the name, the number and what was said about it.
-->
<template>
  <article class="vm-card" :class="{ 'is-absent': absent }" :aria-labelledby="headId">
    <button type="button" class="vm-card-close gk-btn gk-btn-quiet gk-btn-icon" :aria-label="t.close" @click="$emit('close')">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
    </button>
    <img v-if="photo" class="vm-card-photo" :src="photo" alt="" loading="lazy" decoding="async" width="400" height="200">
    <div class="vm-card-body">
      <p class="vm-card-spot">
        <i class="vm-dot" :style="{ '--area': areaColor || null }" aria-hidden="true"></i>
        <span>{{ spotText }}</span>
        <template v-if="areaName"><span aria-hidden="true">&middot;</span><bdi>{{ areaName }}</bdi></template>
      </p>
      <h2 :id="headId" ref="head" class="vm-card-name" tabindex="-1"><bdi>{{ vendor.name }}</bdi></h2>
      <p v-if="absent" class="vm-card-absent">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        <span>{{ absentText }}</span>
      </p>
      <ul v-if="categories.length" class="vm-card-cats">
        <li v-for="c in categories" :key="c.slug" class="gk-chip">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path v-for="(d, i) in c.icon" :key="i" :d="d" /></svg>
          {{ c.label }}
        </li>
      </ul>
      <p v-if="vendor.sells" class="vm-card-sells"><bdi>{{ vendor.sells }}</bdi></p>
      <a v-if="vendor.url" :href="vendor.url" class="gk-btn gk-btn-primary vm-card-link">
        {{ t.visit }}
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" :style="rtl ? 'transform: scaleX(-1)' : null"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
      </a>
    </div>
  </article>
</template>

<script setup>
import { computed, ref } from 'vue';
import { categoryIcon } from './MapCanvasIcons';

const props = defineProps({
    row: { type: Object, required: true },
    categoryLabels: { type: Object, default: () => ({}) },
    areaName: { type: String, default: '' },
    areaColor: { type: String, default: '' },
    t: { type: Object, required: true },
    rtl: { type: Boolean, default: false },
});

defineEmits(['close']);

const head = ref(null);
const headId = computed(() => `vm-card-${props.row.id}`);
const vendor = computed(() => props.row.vendor || {});
const absent = computed(() => props.row.state === 'absent');
// A picture only of somebody with a page of their own: a name only has none.
const photo = computed(() => (vendor.value.url && vendor.value.photo) ? vendor.value.photo : null);
const spotText = computed(() => (props.t.spot || ':number').replace(':number', props.row.label));
const absentText = computed(() => (vendor.value.next ? props.t.absentNext.replace(':date', vendor.value.next) : props.t.absent));
const categories = computed(() => (vendor.value.categories || [])
    .filter((slug) => props.categoryLabels[slug])
    .map((slug) => ({ slug, label: props.categoryLabels[slug], icon: categoryIcon(slug) })));

defineExpose({ focus: () => head.value?.focus({ preventScroll: true }) });
</script>
