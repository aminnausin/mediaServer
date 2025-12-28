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
                'text-foreground-4 bg-primary dark:bg-primary-dark shrink-0 truncate rounded-xl p-1 px-2 text-sm leading-none lowercase transition-colors duration-200',
                {
                    'cursor-default': props.URL,
                    'flex items-center justify-between gap-1': removeable,
                },
                props.class,
            )
        "
        v-bind="wrapperProps"
    >
        <slot> {{ label }}</slot>
        <ButtonCorner
            v-if="removeable"
            :positionClasses="'size-4'"
            :colourClasses="''"
            :class="
                cn(
                    'hover:bg-danger-2 text-white hover:text-white',
                    'dark:text-danger-2 dark:hover:text-foreground-0 dark:hover:bg-danger-2 bg-neutral-900/20 drop-shadow-sm',
                    btnClass,
                )
            "
            :label="'Remove'"
            @click.stop.prevent="$emit('clickAction')"
        />
    </component>
</template>
