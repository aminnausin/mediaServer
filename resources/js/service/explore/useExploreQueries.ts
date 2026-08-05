import { getContinueWatching, getRecentlyAdded, getRecentlyReleased, getRecentlyUpdated, getRecentlyUploaded } from '@/service/explore/exploreAPI';
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { useAuth } from '@/composables/auth/useAuth';

export const useContinueWatching = () => {
    const { isAuthenticated } = useAuth();
    return useQuery({
        queryKey: ['auth', 'explore', 'continue-watching'],
        queryFn: getContinueWatching,
        staleTime: 1000 * 30,
        retry: false,
        enabled: computed(() => !!isAuthenticated.value),
    });
};

export const useRecentlyReleased = () => {
    return useQuery({
        queryKey: ['auth', 'explore', 'recently-released'],
        queryFn: getRecentlyReleased,
        staleTime: 1000 * 30,
        retry: false,
    });
};

export const useRecentlyUpdated = () => {
    return useQuery({
        queryKey: ['auth', 'explore', 'recently-updated'],
        queryFn: getRecentlyUpdated,
        staleTime: 1000 * 30,
        retry: false,
    });
};

export const useRecentlyAdded = () => {
    return useQuery({
        queryKey: ['auth', 'explore', 'recently-added'],
        queryFn: getRecentlyAdded,
        staleTime: 1000 * 30,
        retry: false,
    });
};

export const useRecentlyUploaded = (mediaType?: 'video' | 'audio') => {
    return useQuery({
        queryKey: ['auth', 'explore', ['recently-uploaded', mediaType].filter(Boolean).join('-')],
        queryFn: () => getRecentlyUploaded(mediaType),
        staleTime: 1000 * 30,
        retry: false,
    });
};
