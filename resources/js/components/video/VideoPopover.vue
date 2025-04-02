<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref, useTemplateRef, watch, type ComponentPublicInstance } from 'vue';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';

import VideoButton from './VideoButton.vue';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
        buttonClass?: string;
        popoverClass?: string;
        buttonAttributes?: { [key: string]: any };
        verticalOffsetPixels?: number;
        verticalOffset?: number;
        forcePopoverPosition?: 'top' | 'bottom';
        player?: HTMLVideoElement;
        margin?: number;
        popoverArrow?: boolean;
        title?: string;
    }>(),
    {
        title: 'Settings',
        disabled: false,
        margin: 40,
        popoverArrow: false,
    },
);

const popoverOpen = ref(false);
const popoverPosition = ref<'top' | 'bottom'>(props.forcePopoverPosition ?? 'top');
const popoverAdjustment = ref('');
const popoverHeight = ref(0);

const popover = useTemplateRef('popover');
const popoverButton = useTemplateRef<ComponentPublicInstance>('popoverButton');
const popoverArrowRef = useTemplateRef('popoverArrowRef');

const resizeTimeout = ref<null | number>(null);

async function popoverHeightCalculate() {
    if (!popover.value) return;
    popover.value.$el.classList.add('invisible');
    popoverOpen.value = true;

    await nextTick();
    popoverHeight.value = popover.value.$el.offsetHeight;
    popoverOpen.value = false;
    popover.value.$el.classList.remove('invisible');
}

const adjustPopoverPosition = () => {
    if (!popover.value || !popoverButton.value) return;
    let adjustment = 0;

    const popoverRect = popover.value.$el.getBoundingClientRect();
    const viewportWidth = props.player?.width ?? window.innerWidth;

    if (viewportWidth > popoverRect.width + props.margin * 2 && viewportWidth - popoverRect.right - props.margin < 0) {
        adjustment = viewportWidth - popoverRect.right - props.margin;
    } else if (viewportWidth > popoverRect.width + props.margin * 2 && popoverRect.left <= props.margin) {
        adjustment = props.margin * 2;
    } else {
        return;
    }

    popover.value.$el.style = `left: ${adjustment}px;`; // margin-${popoverPosition.value === 'bottom' ? 'top' : 'bottom'}: ${props.verticalOffsetPixels ?? 32}px;
    if (popoverArrowRef.value) {
        popoverArrowRef.value.style.left = `${popoverRect.width - props.margin / 2 + popoverArrowRef.value.offsetWidth / 2}px`;
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
            adjustPopoverPosition();
            document.getElementById('width')?.focus();
        }
    },
);

onMounted(() => {
    setTimeout(function () {
        popoverHeightCalculate();
    }, 100);
});

