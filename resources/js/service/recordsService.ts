/*
    ACTIONS for record data ?

    Have get, add, and delete actions
    Run and commit to database (store) -> ?? What does that mean?
*/
import type { RecordStoreRequest } from '@/types/requests';
import { API } from './api';

export default {
    createRecord(data: RecordStoreRequest) {
        return API.post('/records', data);
    },
    getRecords(limit?: number) {
        if (limit) return API.get(`/records?limit=${limit}`);

        return API.get(`/records`);
    },
    deleteRecord(id: number) {
        return API.delete(`/records/${id}`);
    },
};
