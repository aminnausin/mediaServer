import { updateMediaConfig, updatePerformanceConfig, updateScannerConfig, updateStorageConfig } from '@/service/server/serverConfig';
import { useMutation, useQueryClient } from '@tanstack/vue-query';

const useServerConfigMutation = (mutationFn: (...args: any[]) => Promise<any>) => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn,
        retry: false,
        onSettled: () => queryClient.invalidateQueries({ queryKey: ['serverConfig'] }),
    });
};

export const UseUpdateScannerConfig = () => useServerConfigMutation(updateScannerConfig);
export const UseUpdateStorageConfig = () => useServerConfigMutation(updateStorageConfig);
export const UseUpdateMediaConfig = () => useServerConfigMutation(updateMediaConfig);
export const UseUpdatePerformanceConfig = () => useServerConfigMutation(updatePerformanceConfig);
