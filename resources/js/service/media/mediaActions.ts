import type { MediaImageEditorProps } from '@/types/modals';
import type { VideoResource } from '@/contracts/media';

import { useModalStore } from '@/stores/ModalStore';
import { toast } from '@aminnausin/cedar-ui';

import EditMediaImagesModal from '@/components/modals/EditMediaImagesModal.vue';

export function handleEditMediaImages(media: VideoResource) {
    if (!media.metadata?.id) return toast.error('ID Missing');

    const modal = useModalStore();

    const metadataInfo = { titleTooltip: `UUID: ${media.metadata.uuid}` };
    modal.open<MediaImageEditorProps>(EditMediaImagesModal, {
        title: `Edit Media Images`,
        resource: media.metadata,
        images: media.metadata.images ?? [],
        ...metadataInfo,
    });
}
