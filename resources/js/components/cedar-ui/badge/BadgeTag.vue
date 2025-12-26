<script setup lang="ts">
import { ButtonCorner } from '../button';
import { RouterLink } from 'vue-router';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = defineProps<{
    label?: string;
    removeable?: boolean;
    btnClass?: string;
    class?: string;
    URL?: string;
}>();

const wrapper = computed(() => (props.URL ? RouterLink : 'p'));
const wrapperProps = computed(() => (props.URL ? { to: props.URL } : {}));
</script>
<template>
    <component
        :is="wrapper"
        :class="
            cn(
                'p-1 px-2 text-sm leading-none rounded-xl truncate lowercase text-foreground-4 bg-primary dark:bg-primary-dark shrink-0 transition-colors duration-300',
                {
                    'cursor-default': props.URL,
                    'flex gap-1 items-center justify-between': removeable,
                },
                props.class,
            )
        "
        v-bind="wrapperProps"
    >
        <slot> {{ label }}</slot>
        <ButtonCorner
            v-if="removeable"
            :positionClasses="'w-4 h-4'"
            :colourClasses="''"
            :class="
                cn(
                    'text-white hover:text-white hover:bg-danger-2',
                    'dark:text-danger-2 dark:hover:text-foreground bg-neutral-900/20 dark:hover:bg-danger-2 drop-shadow-sm',
                    btnClass,
                )
            "
            :label="'Remove'"
            @click.stop.prevent="$emit('clickAction')"
        />
    </component>
</template>
