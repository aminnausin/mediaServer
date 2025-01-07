<script setup>
import { ref } from 'vue';

const { field, tabindex } = defineProps(['field', 'tabindex']);
const model = defineModel();
const $el = ref(null);

const resize = () => {
    $el.value.style.height = '0px';
    $el.value.style.height = $el.value.scrollHeight + 'px';
};
</script>

<template>
    <div class="block mt-1 w-full">
        <textarea
            @input="resize()"
            type="text"
            ref="$el"
            :class="`flex w-full h-auto min-h-[80px] px-3 py-2 text-sm rounded-md focus:outline-none border-none
            disabled:cursor-not-allowed disabled:opacity-50
            text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400
            ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700
            focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500
            scrollbar-thin`"
            :name="field.name"
            :title="field.name"
            :required="field.required"
            :placeholder="field.placeholder ?? 'Type your message here. I will resize based on the height content.'"
            :tabindex="tabindex ?? 0"
            v-model="model"
        >
        </textarea>
    </div>
</template>
