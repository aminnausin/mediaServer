import { computed, reactive, ref, watch } from 'vue';

export default function useTable(props: any) {
    const currentPage = ref(1);
    const itemsPerPage = ref(props.itemsPerPage ?? 10);
    const searchQuery = ref(props.searchQuery ?? '');

    const table = reactive({
        filteredPage: computed(() => {
            const minIndex = itemsPerPage.value * (currentPage.value - 1);
            const maxIndex = Math.min(itemsPerPage.value * currentPage.value, props.data.length);

            return props.data.slice(minIndex, maxIndex);
        }),
        props,
        fields: { currentPage, itemsPerPage, searchQuery },
        handlePageChange(page: number) {
            currentPage.value = page;
        },
        handlePageReset() {
            currentPage.value = 1;
        },
    });

    watch(
        () => props.data,
        (newData, old) => {
            if (newData?.length !== old?.length || (newData[0] && old[0] && newData[0]?.id !== old[0]?.id)) table.handlePageReset();
        },
        { immediate: true },
    );

    return table;
}
