import type { SubtitlesOctopusOptions } from '@jellyfin/libass-wasm';
import type SubtitlesOctopus from '@jellyfin/libass-wasm';
import { ref } from 'vue';

export default function useOctopusRenderer() {
    const assInstance = ref<SubtitlesOctopus | null>(null);

    const instantiateOctopus = async (subUrl: string) => {
        clearOctopus();
        const video = document.getElementById('video-source') as HTMLVideoElement;
        if (!video) return;

        import('@jellyfin/libass-wasm').then(({ default: SubtitlesOctopus }) => {
            const options: SubtitlesOctopusOptions = {
                video,
                subUrl,
                fonts: ['/fonts/Roboto-Medium.ttf', '/fonts/Rubik-Regular.ttf', '/fonts/KleeOne-Regular.ttf'],
                workerUrl: '/build/lib/subtitles-octopus/subtitles-octopus-worker.js',
                fallbackFont: '/fonts/DejaVuSans.ttf',
                onError(e?: any) {
                    console.log('Subtitles failed', e);
                    clearOctopus();
                },
            };
            assInstance.value = new SubtitlesOctopus(options);
        });
    };

    const clearOctopus = () => {
        if (assInstance.value?.worker) {
            assInstance.value.dispose();
            assInstance.value = null;
        }
    };

    const resizeOctopus = () => {
        if (assInstance.value?.worker) assInstance.value.resize();
    };

    return {
        instantiateOctopus,
        clearOctopus,
        resizeOctopus,
    };
}
