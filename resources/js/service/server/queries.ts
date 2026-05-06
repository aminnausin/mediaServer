import type { ServerConfig } from '@/contracts/server';

import { getServerConfig } from '@/service/server/serverConfig';
import { useQuery } from '@tanstack/vue-query';

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
