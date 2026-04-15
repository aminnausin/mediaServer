import type { GenericSortOption } from '@/types/types';
import type { CategoryResource, FolderResource } from '@/contracts/media';

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
