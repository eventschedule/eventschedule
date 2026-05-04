<template>
  <div class="es-a11y-root pointer-events-none fixed bottom-6 z-[45] flex flex-col gap-2" :class="rootAlignClass">
    <div
      v-show="panelOpen"
      id="es-a11y-panel"
      ref="panelRef"
      role="dialog"
      :aria-label="labelHeading"
      aria-modal="true"
      tabindex="-1"
      class="pointer-events-auto es-a11y-ignore w-[min(20rem,calc(100vw-2rem))] rounded-2xl border border-gray-200 bg-white p-4 text-gray-900 shadow-lg dark:border-white/10 dark:bg-[#252526] dark:text-gray-100"
      @keydown="onPanelKeydown"
    >
      <div class="mb-3 flex items-center justify-between gap-2">
        <h2 class="text-base font-semibold">{{ labelHeading }}</h2>
        <button
          type="button"
          class="es-a11y-ignore rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] dark:hover:bg-white/10 dark:hover:text-white"
          :aria-label="labelClose"
          @click="closePanel"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="space-y-3 text-sm">
        <div>
          <div class="mb-1 font-medium text-gray-700 dark:text-gray-300">{{ labelFont }}</div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="opt in fontOptions"
              :key="opt.step"
              type="button"
              class="es-a11y-ignore rounded-lg border px-2 py-1 text-xs font-medium transition focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]"
              :class="fontStep === opt.step ? activePillClass : inactivePillClass"
              @click="setFontStep(opt.step)"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <label class="flex cursor-pointer items-center gap-2">
          <input v-model="highContrast" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" @change="persist" />
          <span>{{ labelHighContrast }}</span>
        </label>

        <label class="flex cursor-pointer items-center gap-2">
          <input v-model="underlineLinks" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" @change="persist" />
          <span>{{ labelUnderline }}</span>
        </label>

        <label class="flex cursor-pointer items-center gap-2">
          <input v-model="reduceMotion" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" @change="persist" />
          <span>{{ labelReduceMotion }}</span>
        </label>

        <button
          type="button"
          class="es-a11y-ignore w-full rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
          @click="resetAll"
        >
          {{ labelReset }}
        </button>

        <a
          v-if="declarationUrl"
          :href="declarationUrl"
          class="es-a11y-ignore block text-center text-sm font-medium text-[var(--brand-blue)] hover:underline"
        >
          {{ labelDeclaration }}
        </a>
      </div>
    </div>

    <button
      type="button"
      class="pointer-events-auto es-a11y-ignore flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-900 shadow-md transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-[#252526] dark:text-gray-100 dark:hover:bg-[#2d2d30]"
      :aria-expanded="panelOpen ? 'true' : 'false'"
      aria-haspopup="dialog"
      aria-controls="es-a11y-panel"
      @click="togglePanel"
    >
      <svg class="h-5 w-5 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a4 4 0 014 4v1h1a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2h1V8a4 4 0 118 0z" />
      </svg>
      <span>{{ labelOpen }}</span>
    </button>
  </div>
</template>

<script>
const STORAGE = {
  font: 'es_a11y_font_step',
  contrast: 'es_a11y_high_contrast',
  underline: 'es_a11y_underline',
  motion: 'es_a11y_reduce_motion',
};

function readBool(key) {
  try {
    return localStorage.getItem(key) === '1';
  } catch (e) {
    return false;
  }
}

function readInt(key, fallback) {
  try {
    const v = parseInt(localStorage.getItem(key), 10);
    return Number.isFinite(v) ? v : fallback;
  } catch (e) {
    return fallback;
  }
}

