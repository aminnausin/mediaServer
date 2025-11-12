<script setup lang="ts">
import type { FormFieldValue } from '@/composables/useForm';
import type { FormField } from '@/types/types';

import LucideChevronDown from '~icons/lucide/chevron-down';
import LucideChevronUp from '~icons/lucide/chevron-up';

const props = defineProps<{ field: FormField }>();
const model = defineModel<FormFieldValue>();

const incrementNumber = () => {
    if (model.value === props.field?.max) return;
    addToNumber(1);
};

const decrementNumber = () => {
    if (model.value === props.field?.min) return;
    addToNumber(-1);
};

const addToNumber = (change: 1 | -1) => {
    if (!model.value || typeof model.value != 'number') {
        model.value = 0;
        return;
    }
    model.value += change;
};
</script>

<template>
    <span class="relative mt-1 inline-flex w-full">
        <input
            :class="[
                'block w-full rounded-md pe-12 text-sm shadow-xs',
                'border-none focus:outline-hidden',
                'disabled:cursor-not-allowed disabled:opacity-50',
                'bg-white text-gray-900 placeholder:text-neutral-400 dark:bg-neutral-700 dark:text-neutral-100',
                'ring-1 ring-neutral-200 ring-inset focus:ring-inset dark:ring-neutral-700',
                'focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500',
            ]"
            :name="field.name"
            :title="field.text ?? field.name"
            type="number"
            :required="field.required"
            :placeholder="field.placeholder"
            :aria-autocomplete="field.autocomplete ? 'list' : 'none'"
            :min="field.min ?? ''"
            :max="field.max ?? ''"
            v-model="model"
        />
        <span class="absolute top-0 right-0 flex h-full w-12 flex-col">
            <button
                @click.prevent.stop="incrementNumber"
                class="flex h-1/2 items-center justify-center rounded-tr-md ring-inset hover:bg-neutral-200/50 focus:ring-2 focus:ring-indigo-400 focus:outline-hidden dark:hover:bg-neutral-600 dark:focus:ring-indigo-500"
                :title="`Increment ${field.name}`"
            >
                <LucideChevronUp width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4" />
            </button>
            <button
                @click.prevent.stop="decrementNumber"
                class="flex h-1/2 items-center justify-center rounded-br-md ring-inset hover:bg-neutral-200/50 focus:ring-2 focus:ring-indigo-400 focus:outline-hidden dark:hover:bg-neutral-600 dark:focus:ring-indigo-500"
                :title="`Decrement ${field.name}`"
            >
                <LucideChevronDown width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4" />
            </button>
        </span>
    </span>
</template>
