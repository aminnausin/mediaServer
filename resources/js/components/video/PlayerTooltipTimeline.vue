<script setup lang="ts">
import { useTooltipVisibility } from './tooltip/useTooltipVisibility';
import { useTemplateRef } from 'vue';
import { getClientX } from '@/service/util';
import { cn } from '@aminnausin/cedar-ui';

import PlayerStoryboard from '@/components/video/PlayerStoryboard.vue';

const props = withDefaults(
    defineProps<{
        tooltipText?: string;
        tooltipTime: number;
        tooltipArrow?: boolean;
        offset?: number;
        style?: string;
        targetElement?: HTMLElement;
        tooltipDelay?: number;
        tooltipLeaveDelay?: number;
    }>(),
    {
        tooltipDelay: 200,
        tooltipLeaveDelay: 100,
        tooltipText: 'Tooltip Text',
        tooltipTime: 0,
        tooltipArrow: false,
        offset: 2,
    },
);

const { tooltipVisible, show, hide } = useTooltipVisibility(props.tooltipDelay, props.tooltipLeaveDelay);

const tooltip = useTemplateRef('tooltip');

function calculateTooltipPosition(event: MouseEvent | TouchEvent) {
    if (!tooltip.value) return;

    const target = props.targetElement ?? (event.target as HTMLElement);
    const rect = target.getBoundingClientRect();
    const width = tooltip.value.offsetWidth;

    let left = Math.max(getClientX(event) - rect.left - width / 2 + props.offset, props.offset);

    if (left + width - props.offset > rect.width) {
        left = rect.width - width + props.offset;
    }

    tooltip.value.style.transform = `translateX(${left}px)`;
}

defineExpose({
    calculateTooltipPosition,
    show,
    hide,
});
</script>

<template>
    <div
        ref="tooltip"
        :class="
            cn(
                'xs:gap-2 pointer-events-none absolute flex flex-col items-center justify-center gap-1 opacity-0 transition-opacity duration-75 ease-in sm:gap-4',
                { 'duration-input scale-100 opacity-100 ease-out': tooltipVisible },
                style,
            )
        "
        style="z-index: 9"
    >
        <PlayerStoryboard :tooltip-time="tooltipTime" :class="cn('scale-50 transition-[scale] duration-150 ease-in', { 'duration-input scale-100 ease-out': tooltipVisible })" />
        <div :class="cn('mx-auto scale-0 transition-[scale] duration-75 ease-in', { 'duration-input scale-100 ease-out': tooltipVisible })">
            <p
                class="bg-opacity-90 flex min-h-4 shrink-0 items-center justify-center rounded-md bg-neutral-800 px-2 py-1 font-mono text-xs whitespace-nowrap shadow-xs backdrop-blur-xs"
            >
                {{ tooltipText }}
            </p>
            <div
                ref="tooltipArrow"
                v-show="tooltipArrow"
                class="absolute bottom-0 left-1/2 inline-flex w-2.5 -translate-x-1/2 translate-y-full items-center justify-center overflow-hidden"
            >
                <div class="bg-opacity-90 h-1.5 w-1.5 origin-top-left -rotate-45 transform bg-neutral-800"></div>
            </div>
        </div>
    </div>
</template>
