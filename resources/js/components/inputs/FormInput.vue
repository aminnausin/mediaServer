<script setup lang="ts">
import type { FormField } from '@/types/types';

defineProps<{ field: FormField }>();
const model = defineModel();
</script>

<template>
    <input
        :class="[
            'rounded-md shadow-sm block mt-1 w-full text-sm',
            'focus:outline-none border-none',
            'disabled:cursor-not-allowed disabled:opacity-50',
            'text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400',
            'ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700',
            'focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500',
        ]"
        :name="field.name"
        :title="field.text ?? field.name"
        :type="field.type"
        :required="field.required ? 'true' : 'false'"
        :placeholder="field.placeholder"
        :autocomplete="field.autocomplete"
        :aria-autocomplete="field.ariaAutocomplete ?? (field.autocomplete ? 'list' : 'none')"
        :min="field.min ?? ''"
        :max="field.max ?? ''"
        v-model="model"
    />
</template>
