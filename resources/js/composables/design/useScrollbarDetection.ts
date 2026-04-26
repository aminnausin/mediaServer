import type { Ref } from 'vue';

import { ref, onMounted, onUnmounted } from 'vue';

export function useScrollbarDetection(containerRef: Ref<HTMLElement | null>, debounceMs = 0) {
    const hasScrollbar = ref(false);

    const checkScrollbar = () => {
        if (!containerRef.value) return;

        hasScrollbar.value = containerRef.value.scrollHeight > containerRef.value.clientHeight;
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

    return { hasScrollbar };
}
