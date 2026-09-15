<script setup lang="ts">
import type { FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate, toPlural, toTimeSpan } from '@/service/util';
import { computed, ref, useTemplateRef } from 'vue';
import { setSeriesDownloadSettings } from '@/service/siteAPI';
import { useDashboardStore } from '@/stores/DashboardStore';
import { useQueryClient } from '@tanstack/vue-query';
import { BasePopover } from '@/components/cedar-ui/popover';
import { storeToRefs } from 'pinia';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { HoverCard } from '@/components/cedar-ui/hover-card';
import { cn, toast } from '@aminnausin/cedar-ui';

import LibraryFolderCardMenu from '@/components/menus/LibraryFolderCardMenu.vue';
import PlayerOSDBase from '@/components/video/OSD/PlayerOSDBase.vue';
import BlurhashImage from '@/components/lazy/BlurhashImage.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import TablerDownload from '@/components/icons/TablerDownload.vue';
import ProiconsStar from '~icons/proicons/star';
import IconShare from '@/components/icons/IconShare.vue';
import IconMusic from '@/components/icons/IconMusic.vue';
import IconPlay from '@/components/icons/IconPlay.vue';

const { stateLibraryId, stateLibraries } = storeToRefs(useDashboardStore());

const props = defineProps<{ data: FolderResource }>();
const popover = useTemplateRef('popover');

const queryClient = useQueryClient();
const processing = ref(false);

const stateLibrary = computed(() => stateLibraries.value.find((lib) => lib.id === stateLibraryId.value));
const formattedDate = computed(() => ({
    short: props.data.created_at ? toFormattedDate(props.data.created_at, false, { year: 'numeric', month: '2-digit', day: '2-digit' }) : 'N/A',
    long: props.data.created_at ? toFormattedDate(props.data.created_at) : 'N/A',
}));

const handleToggleDownloads = async (id: number, currentValue: boolean) => {
    if (processing.value || !props.data.id || currentValue !== props.data.series?.downloads_enabled) return;

    try {
        processing.value = true;

        await setSeriesDownloadSettings(id, { downloads_enabled: !currentValue });
        await queryClient.invalidateQueries({ queryKey: ['auth-only', 'libraryFolders', stateLibraryId.value] });

        toast.success(`${currentValue ? 'Disabled' : 'Enabled'} Folder Downloads.`);
        processing.value = false;
    } catch (error) {
        toast('Failure', { type: 'danger', description: 'Unable to set download settings.' });
        console.error(error);
        processing.value = false;
    }
};
</script>

