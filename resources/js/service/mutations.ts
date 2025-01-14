/* eslint-disable @typescript-eslint/no-unused-vars */
import { useMutation, useQueryClient, type UseMutationReturnType } from '@tanstack/vue-query';
import { API } from './api';

import mediaAPI, { updateCategory } from './mediaAPI';

export const UseCreatePlayback = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: mediaAPI.createPlayback,
        onError: () => {},
        onSuccess: () => {},
        onSettled: async () => {
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
        onSuccess: () => {},
        onSettled: async () => {
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
