import type { CategoryResource, FolderResource, TaskResource, UserResource } from '@/types/resources';
import type { AppManifest, TaskStatsResponse, WaitTimesResponse } from '@/types/types.ts';
import type { PulseResponse } from '@/types/pulseTypes';
import type { Session } from '@/types/model';
import type { Ref } from 'vue';

import { getSiteAnalytics, getPulse, getUsers, getTasks, getTaskStats, getActiveSessions, getManifest, getTaskWaitTimes } from '@/service/siteAPI.ts';
import { getSessions } from '@/service/authAPI';
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { useAuth } from '@/composables/auth/useAuth';

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
            if (Number.isNaN(idRef.value)) return [];
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
    const { isAuthenticated } = useAuth();
    return useQuery<CategoryResource[]>({
        queryKey: ['auth-only', 'categories'],
        queryFn: async () => {
            const { data: response } = await getCategories();
            return response;
        },
        retry: false,
        enabled: computed(() => !!isAuthenticated.value),
    });
};

export const useGetLibraryFolders = (id: Ref<number, number>) => {
    const { isAuthenticated } = useAuth();
    return useQuery<{ data: FolderResource[] }>({
        queryKey: ['auth-only', 'libraryFolders', id],
        queryFn: async () => {
            if (id.value < 1) return { data: [] };
            const { data: response } = await getFolders(id.value);
            return response;
        },
        retry: false,
        enabled: computed(() => !!isAuthenticated.value),
    });
};

export const useGetUsers = () => {
    const { isAuthenticated } = useAuth();
    return useQuery<{ data: UserResource[] }>({
        queryKey: ['users'],
        queryFn: async () => {
            const { data: response } = await getUsers();
            return response;
        },
        retry: false,
        enabled: computed(() => !!isAuthenticated.value),
    });
};

export const useGetTasks = () => {
    const { isAuthenticated, userData } = useAuth();
    return useQuery<{ data: TaskResource[] }>({
        queryKey: ['tasks'],
        queryFn: async () => {
            if (userData.value?.id !== 1) return { data: [] };

            const { data: response } = await getTasks();
            return response;
        },
        enabled: computed(() => !!isAuthenticated.value),
    });
};

export const useGetTaskStats = () => {
    const { isAuthenticated } = useAuth();
    return useQuery<{ data: TaskStatsResponse }>({
        queryKey: ['taskStats'],
        queryFn: async () => {
            const { data: response } = await getTaskStats();
            return response;
        },
        enabled: computed(() => !!isAuthenticated.value),
    });
};

export const useGetActiveSessions = () => {
    const { isAuthenticated } = useAuth();
    return useQuery<{ data: number }>({
        queryKey: ['activeSessions'],
        queryFn: async () => {
            const { data: response } = await getActiveSessions();
            return response;
        },
        enabled: computed(() => !!isAuthenticated.value),
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
    const { isAuthenticated } = useAuth();
    return useQuery<Session[]>({
        queryKey: ['sessions'],
        queryFn: async () => {
            const { data: response } = await getSessions();
            return response;
        },
        enabled: computed(() => !!isAuthenticated.value),
    });
};

export const useGetTaskWaitTimes = () => {
    const { isAuthenticated } = useAuth();
    return useQuery<WaitTimesResponse>({
        queryKey: ['wait-times'],
        queryFn: async () => {
            const { data: response } = await getTaskWaitTimes();
            return response;
        },
        enabled: computed(() => !!isAuthenticated.value),
    });
};
