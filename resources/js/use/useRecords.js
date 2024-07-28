import { ref } from 'vue';

/*
    ACTIONS for record data ? 

    Have get, add, and delete actions
    Run and commit to database (store)
*/
export function useRecords() {
    const records = ref(null);
    const error = ref(null);

    return { records, error};
}