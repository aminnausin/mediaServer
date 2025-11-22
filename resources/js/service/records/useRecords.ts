// Eventually convert the records state in contentStore to a tanstack query composable
import type { RecordStoreRequest } from '@/types/requests';
import type { RecordResource } from '@/types/resources';

import { useMutation, useQuery } from '@tanstack/vue-query';
import { useAuthStore } from '@/stores/AuthStore';
import { queryClient } from '@/service/vue-query';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import api from './api';

export function useRecordsLimited(limit = 10) {
    const { userData } = storeToRefs(useAuthStore());

    const query = useQuery<RecordResource[]>({
        queryKey: ['records', 'limited'],
        queryFn: async () => {
            const full = queryClient.getQueryData<RecordResource[]>(['records', 'full']);
            if (full) {
                return full.slice(0, limit);
            }

            const res = await api.getRecords({ limit });
            return res.data.data;
        },
        staleTime: Infinity,
        placeholderData: [],
        enabled: computed(() => !!userData.value),
    });

    return {
        stateRecords: query.data,
        getRecords: () => queryClient.invalidateQueries({ queryKey: ['records', 'limited'] }),
        ...query,
    };
}

export function useRecords() {
    const query = useQuery<RecordResource[]>({
        queryKey: ['records', 'full'],
        queryFn: async () => (await api.getRecords()).data.data ?? [],
        staleTime: 5 * 60 * 1000, // 5 minutes
        placeholderData: [],
    });

    return {
        stateRecords: query.data.value,
        ...query,
    };
}

export function useRecord() {
    const { userData } = storeToRefs(useAuthStore());

    const createRecord = useMutation({
        mutationFn: async (data: RecordStoreRequest) => {
            if (!userData.value) return;
            return api.createRecord(data);
        },
        onSuccess: (res) => {
            if (!res) return;

            const newRecord = res.data.data;

            // dont prepend to full history if not loaded yet to prevent query blocking
            queryClient.setQueryData<RecordResource[]>(['records', 'full'], (old) => (old ? [newRecord, ...old] : old));

            queryClient.setQueryData<RecordResource[]>(['records', 'limited'], (old) => {
                if (!old) return [newRecord];
                return [newRecord, ...old].slice(0, 10);
            });
        },
    });

    const deleteRecord = useMutation({
        mutationFn: api.deleteRecord,
        onSuccess: (_, deletedId) => {
            queryClient.setQueryData<RecordResource[]>(['records', 'full'], (old) => old?.filter((record) => record.id !== deletedId) ?? []);
            queryClient.setQueryData<RecordResource[]>(['records', 'limited'], (old) => old?.filter((record) => record.id !== deletedId) ?? []);
        },
    });

    return {
        createRecord,
        deleteRecord,
    };
}
