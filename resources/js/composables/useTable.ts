import type { MaybeRefOrGetter, Ref } from 'vue';
import type { TableRow } from '@aminnausin/cedar-ui';

import { computed, ref, toValue, watch } from 'vue';

interface UseTableOptions<T> {
    data: Ref<T[]>;
    itemsPerPage?: MaybeRefOrGetter<number>;
    resetOnDataChange?: MaybeRefOrGetter<boolean>;
}

export default function useTable<T extends TableRow>(options: UseTableOptions<T>) {
    const itemsPerPage = computed(() => toValue(options.itemsPerPage) ?? 10);
    const currentPage = ref(1);

    const pageCount = computed(() => Math.ceil(options.data.value.length / itemsPerPage.value));
    const pageData = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        return options.data.value.slice(start, start + itemsPerPage.value);
    });

    function setPage(page: number) {
        currentPage.value = Math.min(Math.max(1, page), Math.max(1, pageCount.value));
    }

    function resetPage() {
        currentPage.value = 1;
    }

    watch(itemsPerPage, () => resetPage());

    watch(
        options.data,
        (next, prev) => {
            if (toValue(options.resetOnDataChange) === false) return;

            if (!prev) return resetPage(); // On first load
            if (!next.length || !prev.length) return resetPage(); // Handles empty datasets ?
            if (next.length !== prev.length) return resetPage(); // If length changes (search, data source change)
            if (next[0]?.id !== prev[0]?.id) return resetPage(); // If data changes (first item is different but lengths are the same)
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
