<script setup lang="ts">
withDefaults(
    defineProps<{
        disabled?: boolean;
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        disabled: false,
        type: 'button',
    },
);
</script>
<template>
    <button
        :type="type"
        :class="[
            'inline-flex items-center justify-center px-4 py-2 text-sm transition-colors border dark:border-neutral-600 rounded-md focus:outline-none',
            'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900',
            'disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:hover:ring-1 disabled:opacity-60',
        ]"
        :disabled="disabled"
    >
        <slot name="text"></slot>
    </button>
</template>
