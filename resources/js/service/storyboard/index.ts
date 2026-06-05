import type { StoryboardResource } from '@/contracts/media';
import type { StoryboardCue } from './types';

export function buildStoryboardCues(uuid: string, durationSeconds: number, storyboard: StoryboardResource): StoryboardCue[] {
    const { tile_rows: rows, tile_cols: cols, tile_height: tileHeight, tile_width: tileWidth, interval_seconds: interval } = storyboard;

    const tilesPerSheet = rows * cols;
    const cues: StoryboardCue[] = [];

    const dir = storyboard.path ?? `/storage/metadata/metadata/${uuid.slice(0, 2)}/${uuid}/storyboard`;

    for (let t = 0; t < durationSeconds; t += interval) {
        const index = Math.floor(t / interval);
        const sheet = Math.floor(index / tilesPerSheet) + 1;
        const tileIndex = index % tilesPerSheet;
        const col = tileIndex % cols;
        const row = Math.floor(tileIndex / cols);

        const padding = durationSeconds < t + interval + interval ? interval / 2 : 0; // for last cue

        cues.push({
            start: t,
            end: Math.min(t + interval, durationSeconds) + padding,
            image: `${dir}/${sheet}.jpg`,
            x: col * tileWidth,
            y: row * tileHeight,
            w: tileWidth,
            h: tileHeight,
        });
    }

    return cues;
}
