import type { FolderResource, VideoResource } from '@/contracts/media';
import type { ComputedRef, Ref } from 'vue';

import { computed } from 'vue';

export type ActivityItemType = 'video' | 'audio' | 'folder';

export interface ActivityItem {
    id: string;
    type: ActivityItemType;
    title: string;
    subtitle: string;
    timestamp: string;
    thumbnail?: string;
    url: string;
    isNew: boolean;
    isAudio?: boolean;
}

const isRecent = (timestamp?: string) => !!timestamp && Date.now() - new Date(timestamp).getTime() < 1000 * 60 * 60 * 24; // 24h

const episodeLabel = (media: VideoResource) => {
    if (media.episode != null) return `Episode ${media.episode}`;
    return 'New video';
};

const videoToActivity = (media: VideoResource): ActivityItem => {
    const timestamp = media.season && media.episode ? (media.metadata?.file_modified_at ?? media.created_at) : media.created_at;
    return {
        id: `video-${media.id}`,
        type: 'video',
        title: media.title ?? media.name,
        subtitle: episodeLabel(media),
        timestamp,
        thumbnail: media.metadata?.poster_image?.path,
        url: media.url ?? '/',
        isNew: isRecent(timestamp),
    };
};

const audioToActivity = (media: VideoResource): ActivityItem => ({
    id: `audio-${media.id}`,
    type: 'audio',
    title: media.title ?? media.name,
    subtitle: [media.artist, media.album].filter(Boolean).join(' · ') || 'New track',
    timestamp: media.metadata?.file_modified_at ?? media.created_at,
    thumbnail: media.metadata?.poster_image?.path,
    url: media.url ?? '/',
    isNew: isRecent(media.metadata?.file_modified_at ?? media.created_at),
});

const folderToActivity = (folder: FolderResource): ActivityItem => ({
    id: `folder-${folder.id}`,
    type: 'folder',
    title: folder.title,
    subtitle: 'Just updated',
    timestamp: folder.series?.updated_at ?? folder.created_at ?? '',
    thumbnail: folder.series?.poster_image?.path,
    url: `/${folder.category_id}/${folder.id}/details`,
    isNew: isRecent(folder.series?.updated_at),
    isAudio: folder.is_majority_audio,
});

export const useActivityFeed = (
    videos: Ref<VideoResource[] | undefined> | ComputedRef<VideoResource[] | undefined>,
    music: Ref<VideoResource[] | undefined> | ComputedRef<VideoResource[] | undefined>,
    updatedFolders: Ref<FolderResource[] | undefined> | ComputedRef<FolderResource[] | undefined>,
    limit = 14,
) =>
    computed<ActivityItem[]>(() => {
        const items = [...(videos.value ?? []).map(videoToActivity), ...(music.value ?? []).map(audioToActivity), ...(updatedFolders.value ?? []).map(folderToActivity)].filter(
            (item) => item.timestamp,
        );

        return items.sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime()).slice(0, limit);
    });

export const groupLabel = (timestamp: string): string => {
    const date = new Date(timestamp);
    const now = new Date();
    const startOf = (d: Date) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    const diffDays = Math.round((startOf(now) - startOf(date)) / 86400000);

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
};

/**
 * Groups feed items by their generated group label based on their timestamp
 * @param items
 * @returns { label: string; items: ActivityItem[] }[]
 */
export const groupActivityFeed = (items: ActivityItem[]): { label: string; items: ActivityItem[] }[] => {
    const groups: { label: string; items: ActivityItem[] }[] = [];

    for (const item of items) {
        const label = groupLabel(item.timestamp);
        const last = groups[groups.length - 1];
        if (last?.label === label) last.items.push(item);
        else groups.push({ label, items: [item] });
    }

    return groups;
};
