<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        value: number | string;
        isSelected: boolean;
        classOverride?: string;
    }>(),
    {
        isSelected: false,
    },
);
</script>

<template>
    <div
        class="flex items-center justify-center text-center rounded-md cursor-pointer"
        :class="`${classOverride ? `${classOverride} ` : ''}${isSelected ? 'bg-neutral-800 dark:bg-violet-700 text-white hover:bg-violet-700 dark:bg-opacity-60' : 'text-gray-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:text-neutral-100 dark:hover:bg-neutral-900'}`"
    >
        <slot name="text">
            {{ value }}
        </slot>
    </div>
</template>
