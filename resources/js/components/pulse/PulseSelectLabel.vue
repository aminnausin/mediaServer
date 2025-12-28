<script setup lang="ts">
defineProps<{ controlId: string }>();
</script>
<template>
    <label
        class="text-foreground-0 bg-primary-800 flex h-10 w-full items-center justify-between rounded-l-md p-3 py-2 text-left text-sm whitespace-nowrap capitalize shadow-xs ring-1 ring-neutral-200 ring-inset dark:bg-neutral-900 dark:ring-neutral-700"
        :for="controlId"
    >
        <slot></slot>
    </label>
</template>
