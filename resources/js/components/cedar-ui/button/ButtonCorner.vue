<script setup lang="ts">
import { CedarDelete2 } from '../icons';
import { ButtonBase } from '.';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(
    defineProps<{
        to?: string;
        label?: string;
        disabled?: boolean;
        positionClasses?: string; // legacy
        colourClasses?: string; // legacy
        textClasses?: string; // legacy
        useDefaultStyle?: boolean;
        class?: string;
    }>(),
    {
        useDefaultStyle: true,
    },
);

const wrapperProps = computed(() => {
    return props.to
        ? { to: props.to, title: props.label ?? 'Link', 'aria-label': props.label ?? 'Link' }
        : {
              'aria-label': props.label ?? 'Close Modal',
              title: props?.label ?? 'Close Modal',
          };
});

const defaultClasses = computed(() => {
    return props.useDefaultStyle
        ? {
              position: 'absolute top-0 right-0 size-8 mt-5 mr-5',
              colour: 'focus-visible:bg-surface-1 hover:bg-surface-1',
              text: 'text-foreground-1 hover:text-foreground-0',
          }
        : { position: '', colour: '', text: '' };
});
</script>

<template>
    <ButtonBase
        :disabled="disabled"
        :use-size="false"
        :class="cn('rounded-full p-0', positionClasses ?? defaultClasses.position, colourClasses ?? defaultClasses.colour, textClasses ?? defaultClasses.text, props.class)"
        v-bind="wrapperProps"
    >
        <slot name="icon">
            <CedarDelete2 />
        </slot>
    </ButtonBase>
</template>
