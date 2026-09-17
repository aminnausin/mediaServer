<script setup lang="ts">
import type { MediaMetadataEditorProps } from '@/types/modals.ts';
import type { Component } from 'vue';

import { handleEditMediaImages } from '@/service/media/mediaActions';
import { useContentStore } from '@/stores/ContentStore.ts';
import { getMediaDetails } from '@/service/media/media.ts';
import { useModalStore } from '@/stores/ModalStore';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ButtonBase } from '@/components/cedar-ui/button/index.ts';
import { BaseModal } from '@/components/cedar-ui/modal';
import { useQuery } from '@tanstack/vue-query';
import { useAuth } from '@/composables/auth/useAuth';
import { cn } from '@aminnausin/cedar-ui';

import MediaInfoSkeleton from '@/components/skeleton/MediaInfoSkeleton.vue';
import ModalFormFooter from '@/components/forms/ModalFormFooter.vue';
import EditItemHeader from '@/components/headers/EditItemHeader.vue';
import SubtitlesInfo from '@/components/media/SubtitlesInfo.vue';
import MetadataInfo from '@/components/media/MetadataInfo.vue';
import FontsInfo from '@/components/media/FontsInfo.vue';
import ImageInfo from '@/components/media/ImageInfo.vue';

import ProIconsPhoto from '@/components/icons/ProIconsPhoto.vue';

declare type MediaInfoTab = 'metadata' | 'images' | 'subtitles' | 'fonts';

const modal = useModalStore();

const { title, mediaResource: data } = modal.getProps<MediaMetadataEditorProps>();
const { stateDirectory } = storeToRefs(useContentStore());
const { isAuthenticated } = useAuth();

const mediaId = computed(() => data.id);

const tabs = computed<MediaInfoTab[]>(() => {
    const tabs: MediaInfoTab[] = ['metadata', 'images'];

    if (data.subtitles.length) tabs.push('subtitles');
    if (data.fonts?.length) tabs.push('fonts');

    return tabs;
});

const activeTab = ref<MediaInfoTab>(tabs.value.at(0) ?? 'metadata');
const panels: Record<MediaInfoTab, Component> = {
    metadata: MetadataInfo,
    images: ImageInfo,
    subtitles: SubtitlesInfo,
    fonts: FontsInfo,
};

const { data: mediaInfo, isLoading } = useQuery({
    queryKey: ['video-info', mediaId.value],
    queryFn: () => getMediaDetails(mediaId.value),
    enabled: computed(() => modal.isOpen),
});
</script>

<template>
    <BaseModal>
        <template #title>{{ title }}</template>
        <template #description v-if="data.edited_at && data.metadata?.editor_id && isAuthenticated">
            <EditItemHeader :edited_at="data.edited_at" :editor_id="data.metadata.editor_id" />
        </template>
        <div class="contents text-sm">
            <div class="bg-surface-3/50 dark:bg-surface-3 mr-auto flex w-fit gap-0.5 rounded-lg p-0.5">
                <ButtonBase
                    v-for="MediaInfoTab in tabs"
                    :key="MediaInfoTab"
                    :class="
                        cn('h-7 rounded-md px-3 py-1 capitalize transition-colors', {
                            'bg-surface-1 dark:bg-surface-4 text-primary-active dark:text-primary-muted shadow-sm': activeTab === MediaInfoTab,
                            'text-foreground-2 hover:text-foreground-0': activeTab !== MediaInfoTab,
                        })
                    "
                    @click="activeTab = MediaInfoTab"
                >
                    {{ MediaInfoTab }}
                </ButtonBase>
            </div>

            <MediaInfoSkeleton v-if="isLoading" />
            <component v-else :is="panels[activeTab]" :data="data" :media-info="mediaInfo" />

            <ModalFormFooter class="*:h-9" v-if="isAuthenticated">
                <ButtonBase
                    variant="transparent"
                    type="button"
                    class="text-foreground-2 hover:text-foreground-0 xs:-ms-1 xs:max-h-none xs:px-1 max-h-6 gap-1.5 p-0 text-xs transition-colors"
                    @click="() => {}"
                >
                    <ProIconsPhoto class="size-4" />
                    Edit Metadata
                </ButtonBase>
                <ButtonBase
                    variant="transparent"
                    type="button"
                    class="text-foreground-2 hover:text-foreground-0 xs:-ms-1 xs:mr-auto xs:max-h-none xs:px-1 max-h-6 gap-1.5 p-0 text-xs transition-colors"
                    @click="() => handleEditMediaImages(data, stateDirectory.id)"
                >
                    <ProIconsPhoto class="size-4" />
                    Edit Images
                </ButtonBase>
            </ModalFormFooter>
        </div>
    </BaseModal>
</template>
