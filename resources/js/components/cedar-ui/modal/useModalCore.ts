import type { Component } from 'vue';

import { shallowRef, ref, reactive } from 'vue';

const isAnimating = ref(false);
const isOpen = ref(false);
const animationTime = ref(300);
const timeoutId = ref<number | null>(null);

const props = reactive<Record<string, any>>({});
const component = shallowRef<Component | null>(null);

function open(comp: Component, newProps: Record<string, any> = {}) {
    if (timeoutId.value) clearTimeout(timeoutId.value);
    component.value = comp;
    Object.assign(props, newProps);

    isOpen.value = true;
    isAnimating.value = true;

    timeoutId.value = window.setTimeout(() => {
        isAnimating.value = false;
    }, animationTime.value);
}

function close() {
    if (timeoutId.value) clearTimeout(timeoutId.value);
    isOpen.value = false;
    isAnimating.value = true;
    timeoutId.value = window.setTimeout(() => {
        isAnimating.value = false;
    }, animationTime.value);
}

export function useModalCore() {
    return { isOpen, isAnimating, animationTime, props, component, open, close };
}
