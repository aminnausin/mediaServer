import type { FolderImageEditorProps, FolderRelationEditorProps } from '@/types/modals';
import type { FolderResource } from '@/contracts/media';

import { useModalStore } from '@/stores/ModalStore';
import { toast } from '@aminnausin/cedar-ui';

import EditFolderRelationsModal from '@/components/modals/EditFolderRelationsModal.vue';
import EditFolderImagesModal from '@/components/modals/EditFolderImagesModal.vue';

export function handleEditFolderImages(folder: FolderResource) {
    if (!folder.series?.id || !folder.series?.uuid) return toast.error('ID Missing');

    const modal = useModalStore();

    const tooltipInfo = { titleTooltip: `UUID: ${folder.series.uuid}` };
    modal.open<FolderImageEditorProps>(EditFolderImagesModal, {
        title: `Edit Folder Images`,
        resource: folder.series,
        folderResource: folder,
        images: folder.series.images ?? [],
        isMajorityAudio: folder.is_majority_audio,
        ...tooltipInfo,
    });
}

export function handleEditRelations(folder?: FolderResource) {
    if (!folder || !folder.series?.id || !folder.series?.uuid) return toast.error('ID Missing');

    const modal = useModalStore();

    const series = folder.series;
    const tooltipInfo = { titleTooltip: `UUID: ${series.uuid}` };
    modal.open<FolderRelationEditorProps>(EditFolderRelationsModal, {
        resource: series,
        title: `Edit Folder Relations`,
        seriesId: series.id,
        relations: series.related_series ?? [],
        ...tooltipInfo,
    });
}
