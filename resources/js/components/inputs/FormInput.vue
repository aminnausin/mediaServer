<script setup>
const { field } = defineProps(['field']);
const model = defineModel();
</script>

<template>
    <input
        :class="
            'rounded-md shadow-sm block mt-1 w-full ' +
            'focus:outline-none border-none ' +
            'disabled:cursor-not-allowed disabled:opacity-50 ' +
            'text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400 ' +
            'ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700 ' +
            'focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500'
        "
        :name="field.name"
        :title="field.name"
        :type="field.type"
        :required="field.required"
        :placeholder="field.placeholder"
        :autocomplete="field.autocomplete"
        :aria-autocomplete="field.autocomplete ? 'list' : 'none'"
        :min="field.min ?? ''"
        :max="field.max ?? ''"
        v-model="model"
    />
</template>
