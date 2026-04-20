import type { VideoResource } from '@/types/resources';

import { CompareStrategies } from '@/service/sort/strategies';
import { sortObjectNew } from '@/service/sort/baseSort';
import { expect, test } from 'vitest';

test('CompareStrategies.episode sorts by season and episode', () => {
    const baseMedia = {
        view_count: 0,
        progress_offset: 0,
        progress_percentage: 0,
        completion_count: 0,
        video_tags: [],
        subtitles: [],
        created_at: '',
    };

    const media: VideoResource[] = [
        { id: 1, name: '1', path: '/1', season: 2, episode: 2, ...baseMedia },
        { id: 3, name: '3', path: '/3', season: 2, episode: 1, ...baseMedia },
        { id: 2, name: '2', path: '/2', season: 1, episode: 1, ...baseMedia },
        { id: 4, name: '4', path: '/4', season: 1, episode: 2, ...baseMedia },
    ];

    media.sort(sortObjectNew([{ compareFn: CompareStrategies.episode }]));

    expect(media).toEqual([
        { id: 2, name: '2', path: '/2', season: 1, episode: 1, ...baseMedia },
        { id: 4, name: '4', path: '/4', season: 1, episode: 2, ...baseMedia },
        { id: 3, name: '3', path: '/3', season: 2, episode: 1, ...baseMedia },
        { id: 1, name: '1', path: '/1', season: 2, episode: 2, ...baseMedia },
    ]);
});
