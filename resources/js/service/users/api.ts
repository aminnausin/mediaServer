import type { ProfileResource } from '@/types/resources';

import { API } from '@/service/api';

export async function getProfileByName(username: string): Promise<{ data: ProfileResource | null }> {
    const parsedId = Number.parseInt(username);
    if (!Number.isNaN(parsedId) && parsedId.toString() === username) return getProfileById(parsedId);

    const { data } = await API.get(`/profiles/search/${username}`);
    return data ?? null;
}

export async function getProfileById(id: number): Promise<{ data: ProfileResource | null }> {
    const { data } = await API.get(`/profiles/${id}`);
    return data ?? null;
}
