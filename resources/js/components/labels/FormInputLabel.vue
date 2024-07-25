<script setup>
    const { name, text } = defineProps(['name', 'text']);
</script>

<template>
    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" :for="name">
        {{ text }}
    </label>
</template>