<template>
  <section
    class="es-stay22 overflow-hidden bg-white/95 p-5 backdrop-blur-sm sm:rounded-2xl dark:bg-gray-900/95"
    :class="{ rtl: rtl }"
    :dir="rtl ? 'rtl' : null"
    role="region"
    :aria-labelledby="headingId"
  >
    <h2 :id="headingId" class="mb-3 text-base font-semibold text-gray-900 dark:text-gray-100">
      {{ heading }}
    </h2>

    <!--
      v-if rather than binding a null src: the iframe element must not exist at all before
      consent, so there is provably zero request to Stay22. An empty-src iframe still
      creates a document and is easy to reintroduce in a later refactor.

      No sandbox attribute, deliberately. Without allow-same-origin a sandbox blocks
      Stay22's own cookies and storage, which is the entire mechanism the affiliate
      attribution depends on; with allow-same-origin plus allow-scripts the sandbox is
      decorative. Do not "harden" this into a silent no-op.

      No allow="geolocation" either: SecurityHeaders sets Permissions-Policy geolocation=()
      for the document and that propagates into iframes, so Stay22's "near me" control is
      denied. Do not widen the page's Permissions-Policy for an affiliate widget.
    -->
    <div v-if="loaded" class="es-stay22-frame overflow-hidden rounded-xl">
      <iframe :src="themedUrl" :title="frameTitle" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

    <div
      v-else
      class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-[#2d2d30] dark:bg-[#252526] dark:text-gray-300"
    >
      <!-- A GPC visitor gets no button, so the normal body text would promise a choice that is
           not on offer. -->
      <p class="mb-3">{{ globalPrivacyControl ? gpcBody : consentBody }}</p>
      <button
        v-if="!globalPrivacyControl"
        type="button"
        class="inline-flex items-center justify-center rounded-lg bg-[var(--brand-button-bg)] px-4 py-3 text-base font-semibold text-white transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800"
        @click="optIn"
      >
        {{ consentButton }}
      </button>
    </div>

    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
      <a
        :href="themedUrl"
        target="_blank"
        rel="noopener nofollow sponsored"
        class="underline hover:no-underline"
      >{{ linkLabel }}</a>
    </p>

    <!-- Always visible, in both states: this is an affiliate placement, not an amenity. -->
    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">{{ disclosure }}</p>
  </section>
</template>

<script>
import { CONSENT_EVENT, readConsent } from '../cookie-consent';

export default {
  name: 'Stay22Map',
  props: {
    url: {
      type: String,
      required: true,
    },
    heading: {
      type: String,
      default: '',
    },
    frameTitle: {
      type: String,
      default: '',
    },
    consentBody: {
      type: String,
      default: '',
    },
    gpcBody: {
      type: String,
      default: '',
    },
    consentButton: {
      type: String,
      default: '',
    },
    linkLabel: {
      type: String,
      default: '',
    },
    disclosure: {
      type: String,
      default: '',
    },
    rtl: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      // null (never answered) | 'granted' | 'denied'
      consent: readConsent(),
      // Opt-in for THIS page view only, and deliberately not persisted. localStorage would
      // create a second durable consent record that the privacy page's withdrawal button
      // does not clear, so a visitor who withdrew consent would still get the iframe.
      optedIn: false,
      globalPrivacyControl: navigator.globalPrivacyControl === true,
    };
  },
  computed: {
    loaded() {
      if (this.globalPrivacyControl) {
        return false;
      }

      return this.consent === 'granted' || this.optedIn;
    },
    headingId() {
      return 'es-stay22-heading';
    },
    /**
     * The embed URL with `mapstyle` matched to the theme actually resolved in the browser.
     *
     * The server can only guess from the schedule's own background colour, and it guesses
     * nothing at all for gradient themes. The real theme comes from a `dark` class that
     * layouts/app.blade.php applies client-side, which can follow the visitor's OS preference -
     * so without this a light-background schedule viewed in dark mode gets a light map inside a
     * dark page.
     *
     * URLSearchParams.set() replaces an existing key in place and appends a new one at the end,
     * so `aid` stays first either way, which the vendor requires.
     */
    themedUrl() {
      try {
        const parsed = new URL(this.url);
        parsed.searchParams.set(
          'mapstyle',
          document.documentElement.classList.contains('dark') ? 'dark' : 'light'
        );

        return parsed.toString();
      } catch (e) {
        return this.url;
      }
    },
  },
  mounted() {
    document.addEventListener(CONSENT_EVENT, this.onConsentChange);
  },
  unmounted() {
    document.removeEventListener(CONSENT_EVENT, this.onConsentChange);
  },
  methods: {
    optIn() {
      // Sets local opt-in only, never cookie_consent. Loading one affiliate map must not
      // silently grant analytics and advertising consent site-wide.
      this.optedIn = true;
    },
    onConsentChange(e) {
      this.consent = (e.detail && e.detail.value) || null;

      // Withdrawal unloads the map, so the iframe leaves the DOM (GDPR Article 7(3)).
      if (this.consent !== 'granted') {
        this.optedIn = false;
      }
    },
  },
};
</script>
