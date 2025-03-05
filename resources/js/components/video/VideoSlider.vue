<script setup lang="ts">
import { useTemplateRef, watch, type Component } from 'vue';

import VideoTooltipBase from '@/components/video/VideoTooltipBase.vue';

import ProiconsFullScreenMaximize from '~icons/proicons/full-screen-maximize';

const props = withDefaults(
    defineProps<{
        icon?: Component;
        title?: string;
        text?: string;
        useTooltip?: boolean;
        tooltipArrow?: boolean;
        style?: string;
        offset?: number;
        targetElement?: HTMLElement;
        controls?: boolean;
        action?: (...args: any[]) => void;
        wheelAction?: (event: WheelEvent) => void;
    }>(),
    {
        icon: ProiconsFullScreenMaximize,
        tooltipArrow: false,
        useTooltip: true,
        style: '',
        offset: 8,
    },
);

const tooltip = useTemplateRef('tooltip');
const model = defineModel();

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
    <div class="relative h-1.5 mx-0 group-hover:mx-1 rounded-full group-hover:w-12 invisible group-hover:visible w-0 ease-out duration-300">
        <input
            v-model="model"
            @input="action"
            @wheel="wheelAction"
            @mouseenter="tooltipToggle"
            @mouseleave="(e) => tooltipToggle(e, false)"
            type="range"
            min="0"
            max="1"
            step="0.01"
            :class="`w-full h-full appearance-none flex items-center cursor-pointer bg-transparent slider volume${style ? ` ${style}` : ''}`"
            :title="title"
        />
        <VideoTooltipBase v-if="useTooltip" v-cloak :tooltip-text="text" :tooltip-arrow="tooltipArrow" :class-name="`-top-12`" ref="tooltip" :target-element="targetElement" />
    </div>
</template>
