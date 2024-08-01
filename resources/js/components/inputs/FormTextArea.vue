<script setup>
import { ref } from 'vue';

const { field } = defineProps(['field']);
const model = defineModel();
const $el = ref(null)

const resize = () => { 
    $el.value.style.height = '0px'; 
    $el.value.style.height = $el.value.scrollHeight + 'px' 
} 
</script>

<template>
    <div class="block mt-1 w-full">
        <textarea @input="resize()" type="text" ref="$el"
            class="flex w-full h-auto min-h-[80px] px-3 py-2 text-sm border rounded-md bg-white border-gray-300 dark:border-gray-600 dark:text-gray-300 dark:bg-input-900 ring-offset-background placeholder:text-neutral-400 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400 disabled:cursor-not-allowed disabled:opacity-50"
            :name="field.name" 
            :title="field.name"
            :required="field.required"
            :placeholder="field.placeholder ?? 'Type your message here. I will resize based on the height content.'"
            v-model="model">
        </textarea>
    </div>
</template>