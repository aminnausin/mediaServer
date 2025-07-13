import type { VideoResource } from '@/types/resources';
import type { Ref } from 'vue';

import { formatFileSize, toFormattedDuration } from '@/service/util';
import { MediaType } from '@/types/types';
import { useRoute } from 'vue-router';
import { computed, reactive } from 'vue';

// This so does not work lol

export default function useMetaData(data: Ref<VideoResource>, skipBaseURL: boolean = false) {
    const route = useRoute();

    const title = computed(() => `${generateEpisodeTag(data.value)}${data.value.title ?? data.value.name}`);
    const duration = computed(() => toFormattedDuration(data.value.duration) ?? 'N/A');
    const views = computed(() => generateViewsTag(data.value.view_count));
    const description = computed(() => generateDescription(data.value.description ?? ''));
    const url = computed(() => encodeURI((skipBaseURL ? '' : document.location.origin) + route.path + `?video=${data.value.id}`));
    const fileSize = computed(() => (data.value.file_size ? formatFileSize(data.value.file_size) : ''));

    function generateEpisodeTag(episodeData: VideoResource) {
        return episodeData.episode && episodeData.metadata?.media_type === MediaType.AUDIO ? `${episodeData.episode}. ` : '';
    }

    function generateViewsTag(viewCount: number = 0) {
        return `${viewCount} view${viewCount !== 1 ? 's' : ''}`;
    }

    function generateDescription(description: string) {
        const parts: { type: 'text' | 'timestamp'; text?: string; raw?: string; seconds?: number }[] = [];

        let lastIndex = 0;
        let match: RegExpExecArray | null;

        const regex = /(?:(\d{1,2}):)?(\d{1,2}):(\d{2}(?:\.\d+)?)/g;

        while ((match = regex.exec(description)) !== null) {
            const [full, hour, min, sec] = match;
            const start = match.index;
            const end = regex.lastIndex;

            if (start > lastIndex) {
                parts.push({ type: 'text', text: description.slice(lastIndex, start) });
            }

            const seconds = parseInt(hour ?? '0') * 3600 + parseInt(min) * 60 + parseFloat(sec);

            parts.push({ type: 'timestamp', raw: full, seconds });

            lastIndex = end;
        }

        if (lastIndex < description.length) {
            parts.push({ type: 'text', text: description.slice(lastIndex) });
        }

        return parts;
    }

    return {
        title,
        duration,
        views,
        description,
        url,
        fileSize,
    };
}
