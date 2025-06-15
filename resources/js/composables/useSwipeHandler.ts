import type { SwipeDirection } from '@/types/pinesTypes';

import { ref, type Ref } from 'vue';

interface UseSwipeHandlerOptions {
    directions: Ref<SwipeDirection[]>;
    threshold: number;
    onSwipeOut: () => void;
}

export function useSwipeHandler({ directions, threshold, onSwipeOut }: UseSwipeHandlerOptions) {
    const isSwiping = ref(false);
    const offset = ref({ x: 0, y: 0 });
    const dragStartTime = ref<number>();
    const startOffset = ref({ x: 0, y: 0 });

    const getDampening = (delta: number) => {
        const factor = Math.abs(delta) / 20;
        return 1 / (1.5 + factor);
    };

    function onPointerDown(e: PointerEvent) {
        dragStartTime.value = Date.now();
        // Ensure we maintain correct pointer capture even when going outside of the toast (e.g. when swiping)
        (e.target as HTMLElement).setPointerCapture(e.pointerId);
        if ((e.target as HTMLElement).closest('button, a, input, textarea, select')) return;

        startOffset.value = { x: e.clientX, y: e.clientY };
        isSwiping.value = true;
    }

    function onPointerMove(e: PointerEvent) {
        if (!isSwiping.value) return;

        const swipeAmount = { x: 0, y: 0 };
        const xDelta = e.clientX - startOffset.value.x;
        const yDelta = e.clientY - startOffset.value.y;

        if ((directions.value.includes('left') && xDelta < 0) || (directions.value.includes('right') && xDelta > 0)) {
            swipeAmount.x = xDelta;
        } else {
            // Smoothly transition to dampened movement
            const dampened = xDelta * getDampening(xDelta);
            // Ensure we don't jump when transitioning to dampened movement
            swipeAmount.x = Math.abs(dampened) < Math.abs(xDelta) ? dampened : xDelta;
        }

        if ((directions.value.includes('top') && yDelta < 0) || (directions.value.includes('bottom') && yDelta > 0)) {
            swipeAmount.y = yDelta;
        } else {
            const dampened = yDelta * getDampening(yDelta);
            swipeAmount.y = Math.abs(dampened) < Math.abs(yDelta) ? dampened : yDelta;
        }

        offset.value = swipeAmount;
    }

    function onPointerUp() {
        if (!isSwiping.value) return;

        const timeTaken = Date.now() - (dragStartTime.value ?? 0);
        const swipeAmount = offset.value.x;
        const velocity = Math.abs(swipeAmount) / timeTaken;

        if (Math.abs(swipeAmount) >= threshold || velocity > 0.11) {
            onSwipeOut();
            return;
        }

        isSwiping.value = false;
        offset.value = { x: 0, y: 0 };
    }

    return {
        offset,
        isSwiping,
        onPointerDown,
        onPointerMove,
        onPointerUp,
    };
}
