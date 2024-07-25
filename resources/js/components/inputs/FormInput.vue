<script setup>
    const { name, type, required, placeholder, autocomplete } = defineProps(['name', 'type', 'required', 'placeholder', 'autocomplete']);
    const model = defineModel();
</script>

<template>
    <input 
        class="border-gray-300 dark:border-gray-600 dark:text-gray-300 dark:bg-input-900 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" 
        :name="name" 
        :type="type" 
        :required="required ? 'required' : ''" 
        :placeholder="placeholder"
        :autocomplete="autocomplete" 
        :aria-autocomplete="autocomplete ? 'list' : 'none'"
        v-model="model"
    >
</template>