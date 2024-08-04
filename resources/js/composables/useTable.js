import { computed, reactive, ref, watch } from 'vue';

export default function useTable(props){
    const currentPage = ref(1);
    const itemsPerPage = ref(props.itemsPerPage ?? 10);
    const searchQuery = ref(props.searchQuery ?? '')

    const handlePageChange = (page) => {
        currentPage.value = page;
    }

    const handlePageReset = () => {
        currentPage.value = 1
    };

    watch(props.data, handlePageReset, {immediate: true})

    return reactive({
        filteredPage : computed(() => {
            const minIndex = itemsPerPage.value * (currentPage.value - 1);
            const maxIndex = Math.min(itemsPerPage.value * (currentPage.value), props.data.length);
            
            return props.data.slice(minIndex, maxIndex);
        }),
        props,
        fields: {currentPage, itemsPerPage, searchQuery},
        handlePageChange,
        handlePageReset
    });
}