import type { ServerConfig, DiskUsageResponse } from '@/contracts/server';

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { getServerConfig } from '@/service/server/serverConfig';
import { API } from '@/service/api';

export const diskUsageKey = ['server', 'disk-usage'] as const;

export const useGetConfig = () => {
    return useQuery<ServerConfig>({
        queryKey: ['serverConfig'],
        queryFn: async () => {
            const { data: response } = await getServerConfig();
            return response;
        },
        retry: false,
    });
};

export const useGetDiskUsage = () => {
    return useQuery({
        queryKey: diskUsageKey,
        queryFn: () => API.get<DiskUsageResponse>('/server/disk-usage'),
        select: (res) => res.data,
        staleTime: 5 * 60_000,
        refetchOnWindowFocus: false,
        retry: 0,
    });
};

export const useRefreshDiskUsage = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: () => API.get<DiskUsageResponse>('/server/disk-usage', { params: { refresh: 1 } }),
        onSuccess: (res) => queryClient.setQueryData(diskUsageKey, res),
    });
};
