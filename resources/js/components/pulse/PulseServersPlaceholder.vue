<script setup lang="ts">
withDefaults(
    defineProps<{
        cols?: number | string;
        rows?: number;
    }>(),
    {
        cols: 'full',
        rows: 1,
    },
);

const placeholderClass = 'mt-4 h-8 bg-white dark:bg-primary-dark-800 rounded-md ring-1 ring-gray-900/5 shadow-xs';
</script>
<template>
    <section :class="`h-[52px] flex items-center justify-between default:col-span-full default:lg:col-span-${cols} default:row-span-${rows} animate-pulse`">
        <div :class="['w-1/12', placeholderClass]"></div>
        <div :class="['w-3/12', placeholderClass]"></div>
        <div :class="['w-3/12', placeholderClass]"></div>
        <div :class="['w-2/12', placeholderClass]"></div>
    </section>
</template>
