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
    <span class="inline-flex relative w-full mt-1">
        <input
            :class="[
                'rounded-md shadow-sm block w-full pe-12 text-sm',
                'focus:outline-none border-none',
                'disabled:cursor-not-allowed disabled:opacity-50',
                'text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400',
                'ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700',
                'focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500',
            ]"
            :name="field.name"
            :title="field.name"
            type="number"
            :required="field.required"
            :placeholder="field.placeholder"
            :aria-autocomplete="field.autocomplete ? 'list' : 'none'"
            :min="field.min ?? ''"
            :max="field.max ?? ''"
            v-model="model"
        />
        <span class="absolute top-0 right-0 h-full flex flex-col w-12">
            <button
                @click.prevent.stop="incrementNumber"
                class="h-1/2 items-center justify-center flex hover:bg-neutral-200/50 hover:dark:bg-neutral-600 rounded-tr-md ring-inset focus:outline-none focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500"
                :title="`Increment ${field.name}`"
            >
                <LucideChevronUp width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4" />
            </button>
            <button
                @click.prevent.stop="decrementNumber"
                class="h-1/2 items-center justify-center flex hover:bg-neutral-200/50 hover:dark:bg-neutral-600 rounded-br-md ring-inset focus:outline-none focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500"
                :title="`Decrement ${field.name}`"
            >
                <LucideChevronDown width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4" />
            </button>
        </span>
    </span>
</template>
