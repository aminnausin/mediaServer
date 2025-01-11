<script setup lang="ts">
const props = defineProps(['field', 'textSize', 'class']);
</script>

<template>
    <label :for="field?.name" :class="`block font-medium text-gray-700 dark:text-neutral-200 ${textSize ? textSize : 'text-sm'} ${props.class ?? ''}`">
        {{ field?.text }}
    </label>
    <p v-if="field?.subtext" class="text-sm/6 text-gray-500 dark:text-gray-400">{{ field.subtext }}</p>
</template>
