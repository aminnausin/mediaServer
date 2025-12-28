<script setup lang="ts">
import type { Component, ComponentPublicInstance } from 'vue';

import { computed, nextTick, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';
import { CedarOptions } from '../icons';
import { ButtonText } from '../button';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
        buttonClass?: string;
        popoverClass?: string;
        buttonComponent?: Component;
        buttonAttributes?: { [key: string]: any };
        verticalOffsetPixels?: number;
        hideDefaultIcon?: boolean;
        forcePopoverPosition?: 'top' | 'bottom';
    }>(),
    {
        disabled: false,
        buttonComponent: ButtonText,
        hideDefaultIcon: false,
    },
);

const popoverOpen = ref(false);
const popoverArrow = ref(true);
const popoverPosition = ref<'top' | 'bottom'>('bottom');
const popoverAdjustment = ref('');
const popoverHeight = ref(0);
const popoverOffset = ref(8);

const popover = useTemplateRef('popover');
const popoverButton = useTemplateRef<ComponentPublicInstance>('popoverButton');
const popoverArrowRef = useTemplateRef('popoverArrowRef');

const resizeTimeout = ref<null | number>(null);

const mergedButtonAttributes = computed(() => ({
    title: 'Open Menu',
    ...props.buttonAttributes,
}));

async function popoverHeightCalculate() {
    if (!popover.value) return;
    popover.value.$el.classList.add('invisible');
    popoverOpen.value = true;

    await nextTick();
    popoverHeight.value = popover.value.$el.offsetHeight;
    popoverOpen.value = false;
    popover.value.$el.classList.remove('invisible');
    popoverPositionCalculate();
}

function popoverPositionCalculate() {
    if (!popoverButton.value) return;

    popoverPosition.value =
        (props.forcePopoverPosition ??
        window.innerHeight < popoverButton.value.$el.getBoundingClientRect().top + popoverButton.value.$el.offsetHeight + popoverOffset.value + popoverHeight.value)
            ? 'top'
            : 'bottom';
}

const adjustPopoverPosition = () => {
    if (!popover.value || !popoverButton.value) return;
    const margin = 40;
    let adjustment = 0;

    const popoverRect = popover.value.$el.getBoundingClientRect();
    const viewportWidth = window.innerWidth;

    if (viewportWidth > popoverRect.width + margin * 2 && viewportWidth - popoverRect.right - margin < 0) {
        adjustment = viewportWidth - popoverRect.right - margin;
    } else if (viewportWidth > popoverRect.width + margin * 2 && popoverRect.left <= margin) {
        adjustment = margin * 2;
    } else {
        return;
    }

    popover.value.$el.style = `left: ${adjustment}px; margin-${popoverPosition.value === 'bottom' ? 'top' : 'bottom'}: ${props.verticalOffsetPixels ?? 32}px;`;
    if (popoverArrowRef.value) {
        popoverArrowRef.value.style.left = `${popoverRect.width - margin / 2 + popoverArrowRef.value.offsetWidth / 2}px`;
    }
};

const handleClose = () => {
    popoverOpen.value = false;
};

defineExpose({ handleClose });

watch(
    () => popoverOpen.value,
    async (value) => {
        if (value) {
            await nextTick();
            popoverPositionCalculate();
            adjustPopoverPosition();
            document.getElementById('width')?.focus();
        }
    },
);

onMounted(() => {
    window.addEventListener('resize', popoverPositionCalculate);

    window.setTimeout(function () {
        popoverHeightCalculate();
    }, 100);
});

