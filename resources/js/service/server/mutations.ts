import { updateMediaConfig, updatePerformanceConfig, updateScannerConfig, updateStorageConfig } from '@/service/server/serverConfig';
import { useMutation, useQueryClient } from '@tanstack/vue-query';

export const UseUpdateScannerConfig = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: updateScannerConfig,
        onSettled: async () => {
            await queryClient.invalidateQueries({
                queryKey: ['serverConfig'],
            });
        },
    });
};

export const UseUpdateStorageConfig = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: updateStorageConfig,
        onSettled: async () => {
            await queryClient.invalidateQueries({
                queryKey: ['serverConfig'],
            });
        },
    });
};

export const UseUpdateMediaConfig = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: updateMediaConfig,
        onSettled: async () => {
            await queryClient.invalidateQueries({
                queryKey: ['serverConfig'],
            });
        },
    });
};

export const UseUpdatePerformanceConfig = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: updatePerformanceConfig,
        onSettled: async () => {
            await queryClient.invalidateQueries({
                queryKey: ['serverConfig'],
            });
        },
    });
};
