<script setup>
import FormInputLabel from '../labels/FormInputLabel.vue';

const model = defineModel();
const { text, placeholder, id } = defineProps(['text', 'placeholder', 'id']);
</script>
<template>
    <div class="flex flex-col justify-center gap-2 text-sm">
        <FormInputLabel :field="{ name: id, text }" />
        <input
            type="text"
            v-model="model"
            :name="id"
            :id="id"
            :placeholder="placeholder"
            :class="`h-10 p-2 shadow-sm rounded-md w-full text-sm 
            border-none outline-none 
            disabled:cursor-not-allowed disabled:opacity-50 
            text-gray-900 dark:text-neutral-100 bg-white dark:bg-primary-dark-800 placeholder:text-neutral-400
            ring-inset focus:ring-inset ring-[1px] ring-neutral-200 dark:ring-neutral-700
            hocus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500 hover:ring-violet-400 hover:dark:ring-violet-700`"
        />
    </div>
</template>
