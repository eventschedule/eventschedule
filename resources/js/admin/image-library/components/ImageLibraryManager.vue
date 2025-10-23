<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <label class="flex flex-col text-sm font-medium text-slate-700">
          <span class="mb-1 text-xs uppercase tracking-wide text-slate-500">Search</span>
          <input
            v-model="filters.search"
            type="search"
            placeholder="Search images"
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
          />
        </label>
        <label class="flex flex-col text-sm font-medium text-slate-700">
          <span class="mb-1 text-xs uppercase tracking-wide text-slate-500">Type</span>
          <select
            v-model="filters.type"
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
          >
            <option value="">All types</option>
            <option v-for="typeOption in availableTypeOptions" :key="typeOption" :value="typeOption">
              {{ typeOption.toUpperCase() }}
            </option>
          </select>
        </label>
      </div>
      <div class="flex items-center justify-end gap-2 text-xs font-semibold">
        <button
          type="button"
          class="rounded-md border border-slate-200 px-3 py-2 transition hover:bg-slate-100"
          :class="{ 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-500': viewMode === 'grid' }"
          @click="setViewMode('grid')"
        >
          Grid view
        </button>
        <button
          type="button"
          class="rounded-md border border-slate-200 px-3 py-2 transition hover:bg-slate-100"
          :class="{ 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-500': viewMode === 'list' }"
          @click="setViewMode('list')"
        >
          List view
        </button>
      </div>
    </div>

    <div
      class="relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition"
      :class="{
        'border-indigo-400 bg-indigo-50 text-indigo-600': isDragging,
        'opacity-60': uploading,
      }"
      @dragenter.prevent="onDragEnter"
      @dragover.prevent
      @dragleave.prevent="onDragLeave"
      @drop.prevent="onDrop"
    >
      <input
        ref="fileInput"
        type="file"
        multiple
        accept="image/*"
        class="hidden"
        @change="onFileInputChange"
      />
      <div class="text-sm text-slate-600">
        <strong>Drag & drop</strong> images here or
        <button
          type="button"
          class="font-semibold text-indigo-600 underline-offset-2 hover:underline"
          @click="openFilePicker"
        >
          browse your files
        </button>
      </div>
      <p class="mt-2 text-xs text-slate-500">PNG, JPG, GIF, or WebP up to 8 MB.</p>
      <div v-if="uploading" class="mt-3 text-xs font-medium text-indigo-600">Uploading images…</div>
    </div>

    <transition name="fade">
      <div
        v-if="errorMessage"
        class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700"
      >
        {{ errorMessage }}
      </div>
    </transition>

    <transition name="fade">
      <div
        v-if="successMessage"
        class="rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"
      >
        {{ successMessage }}
      </div>
    </transition>

    <div v-if="loading" class="flex items-center justify-center rounded-lg border border-dashed border-slate-200 bg-white py-16 text-sm text-slate-500">
      Loading images…
    </div>

    <ImageGrid
      v-if="!loading && viewMode === 'grid'"
      :images="images"
      :selectable="selectable"
      :selected-id="selectedId"
      :replacing-ids="replacingIds"
      :deleting-ids="deletingIds"
      @select="handleSelect"
      @replace="handleReplace"
      @delete="handleDelete"
    />

    <ImageList
      v-if="!loading && viewMode === 'list'"
      :images="images"
      :selectable="selectable"
      :selected-id="selectedId"
      :replacing-ids="replacingIds"
      :deleting-ids="deletingIds"
      @select="handleSelect"
      @replace="handleReplace"
      @delete="handleDelete"
    />
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';

import ImageGrid from './ImageGrid.vue';
import ImageList from './ImageList.vue';
import { normaliseCollection, normaliseImage, normaliseTypes } from '../utils';

const props = defineProps({
  apiBase: { type: String, default: '/admin/images' },
  selectable: { type: Boolean, default: false },
  initialSearch: { type: String, default: '' },
  initialType: { type: String, default: '' },
  initialView: { type: String, default: 'grid' },
});

const emit = defineEmits(['select']);

const baseUrl = computed(() => {
  const normalized = (props.apiBase || '').replace(/\/+$/, '');
  return normalized || '/admin/images';
});
const selectable = computed(() => props.selectable);

const filters = reactive({
  search: props.initialSearch,
  type: props.initialType,
});

const images = ref([]);
const availableTypes = ref([]);
const loading = ref(false);
const uploading = ref(false);
const isDragging = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const viewMode = ref(props.initialView === 'list' ? 'list' : 'grid');
const selectedId = ref(null);
const fileInput = ref(null);

const replacingIds = reactive({});
const deletingIds = reactive({});

let searchTimer;
let messageTimer;

const availableTypeOptions = computed(() => availableTypes.value);

const clearMessageTimer = () => {
  if (messageTimer) {
    clearTimeout(messageTimer);
    messageTimer = null;
  }
};

const setError = (message) => {
  successMessage.value = '';
  errorMessage.value = message;
  if (message) {
    clearMessageTimer();
    messageTimer = setTimeout(() => {
      errorMessage.value = '';
      messageTimer = null;
    }, 6000);
  }
};

