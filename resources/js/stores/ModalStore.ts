import type { Component } from 'vue';

import { shallowRef, ref, reactive } from 'vue';
import { defineStore } from 'pinia';

export const useModalStore = defineStore('Modal', () => {
    const isAnimating = ref(false);
    const isOpen = ref(false);

    const props = reactive<Record<string, any>>({});
    const component = shallowRef<any>(null);

    const animationTime = ref(300);
    const timeoutId = ref<number | null>(null);

    function open<T extends Record<string, any>>(comp: Component, newProps: T = {} as T) {
        if (timeoutId.value) clearTimeout(timeoutId.value);
        component.value = comp;

        Object.keys(props).forEach((key) => delete props[key]);
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

    function getProps<T extends Record<string, any>>(): T {
        return props as T;
    }

    return {
        props,
        isOpen,
        isAnimating,
        component,
        animationTime,
        open,
        close,
        getProps,
    };
});
