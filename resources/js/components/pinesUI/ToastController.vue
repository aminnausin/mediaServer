<script setup lang="ts">
// This is so bad lol pls fix
import type { Message, ToastControllerProps, ToastLayout, ToastPostion, ToastToDismiss } from '@/types/pinesTypes';

import { DEFAULT_GAP, MOBILE_VIEWPORT_OFFSET, SCALE_STEP, TOAST_WIDTH, VIEWPORT_OFFSET, VISIBLE_TOASTS_AMOUNT, Y_OFFSET_STEP, Z_STEP } from '@/service/toaster/constants';
import { nextTick, onMounted, ref, watch, watchEffect } from 'vue';
import { ToastState } from '@/service/toaster/toastService';

import ToastNotification from '@/components/pinesUI/ToastNotification.vue';

const props = withDefaults(defineProps<ToastControllerProps>(), {
    layout: 'default',
    position: 'bottom-right',
    defaultLife: 3000,
    maxVisibleToasts: VISIBLE_TOASTS_AMOUNT,
    viewportOffset: VIEWPORT_OFFSET,
    mobileViewportOffset: MOBILE_VIEWPORT_OFFSET,
    paddingBetweenToasts: DEFAULT_GAP,
});

const container = ref<HTMLElement>();

const messages = ref<Message[]>([]);
const position = ref<ToastPostion>(props.position);
const layout = ref<ToastLayout>(props.layout);

const toastsHovered = ref(false);
const expanded = ref(props.layout === 'expanded');
const paddingBetweenToasts = ref(props.paddingBetweenToasts);
const heightRecalculateTimeout = ref<null | number>(null);
const burnTimeout = ref<null | number>(null);

function deleteToastWithId(id: string) {
    messages.value = messages.value.filter((msg) => msg.id !== id);
    ToastState.dismiss(id);
    stackToasts('DELETE');
}

function stackToasts(_?: any) {
    positionToasts(_);
    calculateHeightOfToastsContainer();

    if (heightRecalculateTimeout.value) clearTimeout(heightRecalculateTimeout.value);

    // This calculates container height after the toasts are positioned
    if (!expanded.value) return;

    heightRecalculateTimeout.value = window.setTimeout(() => {
        // positionToasts(_);
        calculateHeightOfToastsContainer();
    }, 100);
}

function positionToasts(_?: any) {
    if (messages.value.length == 0) {
        return;
    }

    try {
        const bottomFlag = position.value.includes('bottom');
        const toastElements = [];

        let totalHeight = 0;

        let scaleBuffer = 1 + SCALE_STEP;
        let yBuffer = -Y_OFFSET_STEP;
        let zBuffer = 200 + Z_STEP;

        for (let i = 0; i < messages.value.length; i++) {
            const msg = messages.value[i];
            const toast = document.getElementById(`${messages.value[i].id}`);

            if (!toast) return;

            zBuffer -= Z_STEP;
            scaleBuffer -= SCALE_STEP;
            yBuffer += Y_OFFSET_STEP;

            msg.scale = scaleBuffer;
            msg.offsetY = yBuffer;
            msg.zIndex = zBuffer;

            toast.style.zIndex = `${zBuffer}`;
            toastElements.push(toast);

            if (expanded.value) {
                totalHeight = totalHeight + (totalHeight ? paddingBetweenToasts.value : 0);

                if (bottomFlag) {
                    toast.style.top = 'auto';
                    toast.style.bottom = totalHeight + 'px';
                } else toast.style.top = totalHeight + 'px';

                totalHeight += toast.offsetHeight;

                // toast.style.scale = `1`;
                msg.scale = 1;

                msg.offsetY = 0;

                // toast.style.setProperty('--offset-y', '0px');
                continue;
            }

            msg.scale = scaleBuffer;
            // toast.style.scale = `${scaleBuffer}`;

            if (bottomFlag) {
                msg.offsetY *= -1;
                // toast.style.setProperty('--offset-y', `-${yBuffer}px`);
            } else {
                alignBottom(toastElements[0], toast);
                // toast.style.setProperty('--offset-y', `${yBuffer}px`);
            }

            Object.assign(messages.value[i], msg);
        }

        handleToastsOverflow(toastElements);
    } catch (error) {
        console.log(error);
    }
}

function handleToastsOverflow(toastElements: HTMLElement[]) {
    if (messages.value.length <= props.maxVisibleToasts) {
        return;
    }

    try {
        const burnToast = document.getElementById(`${messages.value[props.maxVisibleToasts]?.id}`);

        if (!burnToast) {
            return;
        }

        // burnToast.firstElementChild?.classList.remove('opacity-100');
        burnToast.firstElementChild?.classList.add('opacity-0');

        // if (burnTimeout.value) {
        // clearTimeout(burnTimeout.value);
        // }

        // Burn 🔥 (remove) last toast
        burnTimeout.value = window.setTimeout(function () {
            messages.value.pop();
        }, 300);

        if (position.value.includes('bottom')) {
            toastElements[1].style.top = 'auto';
        }
    } catch (error) {
        console.log(error);
    }
}

