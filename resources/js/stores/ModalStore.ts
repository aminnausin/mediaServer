import { shallowRef, ref, reactive, type Component } from 'vue';
import { defineStore } from 'pinia';

export const useModalStore = defineStore('Modal', () => {
    const isAnimating = ref(false);
    const isOpen = ref(false);

    const props = reactive<Record<string, any>>({});
    const component = shallowRef<any>(null);

    const animationTime = ref(300);
    const timeoutId = ref<number | null>(null);

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

    return {
        props,
        isOpen,
        isAnimating,
        component,
        animationTime,
        open,
        close,
    };
});
