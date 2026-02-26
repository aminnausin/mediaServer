<script setup lang="ts">
import type { SwipeDirection, ToastProps } from '@aminnausin/cedar-ui';

import { useToastTimer, useSwipeHandler, SWIPE_THRESHOLD, TOAST_LIFE, VISIBLE_TOASTS_AMOUNT, cn } from '@aminnausin/cedar-ui';
import { CedarDanger, CedarInfo, CedarSuccess, CedarWarning, SvgSpinners90RingWithBg } from '@/components/cedar-ui/icons';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { ButtonCorner } from '../button';

const emit = defineEmits<(e: 'close', id: string) => void>(); // removeToast

const props = withDefaults(defineProps<ToastProps>(), {
    type: 'default',
    position: 'bottom-center',
    life: TOAST_LIFE,
    maxVisibleToasts: VISIBLE_TOASTS_AMOUNT,
    title: 'Title',
    html: '',
    style: '', // can be used since toast is instantiated with a function
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

const closeTimeout = ref<NodeJS.Timeout | null>(null);
const stackTimeout = ref<NodeJS.Timeout | null>(null);

const toastHovered = ref(false);
const isMounted = ref(false);

const { offset, isSwiping, onPointerDown, onPointerMove, onPointerUp } = useSwipeHandler({
    directions: swipeDirections,
    swipeThreshold: { px: SWIPE_THRESHOLD },
    onSwipeOut: onClose,
});

const { cancel: cancelToastTimer } = useToastTimer({
    duration: () => props.life || TOAST_LIFE,
    isPaused: () => props.expanded || !props.life || props.life === Infinity || toastHovered.value,
    onTimeout: onClose,
});

function getLeaveDirection() {
    const yDir = isBottom.value ? 'translate-y-full' : '-translate-y-full';

    if (isSwiping.value) {
        const xDir = offset.value.x > 0 ? 'translate-x-full' : '-translate-x-full';
        return Math.abs(offset.value.y) > 0 ? yDir : xDir;
    }

    if (props.toastCount === 1) {
        return yDir;
    }

    return 'translate-y-0';
}

function onClose() {
    if (closeTimeout.value) return;

    leaveDirection.value = getLeaveDirection();
    isMounted.value = false;
    cancelToastTimer();
    closeTimeout.value = globalThis.setTimeout(() => {
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

    stackTimeout.value = globalThis.setTimeout(() => {
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
        :class="[`toast absolute w-full transition-all duration-300 ease-out`, { 'pointer-events-none opacity-0': index >= maxVisibleToasts }, style]"
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
            :enter-from-class="`opacity-0 ${isBottom ? 'translate-y-full' : '-translate-y-full'}`"
            :enter-to-class="`opacity-100 translate-y-0`"
            :leave-from-class="`opacity-100 translate-y-0`"
            :leave-to-class="`opacity-0 ${leaveDirection}`"
        >
            <div
                :class="[
                    { 'p-3 py-4': !html, 'p-0': html },
                    'flex items-start gap-1 rounded-md backdrop-blur-lg',
                    'group relative select-text',
                    'transition-all duration-300 ease-out',
                    'bg-overlay-t text-foreground-0 shadow-[0_5px_15px_-3px_rgb(0_0_0/0.08)]',
                    'ring-r-inverse ring-1 ring-inset',
                ]"
                v-show="isMounted"
            >
                <div
                    v-if="!html"
                    :class="[
                        {
                            'text-success': type === 'success',
                            'text-info': type === 'info',
                            'text-warning': type === 'warning',
                            'text-danger-1': type === 'danger',
                            'text-foreground-0': type === 'default' || type === 'promise',
                        },
                    ]"
                >
                    <CedarSuccess v-if="type === 'success'" class="toast-icon" />
                    <CedarInfo v-if="type === 'info'" class="toast-icon" />
                    <CedarWarning v-if="type === 'warning'" class="toast-icon" />
                    <CedarDanger v-if="type === 'danger'" class="toast-icon" />
                    <SvgSpinners90RingWithBg v-if="type === 'promise'" class="toast-icon" />
                </div>
                <div class="space-y-1.5">
                    <h6
                        :class="[
                            'line-clamp-2 overflow-clip pe-6 text-[13px] leading-none font-medium',
                            {
                                'text-success': type === 'success',
                                'text-info': type === 'info',
                                'text-warning': type === 'warning',
                                'text-danger-1': type === 'danger',
                                'text-foreground-0': type === 'default' || type === 'promise',
                            },
                        ]"
                        :title="title"
                    >
                        {{ title }}
                    </h6>
                    <p v-if="description" :class="['scrollbar-minimal max-h-32 w-full overflow-y-auto pe-2 text-xs leading-4 break-all whitespace-pre-wrap opacity-70']">
                        {{ description }}
                    </p>
                </div>

                <template v-if="!html">
                    <ButtonCorner
                        @click="onClose"
                        class="text-foreground-2 hover:text-foreground-1 dark:text-danger-3 hover:bg-surface-1 dark:bg-surface-1/50 dark:hover:bg-surface-1 dark:hover:text-danger-1 absolute right-0 mr-2.5 size-6 p-1.5"
                        label="Close Toast"
                        :class="
                            cn('cursor-pointer rounded-full opacity-0', {
                                'top-1/2 -translate-y-1/2': !description && !html,
                                'top-0 mt-2.5': description || html,
                                'opacity-100': toastHovered,
                                'opacity-0': !toastHovered,
                            })
                        "
                        :use-default-style="false"
                    />
                </template>
            </div>
        </Transition>
    </li>
</template>
<style lang="css" scoped>
@reference '@css/app.css';

.toast-icon {
    @apply -mt-0.5 size-4 shrink-0;
}

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
