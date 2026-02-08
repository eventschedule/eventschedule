<template>
    <div class="color-picker-root relative">
        <input type="hidden" :name="name" :value="selectedColor" />
        <button
            type="button"
            @click.stop="open = !open"
            class="w-14 h-14 rounded-full transition-all duration-150 flex items-center justify-center"
            :class="!selectedColor ? 'border-2 border-dashed border-gray-300 dark:border-gray-600' : ''"
            :style="selectedColor ? { backgroundColor: selectedColor } : {}"
        >
            <svg v-if="!selectedColor" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" />
            </svg>
        </button>
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                @click.stop
                class="absolute start-0 mt-2 z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4"
            >
                <div class="grid grid-cols-7 gap-3">
                    <button
                        v-for="color in colors"
                        :key="color"
                        type="button"
                        class="w-10 h-10 rounded-full transition-transform duration-150 hover:scale-110"
                        :class="selectedColor === color ? 'outline outline-2 outline-offset-1 outline-gray-800 dark:outline-white' : ''"
                        :style="{ backgroundColor: color }"
                        :title="color"
                        @click="selectColor(color)"
                    ></button>
                </div>
                <button
                    type="button"
                    class="mt-2 w-full text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors duration-150 py-1"
                    @click="selectColor('')"
                >{{ clearLabel }}</button>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    name: { type: String, required: true },
    initialColor: { type: String, default: '' },
    colors: { type: Array, required: true },
    clearLabel: { type: String, default: 'Clear' },
});

const open = ref(false);
const selectedColor = ref(props.initialColor);

function selectColor(color) {
    selectedColor.value = color;
    open.value = false;
}

function onClickOutside(e) {
    if (open.value) {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', onClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onClickOutside);
});
</script>
