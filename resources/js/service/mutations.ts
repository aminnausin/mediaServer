import { useMutation, useQueryClient } from '@tanstack/vue-query';
import { API } from './api';

import mediaAPI from './mediaAPI';

export const UseCreatePlayback = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: mediaAPI.createPlayback,
        onError: () => {},
        onSuccess: (data, variable) => {},
        onSettled: async (data, error, variable) => {
            // console.log(variable);
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
        onSuccess: (data, variable) => {},
        onSettled: async (data, error, variable) => {
            // console.log(variable);
            await queryClient.invalidateQueries({
                queryKey: ['videoTags'],
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
