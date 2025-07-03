import type { CategoryResource, FolderResource, TaskResource, UserResource } from '@/types/resources';
import type { AppManifest, TaskStatsResponse, WaitTimesResponse } from '@/types/types.ts';
import type { PulseResponse } from '@/types/pulseTypes';
import type { Session } from '@/types/model';
import type { Ref } from 'vue';

import { getSiteAnalytics, getPulse, getUsers, getTasks, getTaskStats, getActiveSessions, getManifest, getTaskWaitTimes } from '@/service/siteAPI.ts';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { getSessions } from '@/service/authAPI';
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';

import mediaAPI, { getCategories, getFolders } from '@/service/mediaAPI.ts';

export const useGetAllTags = () => {
    return useQuery({
        queryKey: ['allTags'],
        queryFn: async () => {
            return mediaAPI.getTags();
        },
    });
};

export const useVideoPlayback = (idRef: Ref<number, number>) => {
    return useQuery({
        queryKey: ['videoPlayback', idRef],
        queryFn: async () => {
            if (isNaN(idRef.value)) return [];
            const { data: response } = await mediaAPI.getPlayback(idRef.value);
            return response;
        },
    });
};

export const useGetPulse = (req: { type?: string; period: Ref<string> }) => {
    return useQuery<{ data: PulseResponse }>({
        queryKey: ['pulse', req.period],
        queryFn: async () => {
            const { data: response } = await getPulse({ type: req.type, period: req.period.value });
            return response;
        },
    });
};

export const useGetSiteAnalytics = (period: Ref<string>) => {
    return useQuery({
        queryKey: ['siteAnalytics', period],
        queryFn: async () => {
            const { data: response } = await getSiteAnalytics(period.value);
            return response;
        },
    });
};

export const useGetCategories = () => {
    return useQuery<{ data: CategoryResource[] }>({
        queryKey: ['categories'],
        queryFn: async () => {
            const { data: response } = await getCategories();
            return response;
        },
    });
};

export const useGetLibraryFolders = (id: Ref<number, number>) => {
    return useQuery<{ data: FolderResource[] }>({
        queryKey: ['libraryFolders', id],
        queryFn: async () => {
            if (id.value < 1) return { data: [] };
            const { data: response } = await getFolders(id.value);
            return response;
        },
    });
};

export const useGetUsers = () => {
    return useQuery<{ data: UserResource[] }>({
        queryKey: ['users'],
        queryFn: async () => {
            const { data: response } = await getUsers();
            return response;
        },
    });
};

export const useGetTasks = () => {
    return useQuery<{ data: TaskResource[] }>({
        queryKey: ['tasks'],
        queryFn: async () => {
            const { userData } = storeToRefs(useAuthStore());

            if (userData.value?.id !== 1) return { data: [] };

            const { data: response } = await getTasks();
            return response;
        },
    });
};

export const useGetTaskStats = () => {
    return useQuery<{ data: TaskStatsResponse }>({
        queryKey: ['taskStats'],
        queryFn: async () => {
            const { data: response } = await getTaskStats();
            return response;
        },
    });
};

export const useGetActiveSessions = () => {
    return useQuery<{ data: number }>({
        queryKey: ['activeSessions'],
        queryFn: async () => {
            const { data: response } = await getActiveSessions();
            return response;
        },
    });
};

export const useGetManifest = () => {
    return useQuery<{ data: AppManifest }>({
        queryKey: ['manifest'],
        queryFn: async () => {
            const { data: response } = await getManifest();
            return response;
        },
    });
};

/**
 *
 * @returns List of logged in sessions for the logged in user
 */
export const useGetSessions = () => {
    return useQuery<Session[]>({
        queryKey: ['sessions'],
        queryFn: async () => {
            const { data: response } = await getSessions();
            return response;
        },
    });
};

export const useGetTaskWaitTimes = () => {
    const { userData } = storeToRefs(useAuthStore());
    return useQuery<WaitTimesResponse>({
        queryKey: ['wait-times'],
        queryFn: async () => {
            const { data: response } = await getTaskWaitTimes();
            return response;
        },
        enabled: computed(() => !!userData.value),
    });
};
