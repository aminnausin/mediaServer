<script setup lang="ts">
import type { FormField } from '@/types/types';

defineProps<{
    field: FormField;
    textSize?: string;
}>();
</script>

<template>
    <label :for="field?.name" :class="[`leading-none block font-medium text-gray-700 dark:text-neutral-200`, textSize ? textSize : 'text-sm']">
        {{ field?.text }}
    </label>
    <p v-if="field?.subtext" class="text-sm/6 text-gray-500 dark:text-gray-400">{{ field.subtext }}</p>
</template>
