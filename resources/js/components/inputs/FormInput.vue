<script setup>
    const { field } = defineProps(['field']);
    const model = defineModel();
</script>

<template>
    <input 
        class="border-gray-300 dark:border-gray-600 dark:text-gray-300 dark:bg-input-900 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" 
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
    >
</template>