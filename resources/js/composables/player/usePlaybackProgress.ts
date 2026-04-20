import type { Ref } from 'vue';

import { playbackProgressTrackingInterval } from '@/service/player/playerConstants';
import { getProgress, upsertProgress } from '@/service/api/playbackProgress';
import { watch, onUnmounted } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { useAuth } from '@/composables/auth/useAuth';

export function usePlaybackProgress(mediaId: Ref<number>, getCurrentTime: () => number) {
    const { isAuthenticated } = useAuth();
    const contentStore = useContentStore();

    let interval: ReturnType<typeof setInterval> | null = null;

    const save = async (id = mediaId.value) => {
        if (Number.isNaN(id)) return;

        const metadata = contentStore.getMetadataById(id);
        const duration = metadata?.duration ?? 0;

        const offset = Math.floor(getCurrentTime());
        const pct = duration ? Math.min(Math.round((offset / duration) * 100), 100) : 0;

        if (pct < 5) return;

        const { data } = await upsertProgress(id, {
            progress_offset: offset,
        });

        // this should use server-side resulting data from the upsert
        contentStore.updatePlaybackProgress(id, data);
    };

    const startInterval = () => {
        if (!isAuthenticated) {
            stopInterval();
            return;
        }

        if (interval) return;

        interval = setInterval(save, playbackProgressTrackingInterval);
    };

    const stopInterval = () => {
        if (interval) clearInterval(interval);
        interval = null;
    };

    const getResumeOffset = async (id = mediaId.value) => {
        if (Number.isNaN(id)) return null;

        const { data }: { data: { progress_offset?: number } } = await getProgress(id);
        return data?.progress_offset ?? null;
    };

    watch(mediaId, async (_, oldId) => {
        if (Number.isNaN(oldId)) return;
        stopInterval();
        await save(oldId); // Save previous media progress on switch
    });

    onUnmounted(() => {
        stopInterval();
        save(); // save on exit
    });

    return { startInterval, stopInterval, save, getResumeOffset };
}
