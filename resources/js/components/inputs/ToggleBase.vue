<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';

export interface ToggleProps {
    name?: string;
    title?: string;
    knobStyle?: string;
    class?: string;
    knobHorizontalMargin?: number;
}

const model = defineModel<boolean>();
const props = withDefaults(defineProps<ToggleProps>(), { name: 'toggle', knobHorizontalMargin: 1 });
</script>

<template>
    <label
        :class="
            cn(
                'group hover:bg-primary-dark-600 hover:has-[input:checked]:bg-primary-800',
                'focus-within:bg-primary-dark-600 focus-within:has-[input:checked]:bg-primary-800',
                '@container relative inline-flex h-8 w-16 cursor-pointer items-center rounded-lg',
                'border-primary-dark-800 bg-primary-dark-800 border-2 transition-colors duration-(--duration-input) has-checked:bg-white',
                props.class,
            )
        "
        :title="title ?? props.name"
        :for="props.name"
        :style="{
            '--knob-margin': knobHorizontalMargin,
        }"
    >
        <input type="checkbox" :id="props.name" :name="props.name" v-model="model" class="peer sr-only absolute" />

        <span
            :class="
                cn(
                    'group-hover:bg-primary-dark-600 bg-primary-dark-800 absolute top-1/4 left-[calc(var(--spacing)*var(--knob-margin))] aspect-square h-1/2 rounded-full shadow-[inset_16px_0_0_0_#fff]',
                    'transition-all duration-(--duration-input)',
                    'peer-checked:translate-x-[calc(100cqw-100%-var(--spacing)*var(--knob-margin)*2)] peer-checked:overflow-hidden peer-checked:shadow-none',
                    knobStyle,
                )
            "
        >
            <slot></slot>
        </span>
    </label>
</template>
