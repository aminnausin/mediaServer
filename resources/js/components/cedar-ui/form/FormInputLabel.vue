<script setup lang="ts">
import type { FormField } from '@/types/types';

const props = defineProps<{
    field: FormField;
    textSize?: string;
    class?: string;
}>();
</script>

<template>
    <label
        v-if="field.text"
        :for="field.name"
        :class="[`block leading-none font-medium text-gray-700 dark:text-neutral-200`, textSize ? textSize : 'text-sm', `${props.class ?? ''}`]"
    >
        {{ field.text }}
        <p v-if="field.subtext" class="p text-sm/6 text-gray-500 dark:text-gray-400">{{ field.subtext }}</p>
    </label>
</template>
