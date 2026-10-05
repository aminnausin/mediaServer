import { timestampToSeconds } from '@/components/video/transcript/transcriptUtil';

const STRIP_TAGS = /<(?!\/?[iu]>|br\s*\/?>)[^<>]*>/gi;

export interface TranscriptLine {
    index: number;
    text: string;
    start: number;
    end: number;
}

export function parseTranscript(rawTranscript: string): TranscriptLine[] {
    const lines: TranscriptLine[] = [];
    const cueLines = rawTranscript.replace(/\r\n?/g, '\n').split('\n');

    for (let i = 0; i < cueLines.length; i++) {
        const line = cueLines[i].trim();
        if (!line.includes(' --> ')) continue;

        const [start, rest = ''] = line.split(' --> ');
        const end = rest.trim().split(/\s+/)[0];

        const textLines: string[] = [];
        i++;

        while (i < cueLines.length && cueLines[i].trim() !== '') {
            textLines.push(cueLines[i]);
            i++;
        }

        const startTime = timestampToSeconds(start);
        const endTime = timestampToSeconds(end);
        const text = textLines.join('\n').trim().replace(STRIP_TAGS, '');

        if (!text) continue;

        const previous = lines.at(-1);
        if (previous?.text === text && startTime <= previous.end) {
            previous.end = Math.max(previous.end, endTime);
            continue;
        }

        lines.push({ start: startTime, end: endTime, text, index: lines.length });
    }
    return lines;
}
