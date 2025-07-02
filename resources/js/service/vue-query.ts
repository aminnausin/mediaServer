import type { AxiosError } from 'axios';

import { QueryClient } from '@tanstack/vue-query';

export const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            refetchOnWindowFocus: false,
            retry: (failureCount, error) => {
                const axiosError = error as AxiosError;
                const status = axiosError?.response?.status ?? 0;
                if ([401, 419].includes(status)) return false;
                return failureCount < 3;
            },
        },
        mutations: {
            retry: (failureCount, error) => {
                const axiosError = error as AxiosError;
                const status = axiosError?.response?.status ?? 0;
                if ([401, 419].includes(status)) return false;
                return failureCount < 3;
            },
        },
    },
});
