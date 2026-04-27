<script setup lang="ts">
import type { InputHTMLAttributes } from 'vue';
import type { InputShellProps } from '@aminnausin/cedar-ui';

import { InputShell } from '@/components/cedar-ui/input';
import { ref } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

defineOptions({ inheritAttrs: false });

const model = defineModel();
const props = defineProps<InputHTMLAttributes & InputShellProps>();
const el = ref<HTMLInputElement | null>(null);

defineExpose({ el });
</script>
<template>
    <InputShell :clamp-text="clampText">
        <template #input="{ class: inputClass }">
            <input
                ref="el"
                v-model="model"
                v-bind="$attrs"
                :class="cn(inputClass, props.class)"
                :type="type"
                :min="min"
                :max="max"
                :minlength="minlength"
                :maxlength="maxlength"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
            />
        </template>
    </InputShell>
</template>
