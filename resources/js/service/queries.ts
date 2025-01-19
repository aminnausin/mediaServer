import type { CategoryResource, FolderResource, TaskResource, UserResource } from '@/types/resources';
import type { PulseResponse, TaskStatsResponse } from '@/types/types.ts';
import type { Ref } from 'vue';

import { getSiteAnalytics, getPulse, getUsers, getTasks, getTaskStats } from '@/service/siteAPI.ts';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useQuery } from '@tanstack/vue-query';

import mediaAPI, { getCategories, getFolders } from '@/service/mediaAPI.ts';

export const useGetVideoTags = () => {
    return useQuery({
        queryKey: ['videoTags'],
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
        // enabled: !!period,
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

// export const useGetChatRoomMessagesQuery = (chat_id: number | null) => {
//     return useInfiniteQuery({
//         queryKey: ['chatroom-messages', chat_id],
//         initialPageParam: `/chat/chatroom/${chat_id}/messages/?page=${1}`,
//         getNextPageParam: (lastPage) => lastPage.next,
//         queryFn: async ({ pageParam }: { pageParam: string }): Promise<PaginatedResponse<MessageResponse>> => {
//             if (!chat_id)
//                 return {
//                     count: 0,
//                     total_pages: 0,
//                     page_size: 0,
//                     next: null,
//                     previous: null,
//                     results: [],
//                 };
//             const response = await axios.get(pageParam);
//             return response.data;
//         },
//     });
// };
