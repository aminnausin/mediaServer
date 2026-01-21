import { ref } from 'vue';

import SubtitlesOctopus from '@/lib/libass-wasm/subtitles-octopus';

export default function useOctopusRenderer() {
    const assInstance = ref<any>(null);

    const instantiateOctopus = async (subUrl: string = '/test/2.ass') => {
        clearOctopus();
        const video = document.getElementById('video-source');
        if (!video) return;
        const options = {
            video,
            subUrl,
            fonts: ['/fonts/Roboto-Medium.ttf', '/fonts/Arial.ttf', '/fonts/.fallback-default.woff2'],
            workerUrl: '/lib/subtitles-octopus/subtitles-octopus-worker.js',
            legacyWorkerUrl: '/lib/subtitles-octopus/subtitles-octopus-worker-legacy.js',
        };
        assInstance.value = new SubtitlesOctopus(options);
    };

    const clearOctopus = () => {
        if (assInstance.value && assInstance.value.worker) {
            assInstance.value.dispose();
            assInstance.value = null;
        }
    };

    const resizeOctopus = () => {
        if (assInstance.value && assInstance.value.worker) assInstance.value.resize();
    };

    return {
        instantiateOctopus,
        clearOctopus,
        resizeOctopus,
    };
}
