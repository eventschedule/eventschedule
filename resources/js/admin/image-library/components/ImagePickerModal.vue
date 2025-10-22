<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60" @click="close"></div>
    <div class="relative z-10 flex h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl">
      <header class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
        <div>
          <h2 class="text-lg font-semibold text-slate-800">Select image</h2>
          <p class="text-sm text-slate-500">Upload new images or pick an existing one from the library.</p>
        </div>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="close">
          <span class="sr-only">Close</span>
          ✕
        </button>
      </header>
      <main class="flex flex-1 flex-col gap-6 overflow-y-auto p-6">
        <ImageLibraryManager
          :api-base="apiBase"
          selectable
          :initial-view="initialView"
          @select="onImageSelected"
        />
      </main>
      <footer class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4">
        <div class="flex items-center gap-3" v-if="selectedImage">
          <img :src="selectedImage.url" :alt="selectedImage.display_name" class="h-14 w-14 rounded border border-slate-200 object-cover" />
          <div class="text-sm text-slate-600">
            <div class="font-semibold text-slate-800">{{ selectedImage.display_name }}</div>
            <div class="text-xs text-slate-500">{{ selectedImage.size_human }} · {{ selectedImage.dimensions_label ?? 'Unknown size' }}</div>
          </div>
        </div>
        <div class="flex flex-1 items-center justify-end gap-2">
          <button type="button" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100" @click="close">
            Cancel
          </button>
          <button
            type="button"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-slate-300"
            :disabled="!selectedImage"
            @click="confirmSelection"
          >
            Use image
          </button>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import ImageLibraryManager from './ImageLibraryManager.vue';

const props = defineProps({
  apiBase: { type: String, default: '/admin/images' },
  initialView: { type: String, default: 'grid' },
});

const emit = defineEmits(['selected', 'close', 'open']);

const isOpen = ref(false);
const selectedImage = ref(null);

const open = () => {
  isOpen.value = true;
  emit('open');
};

const close = () => {
  isOpen.value = false;
  emit('close');
};

const onImageSelected = (image) => {
  selectedImage.value = image;
};

const confirmSelection = () => {
  if (selectedImage.value) {
    emit('selected', selectedImage.value);
    close();
  }
};

watch(isOpen, (value) => {
  if (!value) {
    selectedImage.value = null;
  }
});

defineExpose({
  open,
  close,
  get selected() {
    return selectedImage.value;
  },
});
</script>
