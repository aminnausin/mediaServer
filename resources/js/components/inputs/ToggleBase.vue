<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';

export interface ToggleProps {
    name?: string;
    title?: string;
    knobStyle?: string;
    class?: string;
}

const model = defineModel<boolean>();
const props = withDefaults(defineProps<ToggleProps>(), { name: 'toggle' });
</script>

<template>
    <label
        :class="
            cn(
                'group hover:bg-primary-dark-600 hover:has-[input:checked]:bg-primary-800 relative inline-flex h-8 w-16 cursor-pointer items-center rounded-lg border-2 border-zinc-800 bg-zinc-800 transition-colors duration-300 has-checked:bg-white',
                props.class,
            )
        "
        :title="title ?? name"
        :for="name"
    >
        <input type="checkbox" :id="name" :name="name" v-model="model" class="peer sr-only" />

        <span
            :class="
                cn(
                    'group-hover:bg-primary-dark-600 absolute top-1/4 left-1/8 aspect-square h-1/2 rounded-full bg-zinc-800 shadow-[inset_16px_0_0_0_#fff]',
                    'transition-all duration-300',
                    'peer-checked:translate-x-8 peer-checked:overflow-hidden peer-checked:shadow-none',
                    knobStyle,
                )
            "
        >
            <slot></slot>
        </span>
    </label>
</template>
