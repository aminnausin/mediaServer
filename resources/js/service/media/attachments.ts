import { API } from '@/service/api';

export const resetSubtitles = (metadataId: number) => {
    return API.delete(`/metadata/${metadataId}/subtitles`, { headers: { 'X-Skip-Toast': 'true' } });
};

export const resetFonts = (metadataId: number) => {
    return API.delete(`/metadata/${metadataId}/fonts`, { headers: { 'X-Skip-Toast': 'true' } });
};
