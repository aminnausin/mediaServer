import type { SubtitlesOctopusOptions } from '@jellyfin/libass-wasm';
import type SubtitlesOctopus from '@jellyfin/libass-wasm';

import { ref } from 'vue';

export default function useOctopusRenderer() {
    const assInstance = ref<SubtitlesOctopus | null>(null);

    const instantiateOctopus = async (subUrl: string, language?: string, frameRate?: number) => {
        clearOctopus();
        const video = document.getElementById('video-source') as HTMLVideoElement;
        if (!video) return;

        const baseFonts = ['/fonts/Rubik-Regular.ttf']; // Latin, Arabic, Cyrillic
        const supplementalFonts = generateLanguageFonts(language);

        import('@jellyfin/libass-wasm').then(({ default: SubtitlesOctopus }) => {
            const options: SubtitlesOctopusOptions = {
                video,
                subUrl,
                fonts: [...baseFonts, ...supplementalFonts],
                workerUrl: '/build/lib/subtitles-octopus/subtitles-octopus-worker.js',
                fallbackFont: '/fonts/NotoSans-Regular.ttf',
                onError(e?: any) {
                    console.log('Subtitles failed', e);
                    clearOctopus();
                },
                targetFps: frameRate || 24,
                renderAhead: 60,
            };
            assInstance.value = new SubtitlesOctopus(options);
        });
    };

    const clearOctopus = () => {
        if (assInstance.value?.worker) {
            // Supposedly the jellyfin version disposes itself on error?
            assInstance.value.dispose();
        }
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

    return {
        instantiateOctopus,
        clearOctopus,
        resizeOctopus,
    };
}
