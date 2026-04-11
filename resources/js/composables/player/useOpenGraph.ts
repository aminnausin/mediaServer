import type { FolderResource, VideoResource } from '@/contracts/media';
import type { Ref } from 'vue';

import { watch, onUnmounted } from 'vue';
import { MediaType } from '@/types/types';

export function useOpenGraph(folderData: Ref<FolderResource>, mediaData: Ref<VideoResource>) {
    const originalMetaTags = new Map();

    const setMetaTag = (property: string, content: string) => {
        // if already exists, save the old value
        let meta = document.querySelector(`meta[property="${property}"]`);

        if (!meta) {
            meta = document.createElement('meta');
            meta.setAttribute('property', property);
            document.head.appendChild(meta);
        } else {
            if (!originalMetaTags.has(property)) {
                originalMetaTags.set(property, meta.getAttribute('content'));
            }
        }

        meta.setAttribute('content', content);
    };

    const removeMetaTag = (property: string) => {
        const meta = document.querySelector(`meta[property="${property}"]`);
        if (meta) {
            meta.remove();
        }
    };

    const clearMetaTags = () => {
        ['og:title', 'og:type', 'video:series', 'video:season', 'video:episode'].forEach((property) => {
            removeMetaTag(property);
        });
    };

    // Unused
    const restoreOriginalTags = () => {
        originalMetaTags.forEach((content, property) => {
            const meta = document.querySelector(`meta[property="${property}"]`);

            if (content === null) return;

            if (meta && content !== null) {
                meta.setAttribute('content', content);
            } else if (meta && content === null) {
                meta.remove();
            }
        });
    };

    const updateOpenGraph = () => {
        if (mediaData.value.metadata?.media_type == MediaType.AUDIO) {
            clearMetaTags();
            return;
        }

        setMetaTag('og:title', `${folderData.value.title} Episode ${mediaData.value.episode}`);
        setMetaTag('og:type', 'video.episode');
        setMetaTag('video:series', folderData.value.title);
        setMetaTag('video:season', String(mediaData.value.season || 1));
        setMetaTag('video:episode', String(mediaData.value.episode));
    };

    watch(
        () => [mediaData.value.id, mediaData.value.episode, mediaData.value.season],
        () => {
            updateOpenGraph();
        },
    );

    onUnmounted(() => {
        clearMetaTags();
        // restoreOriginalTags();
    });

    return { updateOpenGraph, setMetaTag, removeMetaTag };
}
