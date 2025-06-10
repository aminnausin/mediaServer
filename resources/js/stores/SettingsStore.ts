import type { Session } from '@/types/model';

import { useGetSessions } from '@/service/queries';
import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useSettingsStore = defineStore('Settings', () => {
    const { data: rawSessions, isLoading: isLoadingSessions } = useGetSessions();
    const stateSessions = ref<Session[]>([]);

    watch(rawSessions, (v) => {
        stateSessions.value = v ?? [];
    });

    return {
        stateSessions,
        isLoadingSessions,
    };
});
