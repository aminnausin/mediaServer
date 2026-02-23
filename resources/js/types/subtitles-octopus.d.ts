declare module '@jellyfin/libass-wasm' {
    export interface SubtitlesOctopusOptions {
        video: HTMLVideoElement;

        subUrl?: string;
        subContent?: string;

        workerUrl: string;
        wasmUrl?: string;

        fonts?: string[];
        availableFonts?: Record<string, string>;
        fallbackFont?: string;

        worker?: Worker;

        timeOffset?: number;
        playbackRate?: number;

        width?: number;
        height?: number;

        debug?: boolean;
        targetFps?: number;
        renderAhead?: number;

        onError?: () => void;
    }

    export default class SubtitlesOctopus {
        constructor(options: SubtitlesOctopusOptions);

        dispose(): void;

        setTrackByUrl(url: string): void;
        setTrack(content: string): void;
        freeTrack(): void;

        setCurrentTime(time: number): void;
        setIsPaused(paused: boolean): void;
        setRate(rate: number): void;
        resize(width?: number, height?: number): void;

        worker?: Worker;
    }
}