onUnmounted(() => {
    if (resizeTimeout.value) {
        clearTimeout(resizeTimeout.value);
    }
});
</script>
<template>
    <VideoButton
        ref="popoverButton"
        :class="buttonClass + ` ${popoverOpen ? 'text-purple-600' : ''}`"
        @click="popoverOpen = true"
        v-bind="buttonAttributes"
        :disabled="disabled"
        :title="title"
    >
        <template #icon>
            <slot name="buttonIcon">
                <svg class="w-4 h-4" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M5.5 3C4.67157 3 4 3.67157 4 4.5C4 5.32843 4.67157 6 5.5 6C6.32843 6 7 5.32843 7 4.5C7 3.67157 6.32843 3 5.5 3ZM3 5C3.01671 5 3.03323 4.99918 3.04952 4.99758C3.28022 6.1399 4.28967 7 5.5 7C6.71033 7 7.71978 6.1399 7.95048 4.99758C7.96677 4.99918 7.98329 5 8 5H13.5C13.7761 5 14 4.77614 14 4.5C14 4.22386 13.7761 4 13.5 4H8C7.98329 4 7.96677 4.00082 7.95048 4.00242C7.71978 2.86009 6.71033 2 5.5 2C4.28967 2 3.28022 2.86009 3.04952 4.00242C3.03323 4.00082 3.01671 4 3 4H1.5C1.22386 4 1 4.22386 1 4.5C1 4.77614 1.22386 5 1.5 5H3ZM11.9505 10.9976C11.7198 12.1399 10.7103 13 9.5 13C8.28967 13 7.28022 12.1399 7.04952 10.9976C7.03323 10.9992 7.01671 11 7 11H1.5C1.22386 11 1 10.7761 1 10.5C1 10.2239 1.22386 10 1.5 10H7C7.01671 10 7.03323 10.0008 7.04952 10.0024C7.28022 8.8601 8.28967 8 9.5 8C10.7103 8 11.7198 8.8601 11.9505 10.0024C11.9668 10.0008 11.9833 10 12 10H13.5C13.7761 10 14 10.2239 14 10.5C14 10.7761 13.7761 11 13.5 11H12C11.9833 11 11.9668 10.9992 11.9505 10.9976ZM8 10.5C8 9.67157 8.67157 9 9.5 9C10.3284 9 11 9.67157 11 10.5C11 11.3284 10.3284 12 9.5 12C8.67157 12 8 11.3284 8 10.5Z"
                        fill="currentColor"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                    ></path>
                </svg>
            </slot>
        </template>
    </VideoButton>
    <Transition
        enter-active-class="ease-out duration-150"
        enter-from-class="scale-[0.8] opacity-60"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="ease-in-out duration-100"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-[0.1] opacity-50"
    >
        <!-- -translate-x-1/2 ${popoverAdjustment ? '' : 'left-1/2'} -->
        <UseFocusTrap
            v-if="popoverOpen"
            :class="`z-10 absolute w-[300px] max-w-lg ${popoverClass} ${popoverPosition === 'bottom' ? `top-8 ${verticalOffset ? `sm:top-${verticalOffset}` : 'sm:top-12'}` : `${verticalOffset ? `bottom-${verticalOffset}` : `bottom-12`}`} right-2 overflow-clip`"
            ref="popover"
            :options="{ allowOutsideClick: true }"
        >
            <OnClickOutside
                @trigger.stop="
                    (e: any) => {
                        popoverOpen = false;
                    }
                "
                @keydown.esc="popoverOpen = false"
                tabindex="-1"
                v-show="popoverOpen"
                v-cloak
                :class="`w-full p-1 bg-neutral-800/90 backdrop-blur-sm border border-neutral-700/10 rounded-md shadow-sm `"
            >
                <div
                    v-show="popoverArrow && popoverPosition == 'bottom'"
                    ref="popoverArrowRef"
                    :class="`absolute inline-block w-5 overflow-hidden -translate-x-2 left-1/2 ${popoverPosition === 'bottom' ? 'top-0 mt-px -translate-y-2.5' : 'bottom-0 mb-px translate-y-2.5'}`"
                >
                    <div
                        :class="`w-2.5 h-2.5 transform bg-neutral-800/90 border-l border-neutral-700/10 rounded-sm ${popoverPosition === 'bottom' ? 'origin-bottom-left rotate-45 border-t' : 'origin-top-left -rotate-45 border-b'} `"
                    ></div>
                </div>
                <slot name="content">
                    <div class="grid gap-4">
                        <div class="space-y-2">
                            {{ popoverAdjustment }}
                            <h4 class="font-medium leading-none">Dimensions</h4>
                            <p class="text-sm text-muted-foreground">Set the dimensions for the layer.</p>
                        </div>
                        <div class="grid gap-2">
                            <div class="grid items-center grid-cols-3 gap-4">
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="width">Width</label
                                ><input
                                    class="flex w-full h-8 col-span-2 px-3 py-2 text-sm bg-transparent border rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    id="width"
                                    value="100%"
                                />
                            </div>
                            <div class="grid items-center grid-cols-3 gap-4">
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="maxWidth">Max. width</label
                                ><input
                                    class="flex w-full h-8 col-span-2 px-3 py-2 text-sm bg-transparent border rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    id="maxWidth"
                                    value="300px"
                                />
                            </div>
                            <div class="grid items-center grid-cols-3 gap-4">
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="height">Height</label
                                ><input
                                    class="flex w-full h-8 col-span-2 px-3 py-2 text-sm bg-transparent border rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    id="height"
                                    value="25px"
                                />
                            </div>
                            <div class="grid items-center grid-cols-3 gap-4">
                                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="maxHeight">Max. height</label
                                ><input
                                    class="flex w-full h-8 col-span-2 px-3 py-2 text-sm bg-transparent border rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
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
</template>
