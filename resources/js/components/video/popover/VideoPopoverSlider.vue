<script setup lang="ts">
import type { PopoverSlider } from '@/types/types';

import { ButtonBase } from '@/components/cedar-ui/button';
import { useId } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const id = useId();

const props = withDefaults(defineProps<PopoverSlider>(), { min: 10, max: 200, step: 5 });
const model = defineModel();
const resetModel = () => {
    if (props.defaultValue === undefined) return;
    model.value = props.defaultValue;
    props.action?.();
};
</script>
<template>
    <label
        v-show="!hidden"
        :for="id"
        :title="title ?? 'Popover Slider'"
        :class="
            cn(
                'transition-input flex w-full flex-wrap items-center gap-y-2 rounded-md px-2 py-1.5 text-xs',
                'ring-white outline-hidden ring-inset focus-within:bg-neutral-950 hover:bg-neutral-900 focus:outline-none has-focus-visible:ring',
                { 'button-disabled': disabled },
                style,
            )
        "
        @dblclick="resetModel"
        @wheel="wheelAction"
    >
        <component v-if="icon" :is="icon" class="mr-2 size-4 shrink-0" />

        <span class="text-nowrap">{{ text }}</span>
        <ButtonBase
            v-if="defaultValue !== undefined"
            type="button"
            class="text-foreground-1 dark ml-auto h-fit cursor-pointer p-0 text-xs tracking-wide focus-visible:outline-offset-2"
            :title="defaultValue !== undefined ? `Reset ${text}` : undefined"
            @click="resetModel"
        >
            {{ shortcut }}
        </ButtonBase>
        <span v-else class="text-foreground-1 dark ml-auto cursor-pointer text-xs tracking-wide">
            {{ shortcut }}
        </span>
        <input
            v-model="model"
            type="range"
            :id="id"
            :class="`slider volume h-2 w-full ring-white outline-hidden focus-visible:ring`"
            :min="min"
            :max="max"
            :step="step"
            :disabled="disabled"
            @input="action"
        />
    </label>
</template>
