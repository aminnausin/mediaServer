<script setup>
const { field, textSize } = defineProps(['field', 'textSize']);
</script>

<template>
    <label class="block font-medium text-gray-700 dark:text-neutral-200" :for="field?.name" :class="{textSize : textSize, 'text-sm': !textSize}">
        {{ field?.text }}
    </label>
    <p v-if="field?.subtext" class="text-sm/6 text-gray-500 dark:text-gray-400">{{ field.subtext }}</p>
</template>