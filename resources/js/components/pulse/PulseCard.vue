<script setup lang="ts">
const props = withDefaults(defineProps<{ cols?: number | string; rows?: number }>(), {
    cols: 'full',
    rows: 1,
});
</script>
<template>
    <!-- @props(['cols' => 6, 'rows' => 1]) -->
    <section
        :class="`flex flex-col p-3 sm:p-6 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-xl shadow-xs ring-1 ring-gray-900/5 default:col-span-full default:lg:col-span-${props.cols ?? 6} default:row-span-${props.rows ?? 1}`"
    >
        <slot mame="slot"></slot>
    </section>
</template>
