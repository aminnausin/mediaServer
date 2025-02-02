import type { VideoResource } from '@/types/resources';

import { formatFileSize, toFormattedDuration } from '@/service/util';
import { useRoute } from 'vue-router';
import { reactive } from 'vue';

// This so does not work lol
export default function useMetaData(data: VideoResource, skipBaseURL: boolean = false) {
    const route = useRoute();

    const metadata = reactive({
        fields: {
            title: data?.title ?? data?.name,
            duration: toFormattedDuration(data?.duration) ?? 'N/A',
            views: data?.view_count ? `${data?.view_count} View${data?.view_count !== 1 ? 's' : ''}` : '0 Views',
            description: data?.description ?? '',
            url: encodeURI((skipBaseURL ? '' : document.location.origin) + route.path + `?video=${data.id}`),
            file_size: data.file_size ? formatFileSize(data.file_size) : '',
        },
        updateData(props: VideoResource) {
            this.fields = {
                ...this.fields,
                title: props?.title ?? props?.name,
                duration: toFormattedDuration(props?.duration) ?? 'N/A',
                views: props?.view_count ? `${props?.view_count} View${props?.view_count !== 1 ? 's' : ''}` : '0 Views',
                description: props?.description ?? '',
                url: encodeURI((skipBaseURL ? '' : document.location.origin) + route.path + `?video=${data.id}`),
                file_size: data.file_size ? formatFileSize(data.file_size) : '',
            };
        },
    });

    return metadata;
}