const setSuccess = (message) => {
  errorMessage.value = '';
  successMessage.value = message;
  if (message) {
    clearMessageTimer();
    messageTimer = setTimeout(() => {
      successMessage.value = '';
      messageTimer = null;
    }, 4000);
  }
};

const extractError = (error) => {
  if (error.response?.data?.errors) {
    const first = Object.values(error.response.data.errors)[0];
    if (Array.isArray(first) && first.length) {
      return first[0];
    }
  }

  if (typeof error.response?.data?.message === 'string') {
    return error.response.data.message;
  }

  if (error.message) {
    return error.message;
  }

  return 'Something went wrong. Please try again.';
};

const fetchImages = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const { data } = await axios.get(baseUrl.value, {
      params: {
        search: filters.search || undefined,
        type: filters.type || undefined,
      },
    });

    const items = normaliseCollection(data.data ?? data.items ?? []);
    images.value = items;

    const typesSource =
      data.filters?.available_types ?? data.available_types ?? data.types ?? [];
    availableTypes.value = normaliseTypes(typesSource);

    if (filters.type && !availableTypes.value.includes(filters.type)) {
      filters.type = '';
    }

    if (selectedId.value && !items.some((item) => item.id === selectedId.value)) {
      selectedId.value = null;
    }
  } catch (error) {
    images.value = [];
    availableTypes.value = [];
    setError(extractError(error));
  } finally {
    loading.value = false;
  }
};

const setViewMode = (mode) => {
  viewMode.value = mode;
};

const openFilePicker = () => {
  fileInput.value?.click();
};

const handleFiles = async (fileList) => {
  const files = Array.from(fileList).filter((file) => file.type.startsWith('image/'));

  if (!files.length) {
    setError('Please choose at least one image file.');
    return;
  }

  uploading.value = true;
  const failures = [];

  for (const file of files) {
    const formData = new FormData();
    formData.append('image', file);

    try {
      await axios.post(baseUrl.value, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } catch (error) {
      failures.push(`${file.name}: ${extractError(error)}`);
    }
  }

  uploading.value = false;

  if (failures.length) {
    setError(`Upload completed with errors. ${failures.join(' ')}`);
  } else {
    setSuccess('Upload complete.');
  }

  await fetchImages();
};

const onFileInputChange = async (event) => {
  if (event.target.files?.length) {
    await handleFiles(event.target.files);
  }

  event.target.value = '';
};

const onDragEnter = () => {
  isDragging.value = true;
};

const onDragLeave = () => {
  isDragging.value = false;
};

const onDrop = async (event) => {
  isDragging.value = false;
  if (event.dataTransfer?.files?.length) {
    await handleFiles(event.dataTransfer.files);
  }
};

const updateBusyMap = (map, id, state) => {
  if (!id) {
    return;
  }

  if (state) {
    map[id] = true;
  } else {
    delete map[id];
  }
};

const handleReplace = async ({ image, file }) => {
  if (!image?.id) {
    setError('Unable to replace the selected image. Please try again.');
    return;
  }

  updateBusyMap(replacingIds, image.id, true);

  const formData = new FormData();
  formData.append('_method', 'PUT');
  formData.append('image', file);

  try {
    const { data } = await axios.post(`${baseUrl.value}/${image.id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    const updated = normaliseImage(data.data);

    if (!updated) {
      await fetchImages();
      return;
    }

    const index = images.value.findIndex((item) => item.id === image.id);

    if (index !== -1) {
      images.value.splice(index, 1, updated);
    }

    setSuccess('Image updated successfully.');
    await fetchImages();
  } catch (error) {
    setError(extractError(error));
  } finally {
    updateBusyMap(replacingIds, image.id, false);
  }
};

const handleDelete = async (image) => {
  if (!image?.id) {
    setError('Unable to delete the selected image. Please try again.');
    return;
  }

  if (!window.confirm('Delete this image permanently?')) {
    return;
  }

  updateBusyMap(deletingIds, image.id, true);

  try {
    await axios.delete(`${baseUrl.value}/${image.id}`);
    images.value = images.value.filter((item) => item.id !== image.id);
    setSuccess('Image deleted.');
    await fetchImages();
  } catch (error) {
    setError(extractError(error));
  } finally {
    updateBusyMap(deletingIds, image.id, false);
  }
};

const handleSelect = (image) => {
  const normalised = normaliseImage(image);

  if (!normalised) {
    setError('Unable to use the selected image. Please choose another image.');
    return;
  }

  selectedId.value = normalised.id;
  emit('select', normalised);
};

watch(
  () => filters.search,
  () => {
    if (searchTimer) {
      clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
      fetchImages();
    }, 350);
  }
);

watch(
  () => filters.type,
  () => {
    fetchImages();
  }
);

onMounted(() => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
  }

  fetchImages();
});

onBeforeUnmount(() => {
  if (searchTimer) {
    clearTimeout(searchTimer);
  }

  clearMessageTimer();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
