import type { SubtitlesOctopusOptions } from '@jellyfin/libass-wasm';
import type { SubtitleResource } from '@/contracts/media';
import type SubtitlesOctopus from '@jellyfin/libass-wasm';

import { toast } from '@aminnausin/cedar-ui';
import { ref } from 'vue';

export default function useOctopusRenderer() {
    const assInstance = ref<SubtitlesOctopus | null>(null);
    const abortController = ref<AbortController | null>(null);

    const instantiateOctopus = async (nextTrack: SubtitleResource, frameRate?: number) => {
        const video = document.getElementById('video-source') as HTMLVideoElement;
        if (!video) return;
        if (assInstance.value) clearOctopus();

        abortController.value?.abort();
        abortController.value = new AbortController();
        const signal = abortController.value.signal;

        const languageTag = nextTrack.track_id === 0 ? `.${nextTrack.language}` : '';
        const subUrl = `/data/subtitles/${nextTrack.metadata_uuid}/${nextTrack.track_id}${languageTag}.ass`;

        const baseFonts = ['/fonts/Rubik-Regular.ttf', '/fonts/KleeOne-Regular.ttf']; // Latin, Arabic, Cyrillic
        const supplementalFonts = generateLanguageFonts(nextTrack.language);

        const trackTitle = nextTrack.title ? formatSubtitleTitle(nextTrack.title) : `subtitles track ${nextTrack.track_id}`;

        try {
            const response = await toast.promise(
                fetch(subUrl, { signal }),
                {
                    loading: `Loading ${trackTitle}...`,
                    success: `Loaded ${trackTitle}`,
                    error: `Failed to load ${trackTitle}`,
                },
                { description: 'Initial load may take a few seconds.' },
            );

            if (!response.ok || signal.aborted) return;

            // This await continues even if this instance of octopus renderer is cancelled like if im loading subs for a video and go to another video, this is never cancelled

            import('@jellyfin/libass-wasm').then(({ default: SubtitlesOctopus }) => {
                if (signal.aborted) return;

                const options: SubtitlesOctopusOptions = {
                    video,
                    subUrl,
                    fonts: [...baseFonts, ...supplementalFonts],
                    workerUrl: '/build/lib/subtitles-octopus/subtitles-octopus-worker.js',
                    fallbackFont: '/fonts/NotoSans-Regular.ttf',
                    onError(e?: any) {
                        toast.error('Subtitles Failed', { description: e?.message ?? undefined });
                        clearOctopus();
                    },
                    targetFps: frameRate || 24,
                };
                assInstance.value = new SubtitlesOctopus(options);
            });
        } catch (e) {
            if (e instanceof DOMException && e.name === 'AbortError') return;
            console.error(e);
        }
    };

    const clearOctopus = () => {
        // Supposedly the jellyfin version disposes itself on error?
        assInstance.value?.dispose();
        assInstance.value = null;
    };

    const resizeOctopus = () => {
        if (assInstance.value?.worker) assInstance.value.resize();
    };

    /**
     * Generates a list of language specific fonts based on a predetermined list of files
     *
     * Supports Thai, Japanese, Chinese
     *
     * @param language Language code
     * @returns string[] Language specific fonts
     */
    const generateLanguageFonts = (language?: string): string[] => {
        switch (language?.toLowerCase()) {
            case 'tha':
                return ['/fonts/NotoSansThai-Regular.ttf'];
            case 'jpn':
                return ['/fonts/KleeOne-Regular.ttf'];
            case 'cn':
            case 'chi':
            case 'tc':
            case 'sc':
                return ['/fonts/NotoSansSC-Regular.ttf', '/fonts/NotoSansTC-Regular.ttf'];
            default:
                return [];
        }
    };

    const formatSubtitleTitle = (title: string): string => {
        if (title.toLowerCase().includes('subtitles')) return title;
        return `${title} subtitles`;
    };

    return {
        instantiateOctopus,
        clearOctopus,
        resizeOctopus,
    };
}
