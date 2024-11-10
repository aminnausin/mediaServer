import { useQuery } from '@tanstack/vue-query';
import mediaAPI from './mediaAPI';
export const useGetVideoTags = () => {
    return useQuery({
        queryKey: ['videoTags'],
        queryFn: () => {
            mediaAPI.getTags();
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
