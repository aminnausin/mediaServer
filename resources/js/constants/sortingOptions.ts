import type { CategoryResource, FolderResource, VideoResource } from '@/contracts/media';
import type { GenericSortOption } from '@/types/types';

export const librarySortingOptions: GenericSortOption<CategoryResource>[] = [
    {
        title: 'Title',
        value: 'name',
    },
    {
        title: 'Date',
        value: 'created_at',
    },
    {
        title: 'Folders',
        value: 'folders_count',
    },
    {
        title: 'Files',
        value: 'videos_count',
    },
    {
        title: 'Size',
        value: 'total_size',
    },
];

export const folderSortingOptions: GenericSortOption<FolderResource>[] = [
    {
        title: 'Title',
        value: 'name',
    },
    {
        title: 'Date Created',
        value: 'created_at',
    },
    {
        title: 'Date Updated',
        value: 'updated_at',
    },
    {
        title: 'Size',
        value: 'total_size',
    },
    {
        title: 'File Count',
        value: 'file_count',
    },
];

export const mediaSortingOptions = (folder: FolderResource): GenericSortOption<VideoResource>[] => [
    {
        title: 'Title',
        value: 'title',
        disabled: false,
    },
    {
        title: 'Date Uploaded',
        value: 'file_modified_at',
        disabled: false,
    },
    {
        title: 'Date Released',
        value: 'released_at',
        disabled: false,
    },
    {
        title: 'Views',
        value: 'view_count',
        disabled: false,
    },
    {
        title: 'Artist',
        value: 'artist',
        disabled: !folder.is_majority_audio,
        hidden: !folder.is_majority_audio,
    },
    {
        title: 'Album',
        value: 'album',
        disabled: !folder.is_majority_audio,
        hidden: !folder.is_majority_audio,
    },
    {
        title: folder.is_majority_audio ? 'Track Number' : `Episode`,
        value: 'episode',
        disabled: false,
    },
    {
        title: folder.is_majority_audio ? 'Disc Number' : 'Season',
        value: 'season',
        disabled: false,
    },
    {
        title: 'Duration',
        value: 'duration',
        disabled: false,
    },
    {
        title: 'File Size',
        value: 'file_size',
        disabled: false,
    },
    {
        title: 'Watch Progress',
        value: 'progress_percentage',
    },
    {
        title: 'Times Completed',
        value: 'completion_count',
    },
];
