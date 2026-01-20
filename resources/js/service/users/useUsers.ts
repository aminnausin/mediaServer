import type { MaybeRefOrGetter } from 'vue';
import type { ProfileResource } from '@/types/resources';

import { getProfileById, getProfileByName } from '@/service/users/api';
import { computed, toValue } from 'vue';
import { useQuery } from '@tanstack/vue-query';

export function useUserProfile(username: MaybeRefOrGetter<string>) {
    return useQuery<ProfileResource>({
        queryKey: ['profileByName', toValue(username)],
        queryFn: async () => {
            const { data } = await getProfileByName(toValue(username));
            if (!data) {
                throw new Error('Profile not found');
            }
            return data;
        },
        staleTime: 60 * 1000,
        retry: 1,
    });
}

export function useUserProfileById(id: MaybeRefOrGetter<number>) {
    return useQuery<ProfileResource>({
        queryKey: ['ProfileById', () => toValue(id)],
        queryFn: async () => {
            const { data } = await getProfileById(toValue(id));
            if (!data) {
                throw new Error('Profile not found');
            }
            return data;
        },
        enabled: computed(() => !!toValue(id)),
        staleTime: 60 * 1000,
        retry: 1,
    });
}
