<script setup lang="ts">
import { computed, useTemplateRef, watch, type Component } from 'vue';

import VideoTooltipBase from '@/components/video/VideoTooltipBase.vue';

import ProiconsFullScreenMaximize from '~icons/proicons/full-screen-maximize';

const props = withDefaults(
    defineProps<{
        icon?: Component;
        title?: string;
        link?: string;
        useTooltip?: boolean;
        tooltipArrow?: boolean;
        style?: string;
        offset?: number;
        targetElement?: HTMLElement;
        controls?: boolean;
        useBackground?: boolean;
        verticalOffset?: string;
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
    const classes = ['relative text-white/80 transition-colors ease-in hover:text-white'];
    if (props.useBackground) classes.push('rounded-full hover:bg-white/10 p-1');
    return classes;
});

watch(
    () => props.controls,
    () => {
        if (!props.controls) tooltipToggle(new MouseEvent('mouseleave'), false);
    },
);
</script>
<template>
    <router-link
        v-if="link?.length"
        :to="link"
        :aria-label="title ?? 'Video Link'"
        :title="useTooltip ? '' : (title ?? 'Video Link')"
        :class="buttonStyle"
        @mouseenter="tooltipToggle"
        @mouseleave="(e: MouseEvent) => tooltipToggle(e, false)"
    >
        <VideoTooltipBase v-if="useTooltip" v-cloak :tooltip-text="title" :tooltip-arrow="tooltipArrow" ref="tooltip" :target-element="targetElement" :offset="offset" />

        <slot name="icon">
            <component :is="icon" class="size-4" />
        </slot>
    </router-link>
    <button
        v-else
        :title="useTooltip ? '' : (title ?? 'Video Button')"
        :aria-label="title ?? 'Video Button'"
        :class="buttonStyle"
        @mouseenter="tooltipToggle"
        @mouseleave="(e) => tooltipToggle(e, false)"
    >
        <VideoTooltipBase
            v-if="useTooltip"
            v-cloak
            :tooltip-text="title"
            :tooltip-arrow="tooltipArrow"
            ref="tooltip"
            :target-element="targetElement"
            :offset="offset"
            :vertical-offset="verticalOffset"
        />

        <slot name="icon">
            <component :is="icon" class="size-4" />
        </slot>
    </button>
</template>
