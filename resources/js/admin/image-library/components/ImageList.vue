<template>
  <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3 text-left">Preview</th>
          <th class="px-4 py-3 text-left">Details</th>
          <th class="px-4 py-3 text-left">Updated</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="image in images" :key="image.id" class="hover:bg-slate-50">
          <td class="px-4 py-3">
            <div class="h-20 w-20 overflow-hidden rounded border border-slate-200">
              <img :src="image.url" :alt="image.display_name" class="h-full w-full object-cover" />
            </div>
          </td>
          <td class="px-4 py-3">
            <div class="font-semibold text-slate-800">{{ image.display_name }}</div>
            <div class="text-xs text-slate-500">{{ image.size_human }} · {{ image.dimensions_label ?? 'Unknown size' }}</div>
            <div class="text-xs text-slate-400">{{ image.mime_type }}</div>
          </td>
          <td class="px-4 py-3 text-slate-500">
            <div>{{ image.updated_at_human ?? image.last_modified_human }}</div>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2 text-xs font-semibold">
              <button
                v-if="selectable"
                type="button"
                class="rounded border border-indigo-200 px-3 py-1 text-indigo-600 transition hover:bg-indigo-50"
                :class="{ 'bg-indigo-600 text-white hover:bg-indigo-500': selectedId === image.id }"
                @click="emit('select', image)"
              >
                {{ selectedId === image.id ? 'Selected' : 'Select' }}
              </button>
              <label class="inline-flex cursor-pointer items-center justify-center rounded border border-slate-300 px-3 py-1 text-slate-600 transition hover:bg-slate-100">
                <input class="hidden" type="file" accept="image/*" @change="onReplace(image, $event)" />
                <span v-if="replacingIds[image.id]">Replacing…</span>
                <span v-else>Replace</span>
              </label>
              <button
                type="button"
                class="rounded border border-red-200 px-3 py-1 text-red-600 transition hover:bg-red-50"
                :disabled="deletingIds[image.id]"
                @click="emit('delete', image)"
              >
                <span v-if="deletingIds[image.id]">Deleting…</span>
                <span v-else>Delete</span>
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="!images.length">
          <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
            No images found. Try adjusting your search or filters.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  images: { type: Array, default: () => [] },
  selectable: { type: Boolean, default: false },
  selectedId: { type: [String, Number], default: null },
  replacingIds: { type: Object, default: () => ({}) },
  deletingIds: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['select', 'replace', 'delete']);

const onReplace = (image, event) => {
  const [file] = event.target.files || [];
  event.target.value = '';

  if (file) {
    emit('replace', { image, file });
  }
};
</script>