function calculateHeightOfToastsContainer() {
    if (!container.value) return;

    if (messages.value.length == 0) {
        container.value.style.height = '0px';
        return;
    }

    const lastToast = messages.value[messages.value.length - 1];
    const lastToastRectangle = document.getElementById(`${lastToast?.id}`)?.getBoundingClientRect();

    const firstToast = messages.value[0];
    const firstToastRectangle = document.getElementById(`${firstToast?.id}`)?.getBoundingClientRect();

    if (!firstToastRectangle || !lastToastRectangle) return;

    if (!toastsHovered.value) {
        container.value.style.height = firstToastRectangle.height + 'px';
        return;
    }

    if (position.value.includes('bottom')) {
        container.value.style.height = firstToastRectangle.top + firstToastRectangle.height - lastToastRectangle.top + 'px';
        return;
    }

    container.value.style.height = lastToastRectangle.top + lastToastRectangle.height - firstToastRectangle.top + 'px';
}

function alignBottom(element1: HTMLElement, element2: HTMLElement) {
    // Get the top position and height of the first element
    const top1 = element1.offsetTop;
    const height1 = element1.offsetHeight;

    // Get the height of the second element
    const height2 = element2.offsetHeight;

    // Calculate the top position for the second element
    const top2 = top1 + (height1 - height2);

    // Apply the calculated top position to the second element
    element2.style.top = top2 + 'px';
}

function resetBottom() {
    for (const message of messages.value) {
        const toastElement = document.getElementById(`${message?.id}`);
        if (toastElement) {
            toastElement.style.bottom = '0px';
        }
    }
}

function resetTop() {
    for (const message of messages.value) {
        const toastElement = document.getElementById(`${message?.id}`);
        if (toastElement) {
            toastElement.style.top = '0px';
        }
    }
}

onMounted(() => {
    stackToasts('MOUNT');
});

watch(
    () => toastsHovered.value,
    (value) => {
        if (layout.value !== 'default') return;

        if (position.value.includes('bottom')) {
            resetBottom();
        } else {
            resetTop();
        }

        if (value) {
            // calculate the new positions
            expanded.value = true;
            stackToasts('HOVER');
            return;
        }

        expanded.value = false;
        // setTimeout(function () {
        // This can get called a bunch of times but since the timer is so short, it will probably be called before the event happens again
        stackToasts('HOVER CLOSE');
        // }, 10);
        // window.setTimeout(function () {
        //     stackToasts('POST HOVER CLOSE');
        // }, 10);
    },
);

watch(
    () => messages.value,
    () => {
        // Ensure expanded is always false when no toasts are present / only one left
        if (messages.value.length <= 1) {
            expanded.value = false;
        }
    },
);

watchEffect((onInvalidate) => {
    const unsubscribe = ToastState.subscribe((newToast) => {
        // ? toast is deleted locally already why is there an extra step
        if ((newToast as ToastToDismiss).dismiss) {
            // messages.value = messages.value.map((t) => (t.id === toast.id ? { ...t, delete: true } : t));
            return;
        }

        nextTick(() => {
            const indexOfExistingToast = messages.value.findIndex((t) => t.id === newToast.id);

            // Update the toast if it already exists
            if (indexOfExistingToast !== -1) {
                messages.value = [
                    ...messages.value.slice(0, indexOfExistingToast),
                    { ...messages.value[indexOfExistingToast], ...newToast },
                    ...messages.value.slice(indexOfExistingToast + 1),
                ];

                return;
            }
            const toast = newToast as Message;

            if (toast.position) position.value = toast.position;

            messages.value.unshift({
                ...toast,
                id: toast.id,
                type: toast.type ?? 'default',
                position: toast.position ?? position.value,
                life: toast.life ?? props.defaultLife,
                title: toast.title,
            });
        });
    });

    onInvalidate(unsubscribe);
});
</script>

<template>
    <teleport to="body">
        <ol
            v-cloak
            ref="container"
            :class="[
                `fixed w-full group z-[500] [&>*]:px-4 [&>*]:px-[${mobileViewportOffset ?? viewportOffset}] [&>*]:sm:px-6 [&>*]:sm:px-[${viewportOffset}] my-4 sm:my-6 my-[${mobileViewportOffset ?? viewportOffset}] sm:my-[${viewportOffset}]`,
                `${TOAST_WIDTH ? `sm:w-[${TOAST_WIDTH}px]` : 'sm:max-w-sm'}`,
                `${position == 'top-right' ? 'right-0 top-0' : ''}`,
                `${position == 'top-left' ? 'left-0 top-0' : ''}`,
                `${position == 'top-center' ? 'left-1/2 -translate-x-1/2 top-0' : ''}`,
                `${position == 'bottom-right' ? 'right-0 bottom-0' : ''}`,
                `${position == 'bottom-left' ? 'left-0 bottom-0' : ''}`,
                `${position == 'bottom-center' ? 'left-1/2 -translate-x-1/2 bottom-0' : ''}`,
            ]"
            @mouseenter="toastsHovered = true"
            @mouseleave="toastsHovered = false"
        >
            <ToastNotification
                v-for="(toast, index) in messages"
                v-bind="toast"
                :key="toast.id"
                :stack="
                    () => {
                        stackToasts('TOAST');
                    }
                "
                :toastCount="messages.length"
                :position="position"
                :expanded="expanded"
                :index="index"
                @close="deleteToastWithId"
            />
        </ol>
    </teleport>
</template>
