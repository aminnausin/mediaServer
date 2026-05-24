import { API } from '../api';

export const regenerateStoryboard = (metadataId: number) => {
    return API.post(`/metadata/${metadataId}/storyboard`, { headers: { 'X-Skip-Toast': 'true' } });
};
