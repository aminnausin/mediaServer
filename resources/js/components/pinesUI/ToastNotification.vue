<script setup lang="ts">
import type { SwipeDirection, ToastProps } from '@/types/pinesTypes';

import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { SWIPE_THRESHOLD, TOAST_LIFE } from '@/service/toaster/constants';
import { useSwipeHandler } from '@/composables/useSwipeHandler';
import { useToastTimer } from '@/composables/useToastTimer';

const emit = defineEmits<(e: 'close', id: string) => void>(); // removeToast

const props = withDefaults(defineProps<ToastProps>(), {
    type: 'default',
    position: 'bottom-center',
    life: TOAST_LIFE,
    title: 'Title',
    html: '',
    style: '',
    swipeDirections: () => [],
});

const leaveDirection = ref('translate-y-0');
const swipeDirections = computed(() => {
    if (props.swipeDirections.length > 0) return props.swipeDirections;

    const [y, x] = props.position.split('-');
    const directions: Array<SwipeDirection> = [];

    if (isSwipeDirection(y)) {
        directions.push(y);
    }

    if (isSwipeDirection(x)) {
        directions.push(x);
    }

    if (x === 'center') {
        directions.push('left', 'right');
    }

    return directions;
});

const isBottom = computed(() => {
    return props.position.includes('bottom');
});

const closeTimeout = ref<null | number>(null);
const stackTimeout = ref<null | number>(null);

const toastHovered = ref(false);
const isMounted = ref(false);

const { offset, isSwiping, onPointerDown, onPointerMove, onPointerUp } = useSwipeHandler({ directions: swipeDirections, threshold: SWIPE_THRESHOLD, onSwipeOut: onClose });

const { cancel: cancelToastTimer } = useToastTimer({
    duration: props.life || TOAST_LIFE,
    isPaused: () => props.expanded || props.type === 'loading' || !props.life || props.life === Infinity || toastHovered.value,
    onTimeout: onClose,
});

function getLeaveDirection() {
    if (isSwiping.value) {
        return offset.value.x > 0 ? 'translate-x-full' : '-translate-x-full';
    }

    if (props.toastCount === 1) {
        return isBottom.value ? 'translate-y-full' : '-translate-y-full';
    }

    return 'translate-y-0';
}

function onClose() {
    if (closeTimeout.value) return;

    leaveDirection.value = getLeaveDirection();
    isMounted.value = false;
    cancelToastTimer();
    closeTimeout.value = window.setTimeout(() => {
        emit('close', props.id);
    }, 350);
}

function clearCloseTimeout() {
    if (stackTimeout.value) {
        clearTimeout(stackTimeout.value);
        stackTimeout.value = null;
    }
    if (closeTimeout.value) {
        clearTimeout(closeTimeout.value);
        closeTimeout.value = null;
    }
}

function isSwipeDirection(val: string): val is SwipeDirection {
    return ['top', 'right', 'bottom', 'left'].includes(val);
}

onMounted(() => {
    isMounted.value = true;

    if (stackTimeout.value) clearTimeout(stackTimeout.value);

    stackTimeout.value = window.setTimeout(() => {
        props.stack();
    });
});

onBeforeUnmount(() => {
    clearCloseTimeout();
});
</script>

