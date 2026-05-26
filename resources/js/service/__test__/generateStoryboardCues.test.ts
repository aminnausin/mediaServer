import type { StoryboardResource } from '@/contracts/media';

import { describe, it, expect } from 'vitest';
import { buildStoryboardCues } from '@/service/storyboard';

const makeStoryboard = (overrides: Partial<StoryboardResource> = {}): StoryboardResource => ({
    tile_rows: 10,
    tile_cols: 10,
    tile_width: 320,
    tile_height: 180,
    interval_seconds: 10,
    ...overrides,
});

const UUID = 'abc12345-uuid';

describe('buildStoryboardCues', () => {
    it('generates correct number of cues', () => {
        const cues = buildStoryboardCues(UUID, 100, makeStoryboard());
        expect(cues.length).toBe(10);
    });

    it('first cue starts at 0', () => {
        const cues = buildStoryboardCues(UUID, 100, makeStoryboard());
        expect(cues[0].start).toBe(0);
    });

    it('last cue is padded by half of interval', () => {
        const storyboard = makeStoryboard();
        const cues = buildStoryboardCues(UUID, 95, storyboard);
        expect(cues.at(-1)?.end).toBeLessThanOrEqual(95 + storyboard.interval_seconds / 2);
    });

    it('first tile has x=0 y=0', () => {
        const cues = buildStoryboardCues(UUID, 100, makeStoryboard());
        expect(cues[0].x).toBe(0);
        expect(cues[0].y).toBe(0);
    });

    it('second tile has correct x offset', () => {
        const cues = buildStoryboardCues(UUID, 200, makeStoryboard());
        expect(cues[1].x).toBe(320);
        expect(cues[1].y).toBe(0);
    });

    it('eleventh tile wraps to second row', () => {
        const cues = buildStoryboardCues(UUID, 200, makeStoryboard());
        expect(cues[10].x).toBe(0);
        expect(cues[10].y).toBe(180);
    });

    it('rolls over to next sheet after 100 tiles', () => {
        const cues = buildStoryboardCues(UUID, 1100, makeStoryboard());
        expect(cues[99].image).toContain('/1.jpg');
        expect(cues[100].image).toContain('/2.jpg');
    });

    it('image path uses first two chars of uuid', () => {
        const cues = buildStoryboardCues(UUID, 10, makeStoryboard());
        expect(cues[0].image).toContain('/ab/');
    });

    it('cue dimensions match storyboard tile size', () => {
        const cues = buildStoryboardCues(UUID, 10, makeStoryboard());
        expect(cues[0].w).toBe(320);
        expect(cues[0].h).toBe(180);
    });
});
