<script setup lang="ts">
// This is so bad lol pls fix
import { type Message, type ToastControllerProps, type ToastLayout, type ToastPostion, type ToastToDismiss } from '@/types/pinesTypes';

import { nextTick, onMounted, ref, watch, watchEffect } from 'vue';
import { ToastState } from '@/service/toaster/toastService';

import ToastNotification from '@/components/pinesUI/ToastNotification.vue';

// Visible toasts amount
const VISIBLE_TOASTS_AMOUNT = 3;

// Viewport padding
const VIEWPORT_OFFSET = '24px';
const MOBILE_VIEWPORT_OFFSET = '16px';

// Default toast width
const TOAST_WIDTH = 0;

// Default gap between toasts
const DEFAULT_GAP = 16;

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
const layout = ref<ToastLayout>(props.layout);
const position = ref<ToastPostion>(props.position);

const toastsHovered = ref(false);
const expanded = ref(props.layout === 'expanded' ? true : false);
const paddingBetweenToasts = ref(props.paddingBetweenToasts);
const heightRecalculateTimeout = ref<null | number>(null);
const burnTimeout = ref<null | number>(null);

// function UniqueComponentId(prefix = 'pv_id_') {
//     return prefix + Math.random().toString(16).slice(2);
// }

// function onAdd(toast) {
//     console.log(toast);

//     if (toast.options.position) position.value = toast.options.position;

//     if (toast.options.id == null) {
//         toast.options.id = UniqueComponentId('toast_');
//     }
//     messages.value.unshift({
//         ...toast.options,
//         id: toast.options.id ?? UniqueComponentId('toast_'),
//         type: toast.options.type ?? 'default',
//         position: toast.options.position ?? position.value,
//         life: toast.options.life ?? props.defaultLife,
//         title: toast.title,
//     });
// }

function deleteToastWithId(id: string) {
    messages.value = messages.value.filter((msg) => msg.id !== id);
    ToastState.dismiss(id);
    stackToasts();
}

function stackToasts() {
    positionToasts();
    calculateHeightOfToastsContainer();

    if (heightRecalculateTimeout.value) clearTimeout(heightRecalculateTimeout.value);

    heightRecalculateTimeout.value = setTimeout(() => {
        calculateHeightOfToastsContainer();
    }, 300);
}

