<script setup lang="ts">
import type { Component, ComponentPublicInstance } from 'vue';

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
        buttonAttributes?: { [key: string]: any };
        hideDefaultIcon?: boolean;
        forcePopoverPosition?: 'top' | 'bottom';
        showPopoverArrow?: boolean;
        teleportDisabled?: boolean;
        teleportTarget?: string;
        scrollContainer?: 'body' | 'window';
        marginY?: number;
        marginX?: number;
    }>(),
    {
        disabled: false,
        buttonComponent: ButtonText,
        hideDefaultIcon: false,
        showPopoverArrow: true,
        teleportDisabled: false,
        teleportTarget: 'body',
        scrollContainer: 'body',
        marginY: 12,
        marginX: 8,
    },
);

const popoverOpen = ref(false);
const popoverStyles = ref<Record<string, string>>({});
const popoverDirection = ref<'top' | 'bottom'>('bottom');
const arrowStyles = ref<Record<string, string>>({});

const popover = useTemplateRef('popover');
const popoverArrow = useTemplateRef('popoverArrowRef');
const popoverButton = useTemplateRef<ComponentPublicInstance>('popoverButton');

const init = ref(false);
const maxWidth = ref(0);
const maxHeight = ref(0);

const mergedButtonAttributes = computed(() => ({
    title: 'Open Menu',
    ...props.buttonAttributes,
}));

const adjustPopoverPosition = () => {
    if (!popover.value?.$el || !popoverButton.value?.$el) return;

    const [scrollX, scrollY] = props.scrollContainer === 'body' ? [document.body.scrollLeft, document.body.scrollTop] : [window.scrollX, window.scrollY];

    const buttonRect = popoverButton.value.$el.getBoundingClientRect();
    const popoverRect = popover.value.$el.getBoundingClientRect();

    maxWidth.value = Math.max(popoverRect.width, maxWidth.value);
    maxHeight.value = Math.max(popoverRect.height, maxHeight.value);

    if (!props.forcePopoverPosition) {
        const spaceBelow = window.innerHeight - buttonRect.bottom - props.marginY;
        const spaceAbove = buttonRect.top - props.marginY;
        popoverDirection.value = spaceBelow < maxHeight.value && spaceAbove >= maxHeight.value ? 'top' : 'bottom';
    }

    const verticalOffset = popoverDirection.value === 'top' ? buttonRect.top + scrollY - props.marginY - maxHeight.value : buttonRect.bottom + props.marginY + scrollY;

    const clampedTop = Math.max(verticalOffset, props.marginY + scrollY);

    let left = buttonRect.left - maxWidth.value / 2 + buttonRect.width / 2 + scrollX;

    const overflow = left + maxWidth.value - window.innerWidth + props.marginX + 8;
    if (overflow > 0) {
        left = Math.max(left - overflow, props.marginX);
        arrowStyles.value = { left: `${maxWidth.value / 2 + overflow}px` };
    } else {
        arrowStyles.value = { left: `${maxWidth.value / 2 - (popoverArrow.value?.getBoundingClientRect().width ?? 10 / 2)}` };
    }

    popoverStyles.value = { left: `${left}px`, top: `${clampedTop}px` };
};

const handleClose = () => {
    maxWidth.value = 0;
    maxHeight.value = 0;
    popoverOpen.value = false;
};

watch(
    () => popoverOpen.value,
    (value) => {
        if (!value) return;
        if (!init.value) init.value = true;

        nextTick(adjustPopoverPosition);
    },
);

onMounted(() => {
    window.addEventListener('resize', handleClose);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleClose);
});

onClickOutside(popover, (event) => {
    if (popoverButton.value?.$el?.contains(event.target as Node)) return;
    popoverOpen.value = false;
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
        <Teleport :to="teleportTarget" :disabled="teleportDisabled" v-if="init">
            <Transition
                enter-active-class="ease-out duration-150 will-change-[transform,opacity]"
                enter-from-class="scale-[0.8] opacity-0"
                enter-to-class="scale-100 opacity-100"
                leave-active-class="ease-in duration-100 will-change-[transform,opacity]"
                leave-from-class="scale-100 opacity-100"
                leave-to-class="scale-[0.65] opacity-0"
            >
                <UseFocusTrap
                    v-if="popoverOpen"
                    ref="popover"
                    :options="{ allowOutsideClick: true, preventScroll: true }"
                    :style="popoverStyles"
                    :class="cn('absolute z-50 w-75 max-w-lg p-3', 'ring-1ring-r-button bg-overlay-2 sm:bg-overlay-2-t', 'rounded-md shadow sm:backdrop-blur-xs', popoverClass)"
                    @keydown.esc="popoverOpen = false"
                >
                    <div
                        v-show="showPopoverArrow"
                        ref="popoverArrowRef"
                        :style="arrowStyles"
                        :class="[
                            'absolute left-1/2 inline-block w-5 -translate-x-2 overflow-hidden',
                            popoverDirection === 'bottom' ? 'top-0 -translate-y-2.5' : 'bottom-0 mb-px translate-y-2.5',
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
