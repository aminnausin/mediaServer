<script setup>
import FormInputLabel from '../labels/FormInputLabel.vue';

const model = defineModel();
const { text, placeholder, id } = defineProps(['text', 'placeholder', 'id'])
</script>
<template>
    <div class="flex flex-col justify-center gap-2 text-sm">
        <FormInputLabel :field="{name: id, text}"/>
        <input type="text" v-model="model" :name="id" :id="id" :placeholder="placeholder"
            class="h-10 p-2 shadow-sm rounded-md w-full focus:border-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500 text-gray-900 dark:text-neutral-100 border-neutral-200/70 bg-white dark:bg-neutral-800 text-sm">
    </div>
</template>