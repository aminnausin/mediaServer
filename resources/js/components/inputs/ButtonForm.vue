<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
        type?: 'button' | 'submit' | 'reset';
        variant?: 'default' | 'submit' | 'reset' | 'auth';
        class?: string;
    }>(),
    {
        disabled: false,
        type: 'button',
        class: '',
    },
);
const variantClass = computed(() => {
    switch (props.variant) {
        case 'submit':
            return [
                'inline-flex items-center justify-center h-10 max-h-full px-4 py-2 font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none',
                'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900',
                'disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:hover:ring-1 disabled:opacity-60',
            ];
        case 'reset':
            return [
                'inline-flex items-center justify-center h-10 max-h-full px-4 py-2 font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none',
                'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900',
                'disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:hover:ring-1 disabled:opacity-60',
            ];
        case 'auth':
            return [
                'inline-flex items-center px-4 py-2',
                'bg-gray-800 dark:bg-gray-200',
                'border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest',
                'text-white dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white',
                'focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300',
                'focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800',
                'transition ease-in-out duration-150',
            ];
        default:
            return [
                'inline-flex items-center justify-center px-4 py-2 text-sm transition-colors border dark:border-neutral-600 rounded-md focus:outline-none',
                'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900',
                'disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:hover:ring-1 disabled:opacity-60',
            ];
    }
});
</script>
<template>
    <button :type="type" :class="[...variantClass, props.class]" :disabled="disabled">
        <slot name="text"></slot>
        <slot></slot>
    </button>
</template>
