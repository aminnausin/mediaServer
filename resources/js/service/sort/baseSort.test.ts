import type { VideoResource } from '@/types/resources';

import { CompareStrategies } from '@/service/sort/strategies';
import { sortObjectNew } from '@/service/sort/baseSort';
import { expect, test } from 'vitest';

test('CompareStrategies.episode sorts by season and episode', () => {
    const videos: VideoResource[] = [
        { id: 1, name: '1', path: '/1', date: '', video_tags: [], season: 2, episode: 2 },
        { id: 3, name: '3', path: '/3', date: '', video_tags: [], season: 2, episode: 1 },
        { id: 2, name: '2', path: '/2', date: '', video_tags: [], season: 1, episode: 1 },
        { id: 4, name: '4', path: '/4', date: '', video_tags: [], season: 1, episode: 2 },
    ];

    videos.sort(sortObjectNew([{ compareFn: CompareStrategies.episode }]));

    expect(videos).toEqual([
        { id: 2, name: '2', path: '/2', date: '', video_tags: [], season: 1, episode: 1 },
        { id: 4, name: '4', path: '/4', date: '', video_tags: [], season: 1, episode: 2 },
        { id: 3, name: '3', path: '/3', date: '', video_tags: [], season: 2, episode: 1 },
        { id: 1, name: '1', path: '/1', date: '', video_tags: [], season: 2, episode: 2 },
    ]);
});
