<template>
  <!--
    v-if rather than a null src: before consent the iframe does not exist at all, so there is
    provably no request to YouTube or Google Maps. The placeholder keeps the frame's own size, so
    nothing on the page moves when it loads.
  -->
  <iframe
    v-if="loaded"
    :class="frameClass"
    :style="frameStyle"
    :src="src"
    :title="title"
    frameborder="0"
    :allow="allow || null"
    :referrerpolicy="referrerpolicy"
    allowfullscreen
    loading="lazy"
  ></iframe>
  <div
    v-else
    :class="frameClass"
    :style="frameStyle"
    class="relative flex flex-col items-center justify-center gap-2 overflow-hidden bg-gray-100 p-4 text-center dark:bg-gray-800"
  >
    <img v-if="poster" :src="poster" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
    <div v-if="poster" class="absolute inset-0 bg-black/45"></div>
    <button
      type="button"
      class="relative inline-flex items-center gap-2 rounded-lg bg-black/75 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:bg-black/90 focus:outline-none focus:ring-2 focus:ring-white"
      :aria-label="title ? button + ': ' + title : button"
      @click="optedIn = true"
    >
      <svg v-if="kind === 'video'" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z" /></svg>
      <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
      {{ button }}
    </button>
    <p class="relative max-w-xs text-xs leading-snug" :class="poster ? 'text-white' : 'text-gray-600 dark:text-gray-300'">{{ body }}</p>
  </div>
</template>

<script>
import { CONSENT_EVENT, hasConsent } from '../cookie-consent';

/**
 * A YouTube video or Google Maps embed that loads only once the visitor wants it: with the
 * marketing cookie category granted (here or later on the page), or for this one item after a
 * click on the placeholder, which is itself the request. Both services see the visitor's IP
 * address and can set cookies, so nothing is requested from them before that. The click is not
 * remembered: withdrawing consent unloads every embed on the page, as it does the Stay22 map.
 *
 * Mounted on each [data-consent-embed] host by consent-embed-boot.js, with every value as a
 * prop, so no user-controlled text is ever compiled as a Vue template.
 */
export default {
  name: 'ConsentEmbed',
  props: {
    src: { type: String, required: true },
    title: { type: String, default: '' },
    kind: { type: String, default: 'video' },
    frameClass: { type: String, default: 'w-full' },
    frameStyle: { type: String, default: '' },
    allow: { type: String, default: '' },
    referrerpolicy: { type: String, default: 'strict-origin-when-cross-origin' },
    poster: { type: String, default: '' },
    button: { type: String, default: '' },
    body: { type: String, default: '' },
  },
  data() {
    return {
      consent: hasConsent('marketing'),
      optedIn: false,
    };
  },
  computed: {
    loaded() {
      return this.consent || this.optedIn;
    },
  },
  mounted() {
    document.addEventListener(CONSENT_EVENT, this.onConsentChange);
  },
  unmounted() {
    document.removeEventListener(CONSENT_EVENT, this.onConsentChange);
  },
  methods: {
    onConsentChange() {
      this.consent = hasConsent('marketing');

      // Withdrawal unloads the embed, so the iframe leaves the DOM (GDPR Article 7(3)).
      if (!this.consent) {
        this.optedIn = false;
      }
    },
  },
};
</script>
