<script setup lang="ts">
defineProps<{
    name?: string;
    text?: string;
    subtext?: string;
    subtextClass?: string;
}>();
</script>

<template>
    <label :for="name" :class="[`font-medium text-gray-700 dark:text-neutral-200`]">
        {{ text }}
    </label>
    <p v-if="subtext" :class="['text-sm/6 text-gray-500 dark:text-gray-400', subtextClass]">{{ subtext }}</p>
</template>