onUnmounted(() => {
    window.removeEventListener('resize', popoverPositionCalculate);
    if (resizeTimeout.value) {
        clearTimeout(resizeTimeout.value);
    }
});
</script>
<template>
    <div class="relative flex">
        <component :is="buttonComponent" ref="popoverButton" :class="buttonClass" @click="popoverOpen = true" v-bind="mergedButtonAttributes" :disabled="disabled">
            <template #text>
                <slot name="buttonText"> </slot>
            </template>
            <template #icon>
                <slot name="buttonIcon">
                    <CedarOptions class="size-4" v-if="!hideDefaultIcon" />
                </slot>
            </template>
        </component>
        <Transition
            enter-active-class="ease-out duration-150"
            enter-from-class="scale-[0.8] opacity-60"
            enter-to-class="scale-100 opacity-100"
            leave-active-class="ease-in-out duration-100"
            leave-from-class="scale-100 opacity-100"
            leave-to-class="scale-[0.1] opacity-50"
        >
            <UseFocusTrap
                v-if="popoverOpen"
                :class="[
                    'ring-r-button bg-overlay-2-t absolute z-50 w-[300px] max-w-lg rounded-md p-4 shadow-xs ring-1 backdrop-blur-xs',
                    popoverClass,
                    '-translate-x-1/2',
                    { 'left-1/2': !popoverAdjustment },
                    popoverPosition === 'bottom' ? 'top-0' : 'bottom-0',
                ]"
                ref="popover"
                :options="{ allowOutsideClick: true }"
            >
                <OnClickOutside
                    @trigger.stop="
                        (_: any) => {
                            popoverOpen = false;
                        }
                    "
                    @keydown.esc="popoverOpen = false"
                    tabindex="-1"
                    v-show="popoverOpen"
                    v-cloak
                    :class="`w-full`"
                >
                    <div
                        v-show="popoverArrow && popoverPosition == 'bottom'"
                        ref="popoverArrowRef"
                        :class="[
                            'absolute left-1/2 inline-block w-5 -translate-x-2 overflow-hidden',
                            popoverPosition === 'bottom' ? 'top-0 -translate-y-2.5' : 'bottom-0 mb-px translate-y-2.5',
                        ]"
                    >
                        <div
                            :class="[
                                'border-r-button bg-overlay-2 h-2.5 w-2.5 transform rounded-xs border-l',
                                popoverPosition === 'bottom' ? 'origin-bottom-left rotate-45 border-t' : 'origin-top-left -rotate-45 border-b',
                            ]"
                        ></div>
                    </div>
                    <slot name="content">
                        <!-- Example -->
                        <div class="grid gap-4">
                            <div class="space-y-2">
                                {{ popoverAdjustment }}
                                <h4 class="leading-none font-medium">Dimensions</h4>
                                <p class="text-muted-foreground text-sm">Set the dimensions for the layer.</p>
                            </div>
                            <div class="grid gap-2">
                                <div class="grid grid-cols-3 items-center gap-4">
                                    <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="width">Width</label
                                    ><input
                                        class="border-input ring-offset-background placeholder:text-muted-foreground col-span-2 flex h-8 w-full rounded-md border bg-transparent px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
                                        id="width"
                                        value="100%"
                                    />
                                </div>
                                <div class="grid grid-cols-3 items-center gap-4">
                                    <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="maxWidth">Max. width</label
                                    ><input
                                        class="border-input ring-offset-background placeholder:text-muted-foreground col-span-2 flex h-8 w-full rounded-md border bg-transparent px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
                                        id="maxWidth"
                                        value="300px"
                                    />
                                </div>
                                <div class="grid grid-cols-3 items-center gap-4">
                                    <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="height">Height</label
                                    ><input
                                        class="border-input ring-offset-background placeholder:text-muted-foreground col-span-2 flex h-8 w-full rounded-md border bg-transparent px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
                                        id="height"
                                        value="25px"
                                    />
                                </div>
                                <div class="grid grid-cols-3 items-center gap-4">
                                    <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="maxHeight">Max. height</label
                                    ><input
                                        class="border-input ring-offset-background placeholder:text-muted-foreground col-span-2 flex h-8 w-full rounded-md border bg-transparent px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
                                        id="maxHeight"
                                        value="none"
                                    />
                                </div>
                            </div>
                        </div>
                    </slot>
                </OnClickOutside>
            </UseFocusTrap>
        </Transition>
    </div>
</template>
