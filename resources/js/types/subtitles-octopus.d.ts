declare module '@jellyfin/libass-wasm' {
    export interface SubtitlesOctopusOptions {
        video: HTMLVideoElement;

        // Performance
        dropAllAnimations?: boolean;
        libassMemoryLimit?: number;
        libassGlyphLimit?: number;

        // Rendering
        targetFps?: number;
        prescaleFactor?: number;
        prescaleHeightLimit?: number; // Default 1080
        maxRenderHeight?: number; // Default 0 (no limit)
        resizeVariation?: number;
        renderAhead?: number; // Render ahead by x MiB

        // Fonts
        fonts: string[];
        availableFonts?: { [fontNameLowercase: string]: string };
        fallbackFont?: string;

        // Events
        onReady?: () => void;
        onError?: (error: any) => void;

        // Resources
        subUrl: string;
        workerUrl: string;

        // Other
        debug?: boolean;
        timeOffset?: number;
    }

    export default class SubtitlesOctopus {
        constructor(options: SubtitlesOctopusOptions);

        dispose(): void;
        freeTrack(): void;

        setTrackByUrl(url: string): void;
        setTrack(content: string): void;

        setCurrentTime(time: number): void;
        setIsPaused(paused: boolean): void;
        setRate(rate: number): void;

        resize(width?: number, height?: number): void;
        resizeWithTimeout(): void;
        resetRenderAheadCache(isResizing?: boolean): void;

        worker?: Worker;
    }
}
