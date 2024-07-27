<script setup>
const model = defineModel();
const { text } = defineProps(['text'])
</script>
<template>
    <div class="flex justify-start items-center gap-2">
        <label class="text-neutral-950 dark:text-white" :for="text">{{ text }}:</label>
        <input type="text" v-model="model"
            class="p-2 text-neutral-950 dark:text-white bg-input-100 border-input-200 shadow-sm leading-none dark:bg-input-800 dark:border-indigo-800 rounded-lg w-full">
    </div>
</template>