<script setup lang="ts">
import { reactive, ref, useTemplateRef } from 'vue';

const props = withDefaults(
    defineProps<{
        tooltipText?: string;
        tooltipArrow?: boolean;
        tooltipPosition?: 'top' | 'bottom';
        offset?: number;
        style?: string;
        targetElement?: HTMLElement;
    }>(),
    {
        tooltipText: 'Tooltip Text',
        tooltipArrow: true,
        tooltipPosition: 'top',
        offset: 0,
        style: '',
    },
);

const data = reactive<{
    tooltipDelay: number;
    tooltipLeaveDelay: number;
    tooltipTimout: number | null;
    tooltipLeaveTimeout: number | null;
}>({
    tooltipDelay: 200,
    tooltipLeaveDelay: 100,
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
    }, data.tooltipDelay);
};

const tooltipLeave = () => {
    if (data.tooltipTimout) clearTimeout(data.tooltipTimout);
    if (!tooltipVisible.value) return;
    if (data.tooltipLeaveTimeout) clearTimeout(data.tooltipLeaveTimeout);
    data.tooltipLeaveTimeout = window.setTimeout(() => {
        tooltipVisible.value = false;
    }, data.tooltipLeaveDelay);
};

function calculateTooltipPosition(event: MouseEvent) {
    if (!tooltip.value) return;

    const target = props.targetElement ?? (event.target as HTMLElement);
    const rect = target.getBoundingClientRect();
    const width = tooltip.value.offsetWidth;

    let left = Math.max(event.clientX - rect.left - width / 2 + props.offset, props.offset);

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
        :class="['absolute flex items-center justify-center transition-opacity ease-out duration-300', tooltipVisible ? 'opacity-100' : 'ease-in-out opacity-0', style]"
    >
        <slot name="content">
            <div :class="`w-full h-full transition-transform pointer-events-none ${tooltipVisible ? 'scale-100' : 'duration-150 ease-in-out scale-0'}`">
                <p
                    class="flex-shrink-0 text-xs whitespace-nowrap min-h-4 py-1 px-2 bg-opacity-90 bg-neutral-800 backdrop-blur-sm rounded-md shadow-sm flex items-center justify-center font-mono"
                >
                    {{ tooltipText }}
                </p>
                <div
                    ref="tooltipArrow"
                    v-show="tooltipArrow"
                    class="bottom-0 -translate-x-1/2 left-1/2 w-2.5 translate-y-full absolute inline-flex items-center justify-center overflow-hidden"
                >
                    <div class="origin-top-left -rotate-45 w-1.5 h-1.5 transform bg-neutral-800 bg-opacity-90"></div>
                </div>
            </div>
        </slot>
    </div>
</template>
