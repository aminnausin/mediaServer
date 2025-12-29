import type { UseTableOptions } from '@aminnausin/cedar-ui';

import { computed, ref, toRef, watch } from 'vue';

export default function useTable<T>(options: UseTableOptions<T>) {
    const itemsPerPage = ref(options.itemsPerPage ?? 10);
    const currentPage = ref(1);
    const data = toRef(options, 'data');

    const pageCount = computed(() => Math.ceil(data.value.length / itemsPerPage.value));
    const pageData = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        return data.value.slice(start, start + itemsPerPage.value);
    });

    function setPage(page: number) {
        currentPage.value = Math.min(Math.max(1, page), pageCount.value);
    }

    function resetPage() {
        currentPage.value = 1;
    }

    watch(
        () => data,
        () => {
            if (options.resetOnDataChange !== false) resetPage();
        },
        { immediate: true },
    );

    return {
        // state
        currentPage,
        itemsPerPage,

        // derived
        pageCount,
        pageData,

        // actions
        setPage,
        resetPage,
    };
}
