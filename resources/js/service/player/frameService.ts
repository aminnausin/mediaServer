import { toast } from '@aminnausin/cedar-ui';

export async function saveVideoFrame(video: HTMLVideoElement) {
    const blob = await getVideoFrame(video);

    if (!blob) {
        return;
    }

    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');

    a.href = url;
    a.download = `frame-${video.currentTime.toFixed(2)}s.png`;

    try {
        a.click();
        toast.success('Frame saved');
    } finally {
        URL.revokeObjectURL(url);
    }
}

export async function copyVideoFrame(video: HTMLVideoElement) {
    const blob = await getVideoFrame(video);

    if (!blob) {
        return;
    }

    try {
        await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
        toast.success('Frame copied');
    } catch (err) {
        toast.error('Copy failed', { description: 'Unable to write to clipboard. Clipboard permissions require a secure HTTPS context.' });
        console.error('Clipboard write failed', err);
    }
}

async function getVideoFrame(video: HTMLVideoElement): Promise<Blob | null> {
    const canvas = document.createElement('canvas');

    if (!video.videoWidth || !video.videoHeight) {
        toast.error('Capture failed', { description: 'Wait for the video to load metadata before capturing a frame.' });
        return null;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');

    if (!ctx) {
        toast.error('Capture failed', { description: 'Failed to get video information.' });
        return null;
    }

    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob((b) => resolve(b), 'image/png'));

    if (!blob) {
        toast.error('Capture failed', { description: 'Failed to generate frame.' });
        return null;
    }

    return blob;
}
