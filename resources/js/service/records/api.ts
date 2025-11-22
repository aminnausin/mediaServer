/*
    ACTIONS for record data ?

    Have get, add, and delete actions
    Run and commit to database (store) -> ?? What does that mean?
*/
import type { RecordStoreRequest } from '@/types/requests';
import type { RecordResource } from '@/types/resources';

import { API } from '../api';

export default {
    createRecord(data: RecordStoreRequest) {
        return API.post<{ data: RecordResource }>('/records', data);
    },
    getRecords(params?: { limit?: number; page?: number }) {
        const queryParams = new URLSearchParams();
        if (params?.limit) queryParams.set('limit', params.limit.toString());
        if (params?.page) queryParams.set('page', params.page.toString());

        let queryString = queryParams.toString();
        if (queryString) queryString = `?${queryString}`;

        return API.get<{ data: RecordResource[] }>(`/records${queryString}`);
    },

    deleteRecord(id: number) {
        return API.delete(`/records/${id}`);
    },
};
