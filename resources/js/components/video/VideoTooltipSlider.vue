<script setup lang="ts">
import { reactive, ref, useTemplateRef } from 'vue';
import { getClientX } from '@/service/util';

const props = withDefaults(
    defineProps<{
        tooltipText?: string;
        tooltipArrow?: boolean;
        tooltipPosition?: 'top' | 'bottom';
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
        tooltipArrow: true,
        tooltipPosition: 'top',
        offset: 0,
        style: '',
    },
);

const data = reactive<{
    tooltipTimout: number | null;
    tooltipLeaveTimeout: number | null;
}>({
    tooltipTimout: null,
    tooltipLeaveTimeout: null,
});

const tooltipVisible = ref(false);
const tooltip = useTemplateRef('tooltip');

const tooltipEnter = () => {
    if (data.tooltipLeaveTimeout) clearTimeout(data.tooltipLeaveTimeout);

    if (tooltipVisible.value) return;

    if (data.tooltipTimout) clearTimeout(data.tooltipTimout);

    data.tooltipTimout = window.setTimeout(() => {
        tooltipVisible.value = true;
        if (!tooltip.value) return;
    }, props.tooltipDelay);
};

const tooltipLeave = () => {
    if (data.tooltipTimout) clearTimeout(data.tooltipTimout);
    if (!tooltipVisible.value) return;
    if (data.tooltipLeaveTimeout) clearTimeout(data.tooltipLeaveTimeout);
    data.tooltipLeaveTimeout = window.setTimeout(() => {
        tooltipVisible.value = false;
    }, props.tooltipLeaveDelay);
};

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

const tooltipToggle = (state: boolean = true) => {
    if (!state) {
        tooltipLeave();
        return;
    }

    tooltipEnter();
};

defineExpose({ calculateTooltipPosition, tooltipToggle, tooltipVisible });
</script>

<template>
    <div
        ref="tooltip"
        style="z-index: 9"
        v-cloak
        :class="['absolute flex items-center justify-center transition-opacity duration-300 ease-out', tooltipVisible ? 'opacity-100' : 'opacity-0 ease-in-out', style]"
    >
        <slot name="content">
            <div :class="`pointer-events-none h-full w-full transition-transform ${tooltipVisible ? 'scale-100' : 'scale-0 duration-150 ease-in-out'}`">
                <p
                    class="flex min-h-4 shrink-0 items-center justify-center whitespace-nowrap rounded-md bg-neutral-800 bg-opacity-90 px-2 py-1 font-mono text-xs shadow-xs backdrop-blur-xs"
                >
                    {{ tooltipText }}
                </p>
                <div
                    ref="tooltipArrow"
                    v-show="tooltipArrow"
                    class="absolute bottom-0 left-1/2 inline-flex w-2.5 -translate-x-1/2 translate-y-full items-center justify-center overflow-hidden"
                >
                    <div class="h-1.5 w-1.5 origin-top-left -rotate-45 transform bg-neutral-800 bg-opacity-90"></div>
                </div>
            </div>
        </slot>
    </div>
</template>
