import type { SubtitleResource } from '@/contracts/media';
import type { TranscriptLine } from '@/components/video/transcript/transcriptParser';

export function timestampToSeconds(timestamp: string): number {
    const parts = timestamp.trim().split(':').map(Number);
    if (parts.length === 2) return parts[0] * 60 + parts[1];
    if (parts.length === 3) return parts[0] * 3600 + parts[1] * 60 + parts[2];
    return 0;
}

export function buildTranscriptUrl(subtitle?: SubtitleResource): string | undefined {
    if (!subtitle) return undefined;

    const { metadata_uuid, track_id, language } = subtitle;
    const languageSlug = track_id === 0 ? `.${language}` : '';

    return `/data/subtitles/${metadata_uuid}/${track_id}${languageSlug}.vtt`;
}

export function findActiveTranscriptIndex(lines: TranscriptLine[], time: number): number {
    let low = 0;
    let high = lines.length - 1;

    while (low <= high) {
        const mid = (low + high) >> 1;
        const line = lines[mid];
        const next = lines[mid + 1];

        if (time < line.start) high = mid - 1;
        else if (!next || time < next.start) return mid;
        else low = mid + 1;
    }

    return -1;
}

export function formatTimestamp(totalSeconds: number, duration = 0): string {
    const total = Math.floor(totalSeconds);
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = String(total % 60).padStart(2, '0');

    if (duration >= 3600) return `${h}:${String(m).padStart(2, '0')}:${s}`;
    if (duration >= 600) return `${String(m).padStart(2, '0')}:${s}`;
    return `${m}:${s}`;
}
