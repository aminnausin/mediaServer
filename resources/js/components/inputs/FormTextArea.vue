<script setup lang="ts">
import type { FormField } from '@/types/types';

import { onMounted, useTemplateRef } from 'vue';

const props = withDefaults(defineProps<{ field: FormField; maxHeight?: number }>(), { maxHeight: 240 });
const model = defineModel<any>();
const textArea = useTemplateRef('textArea');

const resize = () => {
    if (!textArea.value) return;
    textArea.value.style.height = '0px';
    textArea.value.style.height = Math.min(props.maxHeight, textArea.value.scrollHeight) + 'px';
};

onMounted(() => {
    resize();
});
</script>

<template>
    <div class="block mt-1 w-full">
        <textarea
            @input="resize()"
            type="text"
            ref="textArea"
            :class="`flex w-full h-auto min-h-[40px] px-3 py-2 text-sm rounded-md focus:outline-none border-none
            disabled:cursor-not-allowed disabled:opacity-50
            text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400
            ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700
            focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500
            scrollbar-minimal scrollbar-track:bg-neutral-300 scrollbar-track:dark:bg-neutral-800`"
            :style="`max-height: ${maxHeight}px;`"
            :name="field.name"
            :title="field.name"
            :required="field.required"
            :placeholder="field.placeholder ?? 'Type your message here. I will resize based on the height content.'"
            v-model="model"
        >
        </textarea>
    </div>
</template>
