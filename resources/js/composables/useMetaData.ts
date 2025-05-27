import type { VideoResource } from '@/types/resources';

import { formatFileSize, toFormattedDuration } from '@/service/util';
import { MediaType } from '@/types/types';
import { useRoute } from 'vue-router';
import { reactive } from 'vue';

// This so does not work lol
export default function useMetaData(data: VideoResource, skipBaseURL: boolean = false) {
    const route = useRoute();

    const episodeTag = data.episode && data.metadata?.media_type === MediaType.AUDIO ? `${data.episode}. ` : '';
    const metadata = reactive({
        fields: {
            title: `${generateEpisodeTag(data)}${data?.title ?? data?.name}`,
            duration: toFormattedDuration(data?.duration) ?? 'N/A',
            views: data?.view_count ? `${data?.view_count} view${data?.view_count !== 1 ? 's' : ''}` : '0 views',
            description: data?.description ?? '',
            url: encodeURI((skipBaseURL ? '' : document.location.origin) + route.path + `?video=${data.id}`),
            file_size: data.file_size ? formatFileSize(data.file_size) : '',
        },
        updateData(props: VideoResource) {
            this.fields = {
                ...this.fields,
                title: `${generateEpisodeTag(props)}${props?.title ?? props?.name}`,
                duration: toFormattedDuration(props?.duration) ?? 'N/A',
                views: props?.view_count ? `${props?.view_count} view${props?.view_count !== 1 ? 's' : ''}` : '0 views',
                description: props?.description ?? '',
                url: encodeURI((skipBaseURL ? '' : document.location.origin) + route.path + `?video=${data.id}`),
                file_size: data.file_size ? formatFileSize(data.file_size) : '',
            };
        },
    });

    function generateEpisodeTag(episodeData: VideoResource) {
        return episodeData.episode && episodeData.metadata?.media_type === MediaType.AUDIO ? `${episodeData.episode}. ` : '';
    }

    return metadata;
}
