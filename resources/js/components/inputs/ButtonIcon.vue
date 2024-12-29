<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        class?: string;
        type?: 'reset' | 'submit' | 'button' | undefined;
        disabled?: boolean;
        title?: string;
        variant?: 'default' | 'ghost';
    }>(),
    {
        variant: 'default',
    },
);
</script>

<template>
    <button
        :class="`flex items-center justify-center h-10 max-h-full
        aspect-square rounded-md cursor-pointer focus:outline-none
        text-gray-900 dark:text-neutral-100
        ${
            variant === 'default'
                ? `p-2 shadow-sm
                ring-1 ring-neutral-200 dark:ring-neutral-700 hocus:ring-[0.125rem]
        focus:ring-indigo-400 dark:focus:ring-indigo-500 hover:ring-violet-400 hover:dark:ring-violet-700
         bg-white dark:bg-primary-dark-800
        disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:ring-1`
                : ''
        }
         ${props.class} `"
        :type="props.type"
        :disabled="props.disabled"
        :title="props.title ?? 'Icon'"
    >
        <slot name="icon"> </slot>
    </button>
</template>
