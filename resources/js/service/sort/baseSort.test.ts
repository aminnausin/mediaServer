import type { VideoResource } from '@/types/resources';

import { CompareStrategies } from '@/service/sort/strategies';
import { sortObjectNew } from '@/service/sort/baseSort';
import { expect, test } from 'vitest';

test('CompareStrategies.episode sorts by season and episode', () => {
    const videos: VideoResource[] = [
        { id: 1, name: '1', path: '/1', date: '', view_count: 0, video_tags: [], season: 2, episode: 2, date_created: '' },
        { id: 3, name: '3', path: '/3', date: '', view_count: 0, video_tags: [], season: 2, episode: 1, date_created: '' },
        { id: 2, name: '2', path: '/2', date: '', view_count: 0, video_tags: [], season: 1, episode: 1, date_created: '' },
        { id: 4, name: '4', path: '/4', date: '', view_count: 0, video_tags: [], season: 1, episode: 2, date_created: '' },
    ];

    videos.sort(sortObjectNew([{ compareFn: CompareStrategies.episode }]));

    expect(videos).toEqual([
        { id: 2, name: '2', path: '/2', date: '', view_count: 0, video_tags: [], season: 1, episode: 1, date_created: '' },
        { id: 4, name: '4', path: '/4', date: '', view_count: 0, video_tags: [], season: 1, episode: 2, date_created: '' },
        { id: 3, name: '3', path: '/3', date: '', view_count: 0, video_tags: [], season: 2, episode: 1, date_created: '' },
        { id: 1, name: '1', path: '/1', date: '', view_count: 0, video_tags: [], season: 2, episode: 2, date_created: '' },
    ]);
});