function positionToasts() {
    if (messages.value.length == 0) return;

    try {
        let scaleBuffer = 1;
        let yBuffer = 0;
        let zBuffer = 200;
        let bottomFlag = position.value.includes('bottom');
        let totalHeight = 0;
        let toastElements = [];
        for (let i = 0; i < messages.value.length; i++) {
            const toast = document.getElementById(`${messages.value[i].id}`);

            if (!toast) return;

            toast.style.zIndex = `${zBuffer}`;
            toastElements.push(toast);

            if (expanded.value) {
                totalHeight = totalHeight + (totalHeight ? paddingBetweenToasts.value : 0);

                if (bottomFlag) {
                    toast.style.top = 'auto';
                    toast.style.bottom = totalHeight + 'px';
                } else toast.style.top = totalHeight + 'px';

                // console.log('scale', toast.style.scale);

                totalHeight += toast.offsetHeight;
                // toast.style.scale === '1'
                // ? toast.getBoundingClientRect().height
                // : Math.round(toast.getBoundingClientRect().height / scaleBuffer);
                // totalHeight += toast.getBoundingClientRect().height;
                // console.log('totalHeight', i + 1, totalHeight);

                toast.style.scale = `1`;
                toast.style.transform = `translateY(0px)`;
            } else if (i > 0) {
                toast.style.scale = `${scaleBuffer}`;

                if (bottomFlag && !(i >= props.maxVisibleToasts)) {
                    toast.style.transform = `translateY(-${yBuffer}px)`;
                } else {
                    alignBottom(toastElements[0], toast);
                    toast.style.transform = `translateY(${yBuffer}px)`;
                }
            }

            zBuffer -= 10;
            scaleBuffer -= 0.06;
            yBuffer += 16;
        }

        messages.value.length >= props.maxVisibleToasts;

        let burnToast = document.getElementById(`${messages.value[props.maxVisibleToasts]?.id}`);

        if (burnToast) {
            burnToast.firstElementChild?.classList.remove('opacity-100');
            burnToast.firstElementChild?.classList.add('opacity-0');

            if (burnTimeout.value) {
                clearTimeout(burnTimeout.value);
            }

            // Burn 🔥 (remove) last toast
            burnTimeout.value = setTimeout(function () {
                messages.value.pop();
            }, 300);

            if (position.value.includes('bottom')) {
                toastElements[1].style.top = 'auto';
            }
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

    let lastToast = messages.value[messages.value.length - 1];
    let lastToastRectangle = document.getElementById(`${lastToast?.id}`)?.getBoundingClientRect();

    let firstToast = messages.value[0];
    let firstToastRectangle = document.getElementById(`${firstToast?.id}`)?.getBoundingClientRect();

    if (!firstToastRectangle || !lastToastRectangle) return;

    if (toastsHovered.value) {
        if (position.value.includes('bottom')) {
            container.value.style.height = firstToastRectangle.top + firstToastRectangle.height - lastToastRectangle.top + 'px';
        } else {
            container.value.style.height = lastToastRectangle.top + lastToastRectangle.height - firstToastRectangle.top + 'px';
        }
    } else {
        container.value.style.height = firstToastRectangle.height + 'px';
    }
}

function alignBottom(element1: HTMLElement, element2: HTMLElement) {
    // Get the top position and height of the first element
    let top1 = element1.offsetTop;
    let height1 = element1.offsetHeight;

    // Get the height of the second element
    let height2 = element2.offsetHeight;

    // Calculate the top position for the second element
    let top2 = top1 + (height1 - height2);

    // Apply the calculated top position to the second element
    element2.style.top = top2 + 'px';
}

function resetBottom() {
    for (let i = 0; i < messages.value.length; i++) {
        let toastElement = document.getElementById(`${messages.value[i]?.id}`);
        if (toastElement) {
            toastElement.style.bottom = '0px';
        }
    }
}

function resetTop() {
    for (let i = 0; i < messages.value.length; i++) {
        let toastElement = document.getElementById(`${messages.value[i]?.id}`);
        if (toastElement) {
            toastElement.style.top = '0px';
        }
    }
}

//#region Old Toast Event Bus Code

// onMounted(() => {
//     ToastEventBus.on('add', onAdd);
//     ToastEventBus.on('remove', onRemove);
//     stackToasts();
// });

// onBeforeUnmount(() => {
//     ToastEventBus.off('add', onAdd);
//     ToastEventBus.off('remove', onRemove);
// });

// function onRemove(message: Message) {
//     console.log('remove');

//     let params = { message, type: 'close' };
//     if (!params.message.id) return; // This is slow and causes issues. Every second remove is not called because the last one is blocking the event somehow. Doing it right in the event watcher makes it work correctly.
//     // for (let i = 0; i < this.messages.length; i++) {
//     //     if (this.messages[i].idx === params.message.idx) {
//     //         console.log('found ' + params.message.idx);

//     //         this.messages.splice(i, 1);
//     //         break;
//     //     }
//     // }
//     // if (this.messages[params.message.idx]) {
//     //     // delete this.messages[params.message.idx]
//     //     // this.$emit(params.type, { message: params.message });
//     // }
//     deleteToastWithId(params.message.id);
// }

//#endregion

onMounted(() => {
    stackToasts();
});

watch(
    () => toastsHovered.value,
    (value) => {
        if (layout.value == 'default') {
            if (position.value.includes('bottom')) {
                resetBottom();
            } else {
                resetTop();
            }

            if (value) {
                // calculate the new positions
                expanded.value = true;
                if (layout.value == 'default') {
                    stackToasts();
                }
            } else {
                if (layout.value == 'default') {
                    expanded.value = false;
                    //setTimeout(function(){
                    stackToasts();
                    //}, 10);
                    setTimeout(function () {
                        stackToasts();
                    }, 10);
                }
            }
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
            } else {
                let toast = newToast as Message;

                if (toast.position) position.value = toast.position;

                messages.value.unshift({
                    ...toast,
                    id: toast.id,
                    type: toast.type ?? 'default',
                    position: toast.position ?? position.value,
                    life: toast.life ?? props.defaultLife,
                    title: toast.title,
                });

                // messages.value = [toast as Message, ...messages.value];
            }
        });
    });

    onInvalidate(unsubscribe);
});
</script>

<template>
    <teleport to="body">
        <ul
            v-cloak
            ref="container"
            :tabIndex="-1"
            :class="
                `fixed w-full ${TOAST_WIDTH ? `sm:w-[${TOAST_WIDTH}px]` : 'sm:max-w-sm'}  group z-[500] [&>*]:px-4 [&>*]:px-[${mobileViewportOffset ?? viewportOffset}] [&>*]:sm:px-6 [&>*]:sm:px-[${viewportOffset}] my-4 sm:my-6 my-[${mobileViewportOffset ?? viewportOffset}] sm:my-[${viewportOffset}] ` +
                `${position == 'top-right' ? 'right-0 top-0' : ''} ` +
                `${position == 'top-left' ? 'left-0 top-0' : ''} ` +
                `${position == 'top-center' ? 'left-1/2 -translate-x-1/2 top-0' : ''} ` +
                `${position == 'bottom-right' ? 'right-0 bottom-0' : ''} ` +
                `${position == 'bottom-left' ? 'left-0 bottom-0' : ''} ` +
                `${position == 'bottom-center' ? 'left-1/2 -translate-x-1/2 bottom-0' : ''} `
            "
            @mouseenter="toastsHovered = true"
            @mouseleave="toastsHovered = false"
        >
            <ToastNotification
                v-for="toast in messages"
                v-bind="toast"
                :key="toast.id"
                :stack="stackToasts"
                :toastCount="messages.length"
                :position="position"
                @close="deleteToastWithId"
            />
        </ul>
    </teleport>
</template>
