<script setup lang="ts">
const props = defineProps(['class', 'type', 'disabled', 'title']);
</script>

<template>
    <button
        :class="`p-2 flex items-center justify-center h-10 max-h-full gap-2
        shadow-sm rounded-md cursor-pointer focus:outline-none
        ring-1 ring-neutral-200 dark:ring-neutral-700 hocus:ring-[0.125rem]
        focus:ring-indigo-400 dark:focus:ring-indigo-500 hover:ring-violet-400 hover:dark:ring-violet-700
        text-gray-900 dark:text-neutral-100 bg-white dark:bg-primary-dark-800
         ${props.class} disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:ring-1 disabled:opacity-60`"
        :type="props.type"
        :disabled="props.disabled"
        :title="props.title ?? 'Button'"
    >
        <slot name="text"> </slot>
        <slot name="icon"> </slot>
    </button>
</template>
