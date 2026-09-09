import type { AxiosError, AxiosResponse } from 'axios';
import type { FontResource } from '@/contracts/media';

import { useContentStore } from '@/stores/ContentStore';
import { subscribeToTask } from '@/service/wsService';
import { useAppStore } from '@/stores/AppStore';
import { toast } from '@aminnausin/cedar-ui';
import { API } from '@/service/api';

export const resetSubtitles = (metadataId: number) => {
    return API.delete(`/metadata/${metadataId}/subtitles`, { headers: { 'X-Skip-Toast': 'true' } });
};

export const getFonts = (metadataId: number): Promise<AxiosResponse<{ fonts: FontResource[]; fonts_scanned_at?: string }>> => {
    return API.get(`/metadata/${metadataId}/fonts`, { headers: { 'X-Skip-Toast': 'true' } });
};

export const regenerateFonts = (metadataId: number): Promise<AxiosResponse<{ task_id: number; message?: string }>> => {
    return API.post(`/metadata/${metadataId}/fonts`, {}, { headers: { 'X-Skip-Toast': 'true' } });
};

export const runRegenerateFonts = async (videoId: number, metadataId: number) => {
    const contentStore = useContentStore();

    useAppStore().createEcho();

    const onComplete = async () => {
        try {
            const { data } = await getFonts(metadataId);

            const metadata = contentStore.stateVideo.metadata!;

            contentStore.updateVideoData({
                id: videoId,
                fonts: data.fonts?.map((font) => font.path) ?? [],
                metadata: {
                    ...metadata,
                    fonts_scanned_at: data.fonts_scanned_at,
                },
            });
        } catch (error) {
            toast.error('Failed to load fonts', { description: 'Refresh to see updated fonts' });
            console.error(error);
        }
    };

    try {
        const metadata = contentStore.stateVideo.metadata!;

        contentStore.updateVideoData({ id: videoId, fonts: [], metadata: { ...metadata, fonts_scanned_at: '...scanning' } });

        const result = await toast.promise(regenerateFonts(metadataId), {
            loading: 'Resetting Fonts',
            loadingDescription: `Clearing custom font cache`,
            success: 'Fonts Reset!',
            successDescription: 'Fonts reset and regeneration job queued',
            error: 'Failed to reset fonts',
            errorDescription: (err) => {
                const axiosErr = err as AxiosError<{ message?: string }>;
                return axiosErr.response?.data?.message ?? axiosErr.message;
            },
        });

        subscribeToTask(result.data.task_id, { onComplete });
    } catch (error: any) {
        const taskId = error?.response?.data?.task_id;

        if (error?.response?.status === 409 && taskId) {
            subscribeToTask(taskId, { onComplete });
            return;
        }

        const metadata = contentStore.stateVideo.metadata!;
        contentStore.updateVideoData({ id: videoId, fonts: [], metadata: { ...metadata, fonts_scanned_at: undefined } });

        console.error(error);
    }
};
