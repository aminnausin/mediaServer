<script setup lang="ts">
const props = defineProps<{
    name?: string;
    text?: string;
    subtext?: string;
    class?: string;
    subtextClass?: string;
}>();
</script>

<template>
    <label :for="name" :class="[`cursor-pointer font-medium text-gray-700 dark:text-neutral-200`, props.class]">
        {{ text }}
    </label>
    <p v-if="subtext" :class="['text-sm/6 text-gray-500 dark:text-gray-400', subtextClass]">{{ subtext }}</p>
</template>
