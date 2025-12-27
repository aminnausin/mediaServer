<script setup lang="ts">
import type { FormTextAreaProps } from '@aminnausin/cedar-ui';

import { onMounted, useTemplateRef } from 'vue';
import { InputShell } from '../input';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<FormTextAreaProps>(), { maxHeight: 240 });
const model = defineModel<any>();
const textArea = useTemplateRef('textArea');

const resize = () => {
    if (!textArea.value) return;
    textArea.value.style.height = Math.min(props.maxHeight, textArea.value.scrollHeight) + 'px';
};

onMounted(() => {
    resize();
});
</script>

<template>
    <div class="group mt-1 block w-full">
        <InputShell :clamp-text="false">
            <template #input="{ class: inputClass }">
                <textarea
                    @input="resize()"
                    v-model="model"
                    ref="textArea"
                    type="text"
                    :id="field.name"
                    :name="field.name"
                    :title="field.text ?? field.name"
                    :disabled="field.disabled"
                    :required="field.required"
                    :placeholder="field.placeholder"
                    :class="cn('scrollbar-minimal scrollbar-thumb:bg-foreground-1 min-h-8', inputClass)"
                    :style="{
                        'max-height': `${maxHeight}px`,
                        'text-overflow': 'unset',
                    }"
                />
            </template>
        </InputShell>
    </div>
</template>
