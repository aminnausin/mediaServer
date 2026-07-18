import { getContinueWatching, getRecentlyAdded, getRecentlyReleased, getRecentlyUpdated, getRecentlyUploaded } from '@/service/home/homeAPI';
import { useQuery } from '@tanstack/vue-query';

export const useContinueWatching = () => {
    return useQuery({
        queryKey: ['home', 'continue-watching'],
        queryFn: getContinueWatching,
        staleTime: 1000 * 30,
        retry: false,
    });
};

export const useRecentlyReleased = () => {
    return useQuery({
        queryKey: ['home', 'recently-released'],
        queryFn: getRecentlyReleased,
        staleTime: 1000 * 30,
        retry: false,
    });
};

export const useRecentlyUpdated = () => {
    return useQuery({
        queryKey: ['home', 'recently-updated'],
        queryFn: getRecentlyUpdated,
        staleTime: 1000 * 30,
        retry: false,
    });
};

export const useRecentlyAdded = () => {
    return useQuery({
        queryKey: ['home', 'recently-added'],
        queryFn: getRecentlyAdded,
        staleTime: 1000 * 30,
        retry: false,
    });
};

export const useRecentlyUploaded = () => {
    return useQuery({
        queryKey: ['home', 'recently-uploaded'],
        queryFn: getRecentlyUploaded,
        staleTime: 1000 * 30,
        retry: false,
    });
};
