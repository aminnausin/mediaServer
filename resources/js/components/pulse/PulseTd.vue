<script setup lang="ts">
withDefaults(defineProps<{ numeric?: boolean }>(), { numeric: false });
</script>
<template>
    <td
        :class="[
            'first:rounded-l-md last:rounded-r-md text-sm  dark:bg-primary-dark-800 bg-primary-900 ring-1 ring-gray-900/5 first:pl-3 last:pr-3 px-1 sm:px-3 py-3',
            { 'text-right tabular-nums whitespace-nowrap': numeric },
        ]"
    >
        <slot></slot>
    </td>
</template>
