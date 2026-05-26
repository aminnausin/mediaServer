import { API } from '../api';

export const resetSubtitles = (metadataId: number) => {
    return API.delete(`/metadata/${metadataId}/subtitles`, { headers: { 'X-Skip-Toast': 'true' } });
};