export default {
  name: 'AccessibilityWidget',
  props: {
    i18n: {
      type: Object,
      default: () => ({}),
    },
    declarationUrl: {
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
      panelOpen: false,
      fontStep: 0,
      highContrast: false,
      underlineLinks: false,
      reduceMotion: false,
      lastFocus: null,
    };
  },
  computed: {
    rootAlignClass() {
      return this.rtl ? 'start-6 items-start' : 'end-6 items-end';
    },
    activePillClass() {
      return 'border-[var(--brand-blue)] bg-[var(--brand-blue)] text-white dark:bg-[var(--brand-blue)]';
    },
    inactivePillClass() {
      return 'border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-[#1e1e1e]';
    },
    labelOpen() {
      return this.i18n.toolbarOpen || 'Accessibility';
    },
    labelClose() {
      return this.i18n.toolbarClose || 'Close';
    },
    labelHeading() {
      return this.i18n.toolbarHeading || 'Display';
    },
    labelFont() {
      return this.i18n.toolbarFontSize || 'Text size';
    },
    labelHighContrast() {
      return this.i18n.toolbarHighContrast || 'High contrast';
    },
    labelUnderline() {
      return this.i18n.toolbarUnderlineLinks || 'Underline links';
    },
    labelReduceMotion() {
      return this.i18n.toolbarReduceMotion || 'Reduce motion';
    },
    labelReset() {
      return this.i18n.toolbarReset || 'Reset';
    },
    labelDeclaration() {
      return this.i18n.toolbarDeclaration || 'Statement';
    },
    fontOptions() {
      return [
        { step: 0, label: this.i18n.toolbarFontDefault || 'Default' },
        { step: 1, label: this.i18n.toolbarFontMedium || 'Medium' },
        { step: 2, label: this.i18n.toolbarFontLarge || 'Large' },
      ];
    },
  },
  mounted() {
    this.fontStep = readInt(STORAGE.font, 0);
    this.highContrast = readBool(STORAGE.contrast);
    this.underlineLinks = readBool(STORAGE.underline);
    this.reduceMotion = readBool(STORAGE.motion);
    this.applyHtmlClasses();
  },
  methods: {
    togglePanel() {
      this.panelOpen = !this.panelOpen;
      if (this.panelOpen) {
        this.lastFocus = document.activeElement;
        this.$nextTick(() => {
          const p = this.$refs.panelRef;
          if (p) {
            p.focus();
          }
        });
      } else if (this.lastFocus && typeof this.lastFocus.focus === 'function') {
        this.lastFocus.focus();
      }
    },
    closePanel() {
      this.panelOpen = false;
      if (this.lastFocus && typeof this.lastFocus.focus === 'function') {
        this.lastFocus.focus();
      }
    },
    onPanelKeydown(e) {
      if (e.key === 'Escape') {
        e.preventDefault();
        this.closePanel();
      }
    },
    setFontStep(step) {
      this.fontStep = step;
      this.persist();
    },
    persist() {
      try {
        localStorage.setItem(STORAGE.font, String(this.fontStep));
        localStorage.setItem(STORAGE.contrast, this.highContrast ? '1' : '0');
        localStorage.setItem(STORAGE.underline, this.underlineLinks ? '1' : '0');
        localStorage.setItem(STORAGE.motion, this.reduceMotion ? '1' : '0');
      } catch (err) {}
      this.applyHtmlClasses();
    },
    applyHtmlClasses() {
      const root = document.documentElement;
      root.classList.remove('es-a11y-font-step-1', 'es-a11y-font-step-2');
      if (this.fontStep === 1) {
        root.classList.add('es-a11y-font-step-1');
      }
      if (this.fontStep === 2) {
        root.classList.add('es-a11y-font-step-2');
      }
      root.classList.toggle('es-a11y-high-contrast', this.highContrast);
      root.classList.toggle('es-a11y-underline-links', this.underlineLinks);
      root.classList.toggle('es-a11y-reduce-motion', this.reduceMotion);
    },
    resetAll() {
      this.fontStep = 0;
      this.highContrast = false;
      this.underlineLinks = false;
      this.reduceMotion = false;
      this.persist();
    },
  },
};
</script>
