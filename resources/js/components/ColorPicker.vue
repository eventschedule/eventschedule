<template>
    <div class="color-picker-root relative">
        <input type="hidden" :name="name" :value="selectedColor" />
        <button
            type="button"
            @click.stop="open = !open"
            class="w-11 h-11 rounded-full transition-all duration-150 flex items-center justify-center"
            :class="!selectedColor ? 'border-2 border-dashed border-gray-300 dark:border-gray-600' : ''"
            :style="selectedColor ? { backgroundColor: selectedColor } : {}"
        >
            <svg v-if="!selectedColor" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M20.599 1.5c-.376 0-.743.111-1.055.32l-5.08 3.385a18.747 18.747 0 00-3.471 2.987 10.04 10.04 0 014.815 4.815 18.748 18.748 0 002.987-3.472l3.386-5.079A1.902 1.902 0 0020.599 1.5zm-8.3 14.025a18.76 18.76 0 001.896-1.207 8.026 8.026 0 00-4.513-4.513A18.75 18.75 0 008.475 11.7l-.278.5a5.26 5.26 0 013.601 3.602l.502-.278zM6.75 13.5A3.75 3.75 0 003 17.25a1.5 1.5 0 01-1.601 1.497.75.75 0 00-.7 1.123 5.25 5.25 0 009.8-2.62 3.75 3.75 0 00-3.75-3.75z" />
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
                class="absolute start-0 top-12 z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-3"
            >
                <div class="grid grid-cols-7 gap-2">
                    <button
                        v-for="color in colors"
                        :key="color"
                        type="button"
                        class="w-8 h-8 rounded-full transition-transform duration-150 hover:scale-110"
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
