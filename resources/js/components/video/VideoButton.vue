<script setup lang="ts">
import type { Component } from 'vue';

import { computed, useTemplateRef, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { cn } from '@aminnausin/cedar-ui';

import VideoTooltipBase from '@/components/video/VideoTooltipBase.vue';

import ProiconsFullScreenMaximize from '~icons/proicons/full-screen-maximize';

const props = withDefaults(
    defineProps<{
        icon?: Component;
        title?: string;
        to?: string;
        useTooltip?: boolean;
        tooltipArrow?: boolean;
        style?: string;
        offset?: number;
        targetElement?: HTMLElement;
        controls?: boolean;
        useBackground?: boolean;
        verticalOffset?: string;
        class?: string | any[];
    }>(),
    {
        icon: ProiconsFullScreenMaximize,
        tooltipArrow: false,
        style: '',
        offset: 8,
        useBackground: true,
    },
);

const tooltip = useTemplateRef('tooltip');

const tooltipToggle = (event: MouseEvent, state: boolean = true) => {
    if (!props.useTooltip || !tooltip.value) return;

    tooltip.value.tooltipToggle(event, state);
};

const buttonStyle = computed(() => {
    const classes = ['relative text-white/80 transition-colors ease-in hover:text-white cursor-pointer'];
    if (props.useBackground) return cn(classes, 'rounded-full hover:bg-white/10 p-1');
    return classes;
});

const wrapper = computed(() => {
    return props.to ? RouterLink : 'button';
});

const wrapperProps = computed(() => ({
    'aria-label': props.title ?? `Player ${props.to ? 'Link' : 'Button'}`,
    title: props.useTooltip ? undefined : (props.title ?? `Player ${props.to ? 'Link' : 'Button'}`),
    to: props.to,
}));

watch(
    () => props.controls,
    () => {
        if (!props.controls) tooltipToggle(new MouseEvent('mouseleave'), false);
    },
);
</script>
<template>
    <component
        ref="el"
        :is="wrapper"
        :class="cn(buttonStyle, props.class)"
        @mouseenter="tooltipToggle"
        @mouseleave="(e: MouseEvent) => tooltipToggle(e, false)"
        v-bind="wrapperProps"
    >
        <VideoTooltipBase
            v-if="useTooltip"
            v-cloak
            ref="tooltip"
            :offset="offset"
            :tooltip-text="title"
            :tooltip-arrow="tooltipArrow"
            :vertical-offset="verticalOffset"
            :target-element="targetElement"
        />

        <slot name="icon">
            <component :is="icon" class="size-4" />
        </slot>
    </component>
</template>
