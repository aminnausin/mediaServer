<script setup lang="ts">
const props = defineProps<{ method?: string }>();
const colorClasses = getClasses();

function getClasses() {
    switch (props.method) {
        case 'GET':
        case 'OPTIONS':
            return 'text-purple-400 dark:text-purple-300 bg-purple-50 dark:bg-purple-900 border-purple-200 dark:border-purple-700';
        case 'POST':
        case 'PUT':
        case 'PATCH':
            return 'text-blue-400 dark:text-blue-300 bg-blue-50 dark:bg-blue-900 border-blue-200 dark:border-blue-700';
        case 'DELETE':
            return 'text-red-400 dark:text-red-300 bg-red-50 dark:bg-red-900 border-red-200 dark:border-red-700';
        default:
            return 'text-gray-400 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-500';
    }
}
</script>

<template>
    <span :class="`text-xs font-mono px-1 border rounded-sm font-semibold ${colorClasses}`">{{ props.method }}</span>
</template>
