import type { StoryboardResource } from '@/contracts/media';
import type { AxiosResponse } from 'axios';

import { subscribeToTask } from '../wsService';
import { useContentStore } from '@/stores/ContentStore';
import { useAppStore } from '@/stores/AppStore';
import { toast } from '@aminnausin/cedar-ui';
import { API } from '../api';

export const regenerateStoryboard = (metadataId: number): Promise<AxiosResponse<{ task_id: number; message?: string }>> => {
    return API.post(`/metadata/${metadataId}/storyboard`, { headers: { 'X-Skip-Toast': 'true' } });
};
export const getStoryboard = (metadataId: number): Promise<AxiosResponse<StoryboardResource>> => {
    return API.get(`/metadata/${metadataId}/storyboard`);
};

export const runRegenerateStoryboard = async (videoId: number, metadataId: number) => {
    const contentStore = useContentStore();

    useAppStore().createEcho();

    const onComplete = async () => {
        try {
            const { data: storyboard } = await getStoryboard(metadataId);

            contentStore.updateVideoData({ id: videoId, storyboard });
        } catch (error) {
            toast.error('Failed to load storyboard', { description: 'Refresh to see updated storyboard' });
            console.error(error);
        }
    };

    try {
        const result = await toast.promise(regenerateStoryboard(metadataId), {
            loading: 'Resetting Storyboard',
            loadingDescription: `Deleting storyboard cache`,
            success: 'Storyboard Reset!',
            successDescription: 'Storyboard reset and regeneration job queued',
            error: 'Failed to reset storyboard',
        });

        contentStore.updateVideoData({ id: videoId, storyboard: undefined });
        subscribeToTask(result.data.task_id, { onComplete });
    } catch (error: any) {
        const taskId = error?.response?.data?.task_id;

        if (error?.response?.status === 409 && taskId) {
            toast.info('Already in progress', { description: 'Subscribing to existing job' });
            subscribeToTask(taskId, { onComplete });
            return;
        }

        console.error(error);
    }
};