<template>
    <div :class="cn('data-card group transition-input library-card')">
        <RouterLink :to="`/${encodeURI(data.path)}/details`" class="content-auto h-40 w-full rounded-t-lg [contain-intrinsic-size:auto_160px] focus:-outline-offset-2">
            <BlurhashImage
                class="mb-auto size-full object-cover"
                alt="Folder Cover Art"
                :src="data.series?.poster_image?.path ?? data.series?.banner_image?.path ?? handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                :blurhash="data.series?.poster_image?.blur_hash ?? data.series?.banner_image?.blur_hash"
            />

            <div v-if="stateLibrary?.default_folder_id === data.id" class="absolute inset-x-0 top-2 z-1 flex items-start px-1">
                <PlayerOSDBase class="flex w-fit items-center justify-start gap-2 px-2 py-0.5 text-white backdrop-blur-sm">
                    <HoverCard
                        :content-title="'Downloadable Folder'"
                        :content="`${stateLibrary?.downloads_require_auth ? 'Only authenticated users' : 'Any user'} can download from this folder.`"
                        :disabled="true"
                    >
                        <template #trigger>
                            <p class="text-xs">Default</p>
                        </template>
                    </HoverCard>
                </PlayerOSDBase>
            </div>

            <div v-if="data.series?.downloads_enabled && stateLibrary?.downloads_enabled" class="absolute inset-x-0 bottom-2 z-1 flex items-end px-2.5">
                <PlayerOSDBase class="ml-auto flex w-fit items-center justify-start gap-2 p-1 text-white backdrop-blur-sm">
                    <HoverCard
                        :content-title="'Downloadable Folder'"
                        :content="`${stateLibrary?.downloads_require_auth ? 'Only authenticated users' : 'Any user'} can download from this folder.`"
                        v-show="data.series?.downloads_enabled && stateLibrary?.downloads_enabled"
                    >
                        <template #trigger>
                            <TablerDownload class="peer size-4.5 shrink-0" :title="'Downloads enabled'" />
                        </template>
                    </HoverCard>
                </PlayerOSDBase>
            </div>

            <div
                class="playback-progress-bar duration-input absolute inset-x-0 bottom-0 z-1 flex h-1 bg-neutral-300 opacity-80 transition-opacity dark:bg-neutral-700 dark:opacity-60"
                :title="`Completed: ? out of ${data.file_count} ${data.is_majority_audio ? 'track' : 'video'}s`"
            >
                <div
                    class="bg-foreground-3 duration-input playback-progress-fill size-full transition-[background-color]"
                    :style="{ width: `${(data.completion_count / data.file_count) * 100}%` }"
                ></div>
            </div>
        </RouterLink>
        <section class="xxs:gap-2 flex h-full flex-1 flex-col gap-4 p-3">
            <div class="xxs:flex-row flex flex-col items-start gap-1.5">
                <RouterLink :to="`/${encodeURI(data.path)}`" class="group-hover:text-primary dark:group-hover:text-primary-muted min-w-0 flex-1" title="Play">
                    <h3 class="capitalize">{{ data.title ?? data.name }}</h3>
                </RouterLink>
                <div class="flex shrink-0 gap-1.5">
                    <ButtonIcon :title="'Open Folder In New Tab'" :to="`/${encodeURI(data.path)}`" :target="'_blank'" class="size-6 p-0">
                        <template #icon><IconShare class="size-4" /></template>
                    </ButtonIcon>
                    <BasePopover popoverClass="max-w-48 lg:max-w-56 rounded-lg" :buttonClass="'size-6 p-0'" ref="popover">
                        <template #buttonIcon><ProiconsMoreVertical class="size-4" /></template>
                        <template #content>
                            <LibraryFolderCardMenu
                                :data="data"
                                :processing="processing"
                                :library-downloads-enabled="stateLibrary?.downloads_enabled || false"
                                :handle-close-popover="popover?.handleClose"
                                :handle-toggle-downloads="handleToggleDownloads"
                                @clickAction="$emit('clickAction', data.id)"
                            />
                        </template>
                    </BasePopover>
                </div>
            </div>
            <div class="text-foreground-1 mt-1 flex size-full flex-col gap-2 text-xs">
                <div class="mt-auto flex flex-wrap items-center justify-between gap-3 gap-y-1">
                    <div class="flex items-center gap-2">
                        <span class="-ms-0.5 flex items-center gap-1" :title="`${data.file_count} ${data.is_majority_audio ? 'Track' : 'Video'}${toPlural(data.file_count)}`">
                            <template v-if="data.is_majority_audio">
                                <IconMusic class="size-3.5" />
                                {{ data.file_count }}
                            </template>
                            <template v-else>
                                <IconPlay class="size-3.5" />
                                {{ data.file_count }}
                            </template>
                        </span>
                        <span
                            v-if="data.series?.rating !== null && data.series?.rating !== undefined"
                            :title="`Rated ${data.series.rating} out of 100`"
                            class="flex items-center gap-1"
                        >
                            <ProiconsStar class="size-3.75" />
                            <div>
                                <span class="text-primary dark:text-primary-muted font-bold">{{ data.series.rating }}</span>
                                <span class="self-baseline text-[10px]">/100</span>
                            </div>
                        </span>
                    </div>
                    <span class="shrink-0" :title="`Total Size ${formatFileSize(data.total_size)}`">{{ formatFileSize(data.total_size) }}</span>
                </div>

                <div class="bg-hr dark:bg-hr/30 -mx-3 mt-1 h-px shrink-0"></div>

                <div class="flex items-center justify-between gap-x-2">
                    <p class="whitespace-nowrap" :title="`Total Size ${formatFileSize(data.total_size)}`">
                        Updated
                        <span class="@3xs:hidden">{{ data.updated_at ? toTimeSpan(data.updated_at, '', true) : 'Never' }} ago</span>
                        <span class="hidden @3xs:inline-block">{{ data.updated_at ? toTimeSpan(data.updated_at, '') : 'Never' }}</span>
                    </p>
                    <p class="ml-auto truncate" :title="`Date Added ${formattedDate.long}`">
                        {{ formattedDate.short }}
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<style lang="css" scoped>
.data-card {
    &:hover .playback-progress-bar {
        opacity: 1;
    }

    &:hover .playback-progress-fill {
        background-color: var(--color-primary-muted);
    }
}

.dark .data-card:hover .playback-progress-fill {
    background-color: var(--color-primary-active);
}
</style>
