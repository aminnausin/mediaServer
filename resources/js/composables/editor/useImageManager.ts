import type { ImageType } from '@/types/resources';

import { ref } from 'vue';

export interface PendingImage {
    tempId: string;
    file?: File;
    previewUrl: string;
    sourceUrl?: string;
    type: ImageType;
}

export function useImageManager() {
    const pending = ref<Partial<Record<ImageType, PendingImage>>>({});

    function addFile(file: File, type: ImageType): PendingImage {
        clearPendingEntry(type);

        const entry: PendingImage = {
            tempId: createTempId(),
            file,
            previewUrl: URL.createObjectURL(file),
            type,
        };

        pending.value = { ...pending.value, [type]: entry };
        return entry;
    }

    function addUrl(url: string, type: ImageType): PendingImage {
        clearPendingEntry(type);

        const entry: PendingImage = {
            tempId: createTempId(),
            sourceUrl: url,
            previewUrl: url,
            type,
        };

        pending.value = { ...pending.value, [type]: entry };
        return entry;
    }

    function removePending(type: ImageType) {
        clearPendingEntry(type);

        const next = { ...pending.value };
        delete next[type];
        pending.value = next;
    }

    function cleanup() {
        Object.values(pending.value).forEach((i) => {
            if (i?.file) URL.revokeObjectURL(i.previewUrl);
        });
        pending.value = {};
    }

    const clearPendingEntry = (type: ImageType) => {
        const entry = pending.value[type];

        if (entry?.file) URL.revokeObjectURL(entry.previewUrl);
    };

    // replaces crypto.randomUUID
    const createTempId = () => `${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;

    return { pending, addFile, addUrl, removePending, cleanup };
}
