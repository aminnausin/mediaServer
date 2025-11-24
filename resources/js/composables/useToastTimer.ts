import { ref, watch, onBeforeUnmount } from 'vue';

interface UseToastTimerOptions {
    duration: number;
    isPaused: () => boolean;
    onTimeout: () => void;
    immediate?: boolean;
}

export function useToastTimer({ duration, isPaused, onTimeout, immediate = true }: UseToastTimerOptions) {
    const timeoutId = ref<number | null>();
    const startTime = ref(0);
    const remainingTime = ref(duration);

    function start() {
        if (remainingTime.value === Infinity || isPaused()) return;
        startTime.value = Date.now();
        timeoutId.value = window.setTimeout(() => {
            onTimeout();
        }, remainingTime.value);
    }

    function pause() {
        cancel();

        const elapsed = Date.now() - startTime.value;
        remainingTime.value -= elapsed;
    }

    function cancel() {
        if (timeoutId.value) {
            clearTimeout(timeoutId.value);
            timeoutId.value = null;
        }
    }

    watch(
        isPaused,
        (paused) => {
            if (paused) {
                pause();
                return;
            }
            start();
        },
        { immediate },
    );

    onBeforeUnmount(() => {
        cancel();
    });

    return { cancel };
}
