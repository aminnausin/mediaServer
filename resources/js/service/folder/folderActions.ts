import type { FolderImageEditorProps } from '@/types/modals';
import type { FolderResource } from '@/contracts/media';

import { useModalStore } from '@/stores/ModalStore';
import { toast } from '@aminnausin/cedar-ui';

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
