<script setup>
const model = defineModel();
const { placeholder, id, minlength, maxlength } = defineProps(['placeholder', 'id', 'minlength', 'maxlength']);
</script>
<template>
    <input
        type="text"
        v-model="model"
        :name="id"
        :id="id"
        :minlength="minlength"
        :maxlength="maxlength"
        :placeholder="placeholder"
        :class="`h-10 p-2 shadow-xs rounded-md w-full text-sm text-ellipsis whitespace-nowrap line-clamp-1
            border-none outline-hidden
            disabled:cursor-not-allowed disabled:opacity-50
            text-gray-900 dark:text-neutral-100 bg-white dark:bg-primary-dark-800 placeholder:text-neutral-400
            ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700
            hocus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500 hover:ring-violet-400 dark:hover:ring-violet-700`"
    />
</template>
