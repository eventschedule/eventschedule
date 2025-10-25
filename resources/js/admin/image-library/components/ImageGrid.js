import { computed, defineComponent, toRefs } from 'vue';
import { normaliseCollection } from '../utils';

export default defineComponent({
  name: 'ImageGrid',
  props: {
    images: { type: Array, default: () => [] },
    selectable: { type: Boolean, default: false },
    selectedId: { type: [String, Number], default: null },
    replacingIds: { type: Object, default: () => ({}) },
    deletingIds: { type: Object, default: () => ({}) },
  },
  emits: ['select', 'replace', 'delete'],
  setup(props, { emit }) {
    const { images, selectable, selectedId, replacingIds, deletingIds } = toRefs(props);

    const safeImages = computed(() => normaliseCollection(images.value));

    const selectImage = (image) => {
      emit('select', image);
    };

    const replaceImage = (image, event) => {
      const [file] = event.target.files || [];
      event.target.value = '';

      if (file) {
        emit('replace', { image, file });
      }
    };

    const deleteImage = (image) => {
      emit('delete', image);
    };

    return {
      safeImages,
      selectable,
      selectedId,
      replacingIds,
      deletingIds,
      selectImage,
      replaceImage,
      deleteImage,
    };
  },
  template: /* html */ `
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="image in safeImages"
        :key="image.id"
        class="rounded-lg border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
        :class="{ 'ring-2 ring-indigo-500': selectedId === image.id }"
      >
        <div class="relative">
          <img
            :src="image.url"
            :alt="image.display_name"
            class="h-48 w-full rounded-t-lg object-cover"
          />
          <button
            v-if="selectable"
            type="button"
            class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow"
            @click="selectImage(image)"
          >
            {{ selectedId === image.id ? 'Selected' : 'Select' }}
          </button>
        </div>
        <div class="space-y-3 p-4 text-sm text-slate-600">
          <div class="truncate text-base font-semibold text-slate-800" :title="image.display_name">
            {{ image.display_name }}
          </div>
          <ul class="space-y-1 text-xs text-slate-500">
            <li>Size · {{ image.size_human }}</li>
            <li>
              Dimensions ·
              <span v-if="image.dimensions_label">{{ image.dimensions_label }}</span>
              <span v-else>Unknown</span>
            </li>
            <li>Updated · {{ image.updated_at_human ?? image.last_modified_human }}</li>
          </ul>
          <div class="flex flex-wrap gap-2">
            <label class="inline-flex cursor-pointer items-center justify-center rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
              <input
                class="hidden"
                type="file"
                accept="image/*"
                @change="replaceImage(image, $event)"
              />
              <span v-if="replacingIds[image.id]">Replacing…</span>
              <span v-else>Replace</span>
            </label>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded border border-red-200 px-3 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50"
              :disabled="deletingIds[image.id]"
              @click="deleteImage(image)"
            >
              <span v-if="deletingIds[image.id]">Deleting…</span>
              <span v-else>Delete</span>
            </button>
          </div>
        </div>
      </div>
      <div v-if="!safeImages.length" class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
        No images match the current filters.
      </div>
    </div>
  `,
});
