import { describe, it, vi, expect, beforeEach } from 'vitest';
import { saveVideoFrame, copyVideoFrame } from './frameService';
import { toast } from '../toaster/toastService';

vi.mock('../toaster/toastService', () => ({
    toast: {
        success: vi.fn(),
        error: vi.fn(),
    },
}));

function createMockVideo({ width = 1280, height = 720, currentTime = 12.34 } = {}): HTMLVideoElement {
    return {
        videoWidth: width,
        videoHeight: height,
        currentTime,
    } as HTMLVideoElement;
}

function setupCanvasMock() {
    vi.spyOn(HTMLCanvasElement.prototype, 'getContext').mockImplementation(() => {
        return {
            drawImage: vi.fn(),
        } as unknown as CanvasRenderingContext2D;
    });

    vi.spyOn(HTMLCanvasElement.prototype, 'toBlob').mockImplementation(function (cb) {
        cb?.(new Blob(['mock'], { type: 'image/png' }));
    });
}

describe('frameService', () => {
    beforeEach(() => {
        vi.resetAllMocks();
        vi.mock('canvas', () => ({}));

        setupCanvasMock();

        globalThis.ClipboardItem ??= class ClipboardItem {
            static supports(type: string): boolean {
                return type === 'image/png';
            }

            constructor(public items: Record<string, Blob>) {}
        } as unknown as typeof ClipboardItem;
        globalThis.URL.createObjectURL ??= vi.fn(() => 'blob:mock-url');
        globalThis.URL.revokeObjectURL ??= vi.fn();
    });

    describe('saveVideoFrame', () => {
        it('saves the frame and shows a success toast', async () => {
            const video = createMockVideo();
            await saveVideoFrame(video);

            expect(toast.success).toHaveBeenCalledWith('Frame saved');
        });

        it('shows error if frame generation fails', async () => {
            vi.spyOn(HTMLCanvasElement.prototype, 'toBlob').mockImplementation((cb) => cb?.(null));

            const video = createMockVideo();
            await saveVideoFrame(video);

            expect(toast.error).toHaveBeenCalledWith('Capture failed', {
                description: 'Failed to generate frame.',
            });
        });
    });

    describe('copyVideoFrame', () => {
        it('copies the frame and shows a success toast', async () => {
            const writeMock = vi.fn().mockResolvedValue(undefined);
            Object.assign(navigator, {
                clipboard: {
                    write: writeMock,
                },
            });

            const video = createMockVideo();
            await copyVideoFrame(video);

            expect(writeMock).toHaveBeenCalled();
            expect(toast.success).toHaveBeenCalledWith('Frame copied');
        });

        it('shows error if clipboard write fails', async () => {
            const writeMock = vi.fn().mockRejectedValue(new Error('Clipboard fail'));
            Object.assign(navigator, {
                clipboard: {
                    write: writeMock,
                },
            });

            const video = createMockVideo();
            await copyVideoFrame(video);

            expect(toast.error).toHaveBeenCalledWith('Copy failed', {
                description: 'Unable to write to clipboard. Clipboard permissions require a secure HTTPS context.',
            });
        });
    });

    describe('getVideoFrame', () => {
        it('shows error if video is not ready', async () => {
            const video = createMockVideo({ width: 0, height: 0 });
            await saveVideoFrame(video);
            expect(toast.error).toHaveBeenCalledWith('Capture failed', {
                description: 'Wait for the video to load metadata before capturing a frame.',
            });
        });

        it('shows error if getContext returns null', async () => {
            vi.spyOn(document, 'createElement').mockImplementation((tag) => {
                if (tag === 'canvas') {
                    return {
                        getContext: () => null,
                    } as unknown as HTMLCanvasElement;
                }
                return document.createElement(tag);
            });

            const video = createMockVideo();
            await saveVideoFrame(video);

            expect(toast.error).toHaveBeenCalledWith('Capture failed', {
                description: 'Failed to get video information.',
            });
        });
    });
});
