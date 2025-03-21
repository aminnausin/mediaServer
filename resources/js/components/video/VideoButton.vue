<script setup lang="ts">
import { useTemplateRef, watch, type Component } from 'vue';

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
    }>(),
    {
        icon: ProiconsFullScreenMaximize,
        tooltipArrow: false,
        style: '',
        offset: 8,
    },
);

const tooltip = useTemplateRef('tooltip');

const tooltipToggle = (event: MouseEvent, state: boolean = true) => {
    if (!props.useTooltip || !tooltip.value) return;

    tooltip.value.tooltipToggle(event, state);
};

watch(
    () => props.controls,
    () => {
        if (props.controls == false) tooltipToggle(new MouseEvent('mouseleave'), false);
    },
);
</script>
<template>
    <router-link
        v-if="link?.length"
        :to="link"
        :title="useTooltip ? '' : (title ?? 'Video Button')"
        class="transition-opacity ease-in opacity-80 hover:opacity-100 relative"
        @mouseenter="tooltipToggle"
        @mouseleave="(e: MouseEvent) => tooltipToggle(e, false)"
    >
        <VideoTooltipBase
            v-if="useTooltip"
            v-cloak
            :tooltip-text="title"
            :tooltip-arrow="tooltipArrow"
            :class-name="`-top-12`"
            ref="tooltip"
            :target-element="targetElement"
            :offset="offset"
        />

        <slot name="icon">
            <component :is="icon" class="w-4 h-4" />
        </slot>
    </router-link>
    <button
        v-else
        :title="useTooltip ? '' : (title ?? 'Video Button')"
        class="transition-opacity ease-in opacity-80 hover:opacity-100 relative"
        @mouseenter="tooltipToggle"
        @mouseleave="(e) => tooltipToggle(e, false)"
    >
        <VideoTooltipBase
            v-if="useTooltip"
            v-cloak
            :tooltip-text="title"
            :tooltip-arrow="tooltipArrow"
            :class-name="`-top-12`"
            ref="tooltip"
            :target-element="targetElement"
            :offset="offset"
        />

        <slot name="icon">
            <component :is="icon" class="w-4 h-4" />
        </slot>
    </button>
</template>
