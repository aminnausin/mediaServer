<script setup lang="ts">
import type { Component, ComponentPublicInstance, ShallowRef } from 'vue';

import { computed, nextTick, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';
import { CedarOptions } from '@/components/cedar-ui/icons';
import { ButtonText } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
        buttonClass?: string;
        popoverClass?: string;
        buttonComponent?: Component;
        buttonAttributes?: Record<string, any>;
        hideDefaultIcon?: boolean;
        forcePopoverPosition?: 'top' | 'bottom';
        showPopoverArrow?: boolean;
        teleportDisabled?: boolean;
        teleportTarget?: string;
        scrollContainer?: 'body' | 'window';
        marginX?: number;
        offsetY?: number;
    }>(),
    {
        disabled: false,
        buttonComponent: ButtonText,
        hideDefaultIcon: false,
        showPopoverArrow: true,
        teleportDisabled: false,
        teleportTarget: 'body',
        scrollContainer: 'body',
        marginX: 8,
        offsetY: 12,
    },
);

const popoverOpen = ref(false);
const popoverDirection = ref<'top' | 'bottom'>('bottom');
const popoverStyles = ref<Record<string, string>>({});
const arrowStyles = ref<Record<string, string>>({});

const popover = useTemplateRef('popover');
const popoverButton = useTemplateRef<ComponentPublicInstance>('popoverButton');

const mergedButtonAttributes = computed(() => ({
    title: 'Open Menu',
    ...props.buttonAttributes,
}));

function getEl(ref: ShallowRef) {
    return (ref.value?.$el ?? ref.value) as HTMLElement;
}

const adjustPopoverPosition = () => {
    const popoverEl = getEl(popover);
    const buttonEl = getEl(popoverButton);

    if (!popoverEl || !buttonEl || !popoverOpen.value) return;

    const [scrollX, scrollY] = props.scrollContainer === 'body' ? [document.body.scrollLeft, document.body.scrollTop] : [window.scrollX, window.scrollY];

    const buttonRect = buttonEl.getBoundingClientRect();
    const popoverRect = popoverEl.getBoundingClientRect();

    if (buttonRect.width === 0) {
        popoverOpen.value = false;
        return;
    }

    popoverDirection.value =
        props.forcePopoverPosition ??
        (() => {
            const spaceBelow = window.innerHeight - buttonRect.bottom - props.offsetY;
            const spaceAbove = buttonRect.top - props.offsetY;
            return spaceBelow < popoverRect.height && spaceAbove >= popoverRect.height ? 'top' : 'bottom';
        })();

    const top = popoverDirection.value === 'top' ? buttonRect.top + scrollY - props.offsetY - popoverRect.height : buttonRect.bottom + props.offsetY + scrollY;

    let left = buttonRect.left - popoverRect.width / 2 + buttonRect.width / 2 + scrollX;

    const overflowRight = left + popoverRect.width - window.innerWidth + props.marginX + 8;
    const overflowLeft = props.marginX - left + 8;

    if (overflowRight > 0) {
        left = Math.max(left - overflowRight, props.marginX);
        arrowStyles.value = { left: `${popoverRect.width / 2 + overflowRight}px` };
    } else if (overflowLeft > 0) {
        left = left + overflowLeft;
        arrowStyles.value = { left: `${popoverRect.width / 2 - overflowLeft}px` };
    } else {
        arrowStyles.value = {};
    }

    popoverStyles.value = { left: `${left}px`, top: `${top}px` };
};

const handleClose = () => {
    popoverOpen.value = false;
};

watch(
    () => popoverOpen.value,
    (value) => {
        if (!value) return;

        nextTick(adjustPopoverPosition);
    },
);

function handleViewportChange() {
    if (!popoverOpen.value) return;

    requestAnimationFrame(adjustPopoverPosition);
}

onMounted(() => {
    window.addEventListener('resize', handleViewportChange, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('resize', handleViewportChange);
});

onClickOutside(popover, (event) => {
    if (popoverButton.value?.$el?.contains(event.target as Node)) return;
    handleClose();
});

defineExpose({ handleClose });
</script>
<template>
    <div class="relative flex">
        <component :is="buttonComponent" ref="popoverButton" :class="buttonClass" @click="popoverOpen = !popoverOpen" v-bind="mergedButtonAttributes" :disabled="disabled">
            <template #text>
                <slot name="buttonText"> </slot>
            </template>
            <template #icon>
                <slot name="buttonIcon">
                    <CedarOptions class="size-4" v-if="!hideDefaultIcon" />
                </slot>
            </template>
        </component>
        <Teleport :to="teleportTarget" :disabled="teleportDisabled">
            <Transition
                enter-active-class="ease-out duration-200 transition-opacity"
                enter-from-class="opacity-0"
                enter-to-class=" opacity-100"
                leave-active-class="ease-in duration-150 transition-opacity"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <UseFocusTrap
                    v-if="popoverOpen"
                    ref="popover"
                    :style="popoverStyles"
                    :options="{ allowOutsideClick: true, preventScroll: true }"
                    :class="cn('absolute z-50 w-75 max-w-lg', 'p-3', 'ring-r-button bg-overlay-2-t ring-1', 'rounded-md shadow backdrop-blur-xs', popoverClass)"
                    @keydown.esc="popoverOpen = false"
                >
                    <div
                        v-show="showPopoverArrow"
                        ref="popoverArrowRef"
                        :style="arrowStyles"
                        :class="[
                            'absolute left-1/2 inline-block w-5 -translate-x-2 overflow-hidden',
                            popoverDirection === 'bottom' ? 'top-0 -translate-y-2.5' : 'bottom-0 translate-y-2.5',
                        ]"
                    >
                        <div
                            :class="[
                                'border-r-button bg-overlay-2 size-2.5 rounded-xs border-l',
                                popoverDirection === 'bottom' ? 'origin-bottom-left rotate-45 border-t' : 'origin-top-left -rotate-45 border-b',
                            ]"
                        ></div>
                    </div>
                    <slot name="content"> </slot>
                </UseFocusTrap>
            </Transition>
        </Teleport>
    </div>
</template>
