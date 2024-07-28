<script setup>
const model = defineModel();
const { text, placeholder, id } = defineProps(['text', 'placeholder', 'id'])
</script>
<template>
    <div class="flex justify-start items-center gap-2">
        <label class="text-neutral-950 dark:text-white" :for="id">{{ text }}:</label>
        <input type="text" v-model="model" :name="id" :id="id" :placeholder="placeholder"
            class="p-2 text-neutral-950 dark:text-white bg-input-100 border-input-200 shadow-sm leading-none dark:bg-input-800 dark:border-indigo-800 rounded-lg w-full">
    </div>
</template>