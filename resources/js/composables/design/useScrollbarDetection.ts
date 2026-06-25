import type { Ref } from 'vue';

import { ref, onMounted, onUnmounted } from 'vue';

export function useScrollbarDetection(containerRef: Ref<HTMLElement | null>, debounceMs = 0, direction: 'x' | 'y' = 'y') {
    const hasScrollbar = ref(false);
    const hasScrollbarX = ref(false);
    const hasScrollbarY = ref(false);

    const checkScrollbar = () => {
        if (!containerRef.value) return;

        hasScrollbarY.value = containerRef.value.scrollHeight > containerRef.value.clientHeight;
        hasScrollbarX.value = containerRef.value.scrollWidth > containerRef.value.clientWidth;

        hasScrollbar.value = direction === 'x' ? hasScrollbarX.value : hasScrollbarY.value;
    };

    let resizeObserver: ResizeObserver | null = null;

    onMounted(() => {
        checkScrollbar();

        if (containerRef.value) {
            resizeObserver = new ResizeObserver(() => checkScrollbar());
            resizeObserver.observe(containerRef.value);
        }

        onUnmounted(() => {
            resizeObserver?.disconnect();
        });
    });

    return { hasScrollbar, hasScrollbarX, hasScrollbarY };
}