<template>
    <li
        ref="toastEl"
        :id="props.id"
        :class="[`toast w-full absolute duration-300 transition-all ease-out ${!description ? 'toast-no-description' : ''}`, style]"
        :style="{
            '--offset-x': `${offset.x}px`,
            '--offset-y': `${offsetY}px`,
            '--scale': scale,
            '--z-index': zIndex,
            '--position-top': `${expanded ? (isBottom ? 'auto' : positionY) : 'inherit'}`,
            '--position-bottom': `${isBottom && expanded ? positionY : 'inherit'}`,
        }"
        :data-isSwiping="isSwiping"
        @mouseover="toastHovered = true"
        @mouseout="toastHovered = false"
        @dragend="onPointerUp"
        @pointerdown="onPointerDown"
        @pointermove="onPointerMove"
        @pointerup="onPointerUp"
        role="alert"
    >
        <Transition
            enter-active-class=""
            :enter-from-class="`opacity-0 ${isBottom ? 'translate-y-full' : '-translate-y-full'}`"
            :enter-to-class="`opacity-100 translate-y-0`"
            leave-active-class=""
            :leave-from-class="`opacity-100 translate-y-0`"
            :leave-to-class="`opacity-0 ${leaveDirection}`"
        >
            <span
                :class="[
                    { 'p-4': !props.html, 'p-0': props.html },
                    'flex flex-col items-start backdrop-blur-lg rounded-md ',
                    'group relative select-text',
                    'transition-all duration-300 ease-out',
                    'bg-white dark:bg-primary-dark-700/70 text-gray-800 dark:text-neutral-100 shadow-[0_5px_15px_-3px_rgb(0_0_0_/_0.08)]',
                    'ring-inset ring-1 ring-gray-100 dark:ring-neutral-800/50',
                    '!outline-none focus:ring-gray-400 dark:focus:ring-indigo-500 focus:ring-2',
                ]"
                v-show="isMounted"
            >
                <div
                    v-if="!props.html"
                    class="flex items-center"
                    :class="{
                        'text-green-500': props.type === 'success',
                        'text-blue-500': props.type === 'info',
                        'text-orange-400': props.type === 'warning',
                        'text-rose-500': props.type === 'danger',
                        'dark:text-neutral-100 text-gray-800': props.type === 'default',
                    }"
                >
                    <svg v-show="props.type === 'success'" class="w-[18px] h-[18px] mr-1.5 -ml-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM16.7744 9.63269C17.1238 9.20501 17.0604 8.57503 16.6327 8.22559C16.2051 7.87615 15.5751 7.93957 15.2256 8.36725L10.6321 13.9892L8.65936 12.2524C8.24484 11.8874 7.61295 11.9276 7.248 12.3421C6.88304 12.7566 6.92322 13.3885 7.33774 13.7535L9.31046 15.4903C10.1612 16.2393 11.4637 16.1324 12.1808 15.2547L16.7744 9.63269Z"
                            fill="currentColor"
                        ></path>
                    </svg>
                    <svg v-show="props.type === 'info'" class="w-[18px] h-[18px] mr-1.5 -ml-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12 9C12.5523 9 13 8.55228 13 8C13 7.44772 12.5523 7 12 7C11.4477 7 11 7.44772 11 8C11 8.55228 11.4477 9 12 9ZM13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12V16C11 16.5523 11.4477 17 12 17C12.5523 17 13 16.5523 13 16V12Z"
                            fill="currentColor"
                        ></path>
                    </svg>
                    <svg v-show="props.type === 'warning'" class="w-[18px] h-[18px] mr-1.5 -ml-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M9.44829 4.46472C10.5836 2.51208 13.4105 2.51168 14.5464 4.46401L21.5988 16.5855C22.7423 18.5509 21.3145 21 19.05 21L4.94967 21C2.68547 21 1.25762 18.5516 2.4004 16.5862L9.44829 4.46472ZM11.9995 8C12.5518 8 12.9995 8.44772 12.9995 9V13C12.9995 13.5523 12.5518 14 11.9995 14C11.4473 14 10.9995 13.5523 10.9995 13V9C10.9995 8.44772 11.4473 8 11.9995 8ZM12.0009 15.99C11.4486 15.9892 11.0003 16.4363 10.9995 16.9886L10.9995 16.9986C10.9987 17.5509 11.4458 17.9992 11.9981 18C12.5504 18.0008 12.9987 17.5537 12.9995 17.0014L12.9995 16.9914C13.0003 16.4391 12.5532 15.9908 12.0009 15.99Z"
                            fill="currentColor"
                        ></path>
                    </svg>
                    <svg v-show="props.type === 'danger'" class="w-[18px] h-[18px] mr-1.5 -ml-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9996 7C12.5519 7 12.9996 7.44772 12.9996 8V12C12.9996 12.5523 12.5519 13 11.9996 13C11.4474 13 10.9996 12.5523 10.9996 12V8C10.9996 7.44772 11.4474 7 11.9996 7ZM12.001 14.99C11.4488 14.9892 11.0004 15.4363 10.9997 15.9886L10.9996 15.9986C10.9989 16.5509 11.446 16.9992 11.9982 17C12.5505 17.0008 12.9989 16.5537 12.9996 16.0014L12.9996 15.9914C13.0004 15.4391 12.5533 14.9908 12.001 14.99Z"
                            fill="currentColor"
                        ></path>
                    </svg>
                    <p class="text-[13px] font-medium leading-none" :title="props.title">{{ props.title }}</p>
                </div>
                <p
                    v-show="props.description"
                    :class="{ 'pl-5': props.type !== 'default' }"
                    class="mt-1.5 text-xs leading-tight opacity-70 w-full whitespace-pre-wrap break-words overflow-y-auto scrollbar-minimal max-h-32 min-h-3 pe-2"
                >
                    {{ description }}
                </p>
                <template v-if="!props.html">
                    <span
                        @click="onClose"
                        class="absolute right-0 p-1.5 mr-2.5 text-gray-400 dark:text-rose-700 duration-100 ease-in-out rounded-full opacity-0 cursor-pointer hover:bg-gray-50 dark:bg-gray-800/50 hover:text-gray-500 dark:hover:text-rose-600"
                        :class="{
                            'top-1/2 -translate-y-1/2': !props.description && !props.html,
                            'top-0 mt-2.5': props.description || props.html,
                            'opacity-100': toastHovered,
                            'opacity-0': !toastHovered,
                        }"
                    >
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                    </span>
                </template>
            </span>
        </Transition>
    </li>
</template>
<style lang="css" scoped>
.toast {
    transform: translateY(var(--offset-y, 0px)) translateX(var(--offset-x, 0px)) scale(var(--scale, 1));
    z-index: var(--z-index, 200);
    top: var(--position-top, inherit);
    bottom: var(--position-bottom, inherit);
}

@media (prefers-reduced-motion: reduce) {
    .toast {
        transition: none !important;
    }
}

[data-isSwiping='true'] {
    transition-property: none !important;
}
</style>
