<template>
  <form v-if="action.post" method="POST" :action="action.post" class="sg-action-form">
    <input type="hidden" name="_token" :value="csrf" />
    <button type="submit" :class="classes">{{ action.label }}</button>
  </form>
  <a
    v-else-if="action.href"
    :href="action.href"
    :target="action.target || null"
    :rel="action.target ? 'noopener noreferrer' : null"
    :class="classes"
    @click="emit('follow', action)"
  >{{ action.label }}</a>
  <button v-else type="button" :class="classes" @click="emit('act', action)">
    <svg v-if="action.done" class="sg-check-inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M5 12.5l4.5 4.5L19 7.5" pathLength="24" />
    </svg>
    {{ action.label }}
  </button>
</template>

<script setup>
/*
 * One thing the setup guide offers to do: a link, a form that posts (publishing a draft), or
 * a button the guide handles itself (copying the address, turning a step down).
 *
 * The three looks are the product's own: `primary` is <x-brand-link>, `secondary` is
 * <x-secondary-link>, `link` is <x-link>. A compiled component cannot use the Blade ones, so
 * their classes are repeated here; change one, change the other.
 */
import { computed } from 'vue';

const props = defineProps({
  action: { type: Object, required: true },
  kind: { type: String, default: 'link' },
  csrf: { type: String, default: '' },
  block: { type: Boolean, default: false },
  shine: { type: Boolean, default: false },
});

const emit = defineEmits(['act', 'follow']);

const looks = {
  primary: 'sg-forward inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-b from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-base text-white no-underline shadow-sm transition-all duration-200 hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800',
  secondary: 'ap-secondary-btn inline-flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 no-underline transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800',
  link: 'inline-flex items-center gap-1 text-sm font-medium text-blue-600 dark:text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded',
  quiet: 'inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded',
};

const classes = computed(() => [
  looks[props.kind] || looks.link,
  props.block ? 'w-full' : '',
  props.shine ? 'sg-shine' : '',
]);
</script>
