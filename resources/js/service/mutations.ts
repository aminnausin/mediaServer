import { useMutation, useQueryClient } from '@tanstack/vue-query';
import { API } from '@/service/api';

import mediaAPI from '@/service/mediaAPI';

export const UseCreatePlayback = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: mediaAPI.createPlayback,
        onError: () => {},
        onSuccess: () => {},
        onSettled: async () => {
            await queryClient.invalidateQueries({
                queryKey: ['videoPlayback'],
            });
        },
    });
};

export const UseCreateTag = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: CreateTag,
        onError: () => {},
        onSuccess: () => {},
        onSettled: async () => {
            await queryClient.invalidateQueries({
                queryKey: ['allTags'],
            });
        },
    });
};

export const CreateTag = async (data: { name: string }) => {
    try {
        const response = await API.post('/tags', data);
        return response.data;
    } catch (error) {
        console.error('Failed to create tag', error);
    }
};
