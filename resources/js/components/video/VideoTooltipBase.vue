<script setup lang="ts">
import { nextTick, reactive, ref, useTemplateRef } from 'vue';

const props = withDefaults(
    defineProps<{
        tooltipText?: string;
        tooltipArrow?: boolean;
        tooltipPosition?: 'top' | 'bottom';
        offset?: number;
        className?: string;
        style?: string;
        targetElement?: HTMLElement;
    }>(),
    {
        tooltipText: 'Tooltip Text',
        tooltipArrow: false,
        tooltipPosition: 'top',
        offset: 8,
        className: '',
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
const tooltipWidth = ref(48);
const tooltip = useTemplateRef('tooltip');

const tooltipEnter = (event: MouseEvent) => {
    if (data.tooltipLeaveTimeout) clearTimeout(data.tooltipLeaveTimeout);

    if (tooltipVisible.value) return;

    if (data.tooltipTimout) clearTimeout(data.tooltipTimout);

    tooltipWidth.value = tooltip.value?.offsetWidth ?? 48;

    data.tooltipTimout = setTimeout(async () => {
        tooltipVisible.value = true;

        if (!tooltip.value) return;

        await nextTick();
        tooltipWidth.value = tooltip.value.offsetWidth;

        calculateTooltipPosition(event);
        tooltipLeave(4000);
    }, data.tooltipDelay);
};

const tooltipLeave = (timeout: number = data.tooltipLeaveDelay) => {
    if (data.tooltipTimout) clearTimeout(data.tooltipTimout);
    if (!tooltipVisible.value) return;
    if (data.tooltipLeaveTimeout) clearTimeout(data.tooltipLeaveTimeout);
    data.tooltipLeaveTimeout = setTimeout(() => {
        tooltipVisible.value = false;
    }, timeout);
};

function calculateTooltipPosition(event: MouseEvent) {
    if (!tooltip.value || !props.targetElement || !event.target) return;

    const boundingElement = event.target as HTMLElement;
    const buttonRect = boundingElement.getBoundingClientRect();
    const targetRect = props.targetElement.getBoundingClientRect();

    const buttonCenterX = buttonRect.width / 2;

    let left = buttonCenterX - tooltipWidth.value / 2;
    // console.log(buttonCenterX, `${left}px`);

    if (left + buttonRect.left < targetRect.left + props.offset) {
        // console.log('left', left + buttonRect.left, targetRect.left + props.offset);
        left = targetRect.left - buttonRect.left + props.offset;
    } else if (left + buttonRect.left + tooltipWidth.value > targetRect.right - props.offset) {
        // console.log('right', left, targetRect.right - props.offset, targetRect.right - props.offset - tooltipWidth.value - buttonRect.left);
        left = targetRect.right - props.offset - tooltipWidth.value - buttonRect.left;
    }

    // if (left + tooltipWidth.value > targetRect.width) {
    //     left = targetRect.width - tooltipWidth.value;
    // }

    tooltip.value.style.left = `${left}px`;

    // if (left + tooltipWidth.value / 2 + props.offset > rect.width) {
    //     tooltip.value.style.right = `${props.offset}px`;
    //     tooltip.value.style.removeProperty('left');
    //     return;
    // }

    // tooltip.value.style.left = `${left}px`;
    // tooltip.value.style.removeProperty('right');
}

const tooltipToggle = (event: MouseEvent, state: boolean = true) => {
    if (!state) {
        tooltipLeave();
        return;
    }

    tooltipEnter(event);
};

defineExpose({ tooltipToggle });
</script>

<template>
    <Transition
        enter-active-class="ease-out duration-150"
        enter-from-class="scale-[0.8] opacity-60"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="ease-in-out duration-100"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-[0.1] opacity-50"
    >
        <div ref="tooltip" v-show="tooltipVisible" :class="`absolute ${className ? className : ''}`">
            <slot name="content">
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
            </slot>
        </div>
    </Transition>
</template>
