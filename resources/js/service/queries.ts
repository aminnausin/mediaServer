import type { PulseResponse } from '@/types/types.ts';
import type { Ref } from 'vue';

import { getPulse } from '@/service/siteAPI.ts';
import { useQuery } from '@tanstack/vue-query';

import mediaAPI from '@/service/mediaAPI.ts';

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

export const useGetPulse = (type?: string) => {
    return useQuery<{ data: PulseResponse }>({
        queryKey: ['pulse'],
        queryFn: async () => {
            const { data: response } = await getPulse(type);
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
