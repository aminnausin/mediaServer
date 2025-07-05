<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        type?: 'reset' | 'submit' | 'button' | undefined;
        disabled?: boolean;
        title?: string;
        variant?: 'default' | 'ghost' | 'transparent';
        to?: string;
        target?: string;
    }>(),
    {
        variant: 'default',
        target: '_self',
        type: 'button',
    },
);

const variantClass = computed(() => {
    switch (props.variant) {
        case 'ghost':
            return '';
        case 'transparent':
            return [
                'transition',
                'hover:bg-white hover:dark:bg-primary-dark-800',
                'focus:outline-none hover:text-gray-900 dark:text-neutral-100',
                'hocus:ring-[0.125rem] hover:ring-violet-400 hover:dark:ring-violet-700 focus:ring-white',
                'aria-disabled:cursor-not-allowed aria-disabled:hover:ring-neutral-200 aria-disabled:hover:dark:ring-neutral-700 aria-disabled:ring-1 aria-disabled:opacity-60',
                'disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:hover:ring-1 disabled:opacity-60',
            ].join(' ');
        default:
            return [
                'h-10 max-h-full aspect-square rounded-md ',
                'p-2 shadow-sm',
                'focus:outline-none text-gray-900 dark:text-neutral-100',
                'ring-1 ring-neutral-200 dark:ring-neutral-700 hocus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500 hover:ring-violet-400 hover:dark:ring-violet-700',
                'bg-white dark:bg-primary-dark-800',
                'aria-disabled:cursor-not-allowed aria-disabled:hover:ring-neutral-200 aria-disabled:hover:dark:ring-neutral-700 aria-disabled:ring-1 aria-disabled:opacity-60',
                'disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:ring-1 disabled:opacity-60',
            ].join(' ');
    }
});
</script>

<template>
    <router-link
        v-if="to"
        :to="to"
        :class="['flex items-center justify-center cursor-pointer', variantClass]"
        :type="props.type"
        :title="props.title ?? 'Button'"
        :aria-disabled="disabled"
        :target="target"
    >
        <slot name="text"> </slot>
        <slot name="icon"> </slot>
    </router-link>
    <button v-else :class="['flex items-center justify-center cursor-pointer', variantClass]" :type="props.type" :disabled="props.disabled" :title="props.title ?? 'Icon'">
        <slot name="text"> </slot>
        <slot name="icon"> </slot>
    </button>
</template>
