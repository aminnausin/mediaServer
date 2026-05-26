import { reactive, ref } from 'vue';

export function useTooltipVisibility(delay: number = 200, leaveDelay: number = 100) {
    const tooltipVisible = ref(false);
    const timeouts = reactive<{ enter: NodeJS.Timeout | null; leave: NodeJS.Timeout | null }>({
        enter: null,
        leave: null,
    });

    function show(overrideDelay?: number, cb?: () => void, prepare?: () => void) {
        if (timeouts.leave) clearTimeout(timeouts.leave);
        if (tooltipVisible.value) return;
        // Not sure why event was checked for here if (tooltipVisible.value && event) return;
        if (timeouts.enter) clearTimeout(timeouts.enter);

        timeouts.enter = globalThis.setTimeout(() => {
            tooltipVisible.value = true;
            if (cb) cb();
        }, overrideDelay ?? delay);
    }

    function hide(overrideDelay?: number, cb?: () => void) {
        if (timeouts.enter) clearTimeout(timeouts.enter);
        if (!tooltipVisible.value) return;
        if (timeouts.leave) clearTimeout(timeouts.leave);

        timeouts.leave = globalThis.setTimeout(() => {
            tooltipVisible.value = false;
            if (cb) cb();
        }, overrideDelay ?? leaveDelay);
    }

    return { tooltipVisible, show, hide };
}
