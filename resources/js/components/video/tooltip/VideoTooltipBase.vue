<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { useTooltipVisibility } from './useTooltipVisibility';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(
    defineProps<{
        tooltipText?: string;
        tooltipArrow?: boolean;
        tooltipPosition?: 'top' | 'bottom';
        offset?: number;
        verticalOffset?: string;
        style?: string;
        targetElement?: HTMLElement;
        tooltipDelay?: number;
        tooltipLeaveDelay?: number;
    }>(),
    {
        tooltipDelay: 200,
        tooltipLeaveDelay: 100,
        tooltipText: 'Tooltip Text',
        tooltipArrow: false,
        tooltipPosition: 'top',
        verticalOffset: '-3rem',
        offset: 8,
    },
);

const cachedBoundingElement = ref<HTMLElement>();
const tooltipWidth = ref(48);
const tooltip = useTemplateRef('tooltip');

const { tooltipVisible, show, hide } = useTooltipVisibility(props.tooltipDelay, props.tooltipLeaveDelay);

function tooltipEnter(event?: MouseEvent) {
    show(
        event ? props.tooltipDelay : 0,
        async () => {
            if (!tooltip.value) return;

            await nextTick();
            tooltipWidth.value = tooltip.value.offsetWidth;

            calculateTooltipPosition(event);
            hide(4000);
        },
        () => (tooltipWidth.value = tooltip.value?.offsetWidth ?? 48),
    );
}

function calculateTooltipPosition(event?: MouseEvent | TouchEvent) {
    if (!tooltip.value || !props.targetElement) return;

    const sourceElement = (event?.target as HTMLElement) ?? cachedBoundingElement.value;
    if (!sourceElement) return;

    cachedBoundingElement.value = sourceElement;

    const buttonRect = cachedBoundingElement.value.getBoundingClientRect();
    const targetRect = props.targetElement.getBoundingClientRect();

    const buttonCenterX = buttonRect.width / 2;

    let left = buttonCenterX - tooltipWidth.value / 2;

    if (left + buttonRect.left < targetRect.left + props.offset) {
        left = targetRect.left - buttonRect.left + props.offset;
    } else if (left + buttonRect.left + tooltipWidth.value > targetRect.right - props.offset) {
        left = targetRect.right - props.offset - tooltipWidth.value - buttonRect.left;
    }

    tooltip.value.style.left = `${left}px`;
}

function tooltipToggle(event?: MouseEvent, state: boolean = true) {
    if (!state) {
        hide();
        return;
    }

    tooltipEnter(event);
}

const handleTooltipLeave = (e: Event) => {
    hide(50);
};

onMounted(() => {
    window.addEventListener('resize', handleTooltipLeave);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleTooltipLeave);
});

defineExpose({ calculateTooltipPosition, tooltipToggle, tooltipEnter, tooltipLeave: hide, tooltipVisible });

watch(
    () => props.tooltipText,
    async () => {
        if (!tooltipVisible.value) return;
        tooltipEnter();
    },
);
</script>

<template>
    <Transition enter-from-class="scale-[0.8]" leave-to-class="scale-[0.1]">
        <div
            v-show="tooltipVisible"
            ref="tooltip"
            :class="cn('absolute text-white opacity-60 transition-[opacity,scale] duration-75 ease-in', { 'duration-input scale-100 opacity-100 ease-out': tooltipVisible }, style)"
            style="z-index: 9"
            :style="{ top: verticalOffset }"
        >
            <slot name="content">
                <p
                    class="bg-opacity-90 flex min-h-4 shrink-0 items-center justify-center rounded-md bg-neutral-800/90 px-2 py-1 font-mono text-xs whitespace-nowrap shadow-xs backdrop-blur-xs"
                >
                    {{ tooltipText }}
                </p>
                <div
                    ref="tooltipArrow"
                    v-show="tooltipArrow"
                    class="absolute bottom-0 left-1/2 inline-flex w-2.5 -translate-x-1/2 translate-y-full items-center justify-center overflow-hidden"
                >
                    <div class="bg-opacity-90 h-1.5 w-1.5 origin-top-left -rotate-45 transform bg-neutral-800/90"></div>
                </div>
            </slot>
        </div>
    </Transition>
</template>
