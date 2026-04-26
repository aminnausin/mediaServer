<script setup lang="ts">
import type { InputShellProps, TextInputProps } from '@aminnausin/cedar-ui';

import { InputShell } from '.';
import { ref } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

defineOptions({ inheritAttrs: false });

const model = defineModel();
const props = defineProps<TextInputProps & InputShellProps>();
const el = ref<HTMLInputElement | null>(null);

defineExpose({ el });
</script>
<template>
    <InputShell :clamp-text="clampText">
        <template #input="{ class: inputClass }">
            <input
                ref="el"
                type="text"
                v-model="model"
                :id="id"
                :minlength="minlength"
                :maxlength="maxlength"
                :placeholder="placeholder"
                :class="cn(inputClass, props.class)"
                :disabled="disabled"
                v-bind="$attrs"
            />
        </template>
    </InputShell>
</template>
