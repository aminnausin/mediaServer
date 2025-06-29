import { API } from '@/service/api';

export async function getProfileByName(username: string) {
    const parsedId = parseInt(username);
    if (!isNaN(parsedId) && parsedId.toString() === username) return getProfileById(parsedId);

    const { data } = await API.get(`/profiles/search/${username}`);
    return data ?? null;
}

export async function getProfileById(id: number) {
    const { data } = await API.get(`/profiles/${id}`);
    return data ?? null;
}
