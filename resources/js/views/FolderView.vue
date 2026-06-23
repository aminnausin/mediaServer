<script setup lang="ts">
import type { GenericSortOption } from '@/types/types';
import type { VideoResource } from '@/contracts/media';
import type { ComputedRef } from 'vue';
import type { SortDir } from '@aminnausin/cedar-ui';

import { formatFileSize, handleStorageURL, toFormattedDuration, toTimeSpan } from '@/service/util';
import { onMounted, nextTick, computed, watch, ref, useTemplateRef } from 'vue';
import { handleEditFolderImages } from '@/service/folder/folderActions';
import { ButtonBase, ButtonIcon } from '@/components/cedar-ui/button';
import { mediaSortingOptions } from '@/constants/sortingOptions';
import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { MediaType } from '@/types/types';
import { cn, toast } from '@aminnausin/cedar-ui';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/auth/useAuth';

import EditFolderModal from '@/components/modals/EditFolderModal.vue';
import EditMediaModal from '@/components/modals/EditMediaModal.vue';
import FolderInfoRow from '@/components/folders/FolderInfoRow.vue';
import VideoSidebar from '@/components/panels/VideoSidebar.vue';
import ShareModal from '@/components/modals/ShareModal.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';
import TableBase from '@/components/cedar-ui/table/TableBase.vue';
import VideoCard from '@/components/cards/data/VideoCard.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import ProiconsPhoto from '~icons/proicons/photo';
import CircumMonitor from '~icons/circum/monitor';
import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';
import { useScrollbarDetection } from '@/composables/design/useScrollbarDetection';

type FolderTab = 'overview' | 'files' | 'images' | 'metadata' | 'stats';

const folderTabs: FolderTab[] = ['overview', 'files', 'images', 'metadata', 'stats'];

const activeTab = ref<FolderTab>('overview');

const { searchQuery, stateFilteredPlaylist, stateDirectory, stateVideo, stateFolder, currentMediaIndex, isLoadingContent } = storeToRefs(useContentStore());
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { isAuthenticated } = useAuth();

const { getCategory, playlistSort } = useContentStore();

const folderInfoScrollContainer = useTemplateRef('folder-info');

const { hasScrollbar: folderInfoHasScrollbar } = useScrollbarDetection(folderInfoScrollContainer);

const route = useRoute();
const modal = useModalStore();

const sortingOptions = computed(() => mediaSortingOptions(stateFolder.value)) satisfies ComputedRef<GenericSortOption<VideoResource>[]>; // Idk what the point of using satisfies is

const handleSort = (column: keyof VideoResource = 'file_modified_at', dir: SortDir = 1) => {
    playlistSort({ column, dir });
};

const mediaTypeDescription = computed(() => {
    return stateVideo.value?.metadata?.media_type === MediaType.AUDIO || stateFolder.value?.is_majority_audio ? 'Track' : 'Video';
});

const handleVideoAction = (e: Event, id: number, action: 'edit' | 'share' | 'download') => {
    if (!stateFolder.value?.videos) return;

    const mediaResource = stateFolder.value.videos.find((video: VideoResource) => video.id === id);

    if (!mediaResource) {
        toast.error('File not found');
        return;
    }

    switch (action) {
        case 'edit': {
            const metadataInfo = mediaResource.metadata ? { titleTooltip: `UUID: ${mediaResource.metadata.uuid}` } : {};

            modal.open(EditMediaModal, { title: `Edit ${mediaTypeDescription.value} Metadata`, mediaResource: mediaResource, ...metadataInfo });
            break;
        }
        case 'share':
            modal.open(ShareModal, {
                title: `Share ${mediaTypeDescription.value}`,
                shareLink: encodeURI(document.location.origin + route.path + `?video=${mediaResource.id}`),
                defaultTimestamp: 0,
            });
            break;
        default:
            toast.error('Option Unavailable', { description: `You cannot ${action} this file.` });
            break;
    }
};

onMounted(() => {
    useAppStore().cycleSideBar('folders', 'list-card');
    reload();
});

async function reload() {
    if (isLoadingContent.value) return;

    try {
        const toSingleParam = (p: string | string[]) => (Array.isArray(p) ? p[0] : p);

        const URL_CATEGORY = toSingleParam(route.params.category);
        const URL_FOLDER = toSingleParam(route.params.folder);

        await nextTick();
        document.body.scrollTo({ top: 0, behavior: 'instant' });
        isLoadingContent.value = true;
        await getCategory(URL_CATEGORY, URL_FOLDER);

        setFolderAsPageTitle();
        selectedSideBar.value = 'folders';
    } catch (error) {
        console.log(error);
    }
    isLoadingContent.value = false;
}

const setFolderAsPageTitle = () => {
    const title = stateFolder.value?.series?.title ?? stateFolder?.value?.name;
    if (!title) {
        pageTitle.value = 'Folder not Found';
        document.title = 'Folder not Found';
        return;
    }
    pageTitle.value = title;
    document.title = title;
};

watch(() => `${route.params.category}/${route.params.folder}`, reload, { immediate: false });
</script>

<template>
    <LayoutBase>
        <template #content>
            <div id="content-folder" class="page-height flex flex-col gap-3 text-sm">
                <div class="bg-surface-2/60 ring-r-default/5 h-fit w-full rounded-xl shadow-md ring-1">
                    <div
                        class="ring-r-default/5 flex h-52 items-end overflow-clip rounded-t-xl bg-cover text-white ring-1 lg:h-64"
                        :style="{
                            'background-image': `url(${stateFolder?.series?.banner_image?.path ?? 'https://s4.anilist.co/file/anilistcdn/user/banner/b6792701-mBLPRvzr3xPL.jpg'})`,
                        }"
                    >
                        <div class="flex w-full flex-wrap items-end gap-4 bg-linear-to-b from-transparent to-neutral-950/40 p-3 text-center">
                            <LazyImage
                                :wrapper-class="'relative h-fit w-fit'"
                                class="aspect-2-3 mx-auto w-24 min-w-24 rounded-md object-cover"
                                :src="stateFolder?.series?.poster_image?.path ?? handleStorageURL(stateFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                                alt="profile"
                            />
                            <div class="text-centre flex flex-1 flex-wrap items-end justify-center gap-1 sm:pb-2">
                                <h2 class="w-full text-2xl text-balance capitalize sm:flex-1 sm:text-left">{{ stateFolder?.title }}</h2>
                                <p class="text-sm">Created: {{ toTimeSpan(stateFolder?.created_at || '', '') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="z-3 flex w-full flex-col justify-between gap-4 p-3">
                        <div class="flex flex-wrap items-start gap-2">
                            <div class="flex flex-1">
                                <ButtonBase
                                    v-for="tab in folderTabs"
                                    :key="tab"
                                    :class="cn('hover:text-primary dark:hover:text-primary-muted capitalize', { 'text-primary-active dark:text-primary-muted': activeTab === tab })"
                                    @click="activeTab = tab"
                                >
                                    {{ tab }}
                                </ButtonBase>
                            </div>
                            <div v-if="isAuthenticated && stateFolder" class="flex flex-wrap gap-2">
                                <ButtonIcon class="size-7 p-0 shadow-md" title="Edit Folder Images" @click="handleEditFolderImages(stateFolder)">
                                    <template #icon>
                                        <ProiconsPhoto class="size-4" />
                                    </template>
                                </ButtonIcon>
                                <ButtonIcon class="size-7 p-0 shadow-md" title="Edit Folder Metadata" @click="modal.open(EditFolderModal, { cachedFolder: stateFolder })">
                                    <template #icon>
                                        <CircumEdit class="size-4" />
                                    </template>
                                </ButtonIcon>
                                <ButtonIcon
                                    class="size-7 p-0 shadow-md"
                                    title="Share Folder"
                                    @click="modal.open(ShareModal, { title: 'Share Folder', shareLink: `/${stateDirectory.name}/${stateFolder.title}/info` })"
                                >
                                    <template #icon>
                                        <CircumShare1 class="size-4" />
                                    </template>
                                </ButtonIcon>
                                <ButtonIcon class="size-7 p-0 shadow-md" title="Watch" :to="`/${stateDirectory.name}/${stateFolder.title}`">
                                    <template #icon>
                                        <CircumMonitor class="size-4" />
                                    </template>
                                </ButtonIcon>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-1" v-if="activeTab === 'overview'">
                    <p class="text-foreground-1">Description</p>
                    <p class="text-foreground-1 bg-surface-2/60 ring-r-default/5 h-fit w-full flex-1 space-y-0.5 rounded-xl p-3 text-sm text-balance shadow-md ring-1">
                        {{ stateFolder?.series?.description ?? 'No Description' }}
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row" v-if="activeTab === 'overview' || activeTab === 'files'">
                    <div class="bg-surface-2/60 ring-r-default/5 w-full rounded-xl p-3 shadow-md ring-1 sm:max-w-40" v-if="activeTab === 'overview'">
                        <div :class="cn('scrollbar-minimal flex gap-2 overflow-x-auto sm:flex-col sm:pb-0', { 'pb-1': folderInfoHasScrollbar })" ref="folder-info">
                            <FolderInfoRow v-if="stateFolder.total_size" title="Total Size" :value="formatFileSize(stateFolder.total_size)" />
                            <FolderInfoRow title="Total Views" :value="stateFolder.videos.reduce((acc, vid) => (acc += vid.view_count ?? 0), 0)" />
                            <FolderInfoRow v-if="stateFolder.series?.episodes" title="Episodes" :value="stateFolder.series.episodes" />
                            <FolderInfoRow v-if="stateFolder.series?.seasons" title="Seasons" :value="stateFolder.series?.seasons" />
                            <FolderInfoRow
                                title="Avg Duration"
                                :value="`${toFormattedDuration(stateFolder.videos.reduce((acc, vid) => (acc += vid.duration ?? 0), 0) / (stateFolder.episodes || 1))}`"
                            />
                            <FolderInfoRow title="Intro Duration" :value="`${stateFolder.series?.avg_intro_duration}s`" />
                            <FolderInfoRow v-if="stateFolder.series?.started_at" title="Start Date" :value="stateFolder.series?.started_at" />
                            <FolderInfoRow v-if="stateFolder.series?.ended_at" title="End Date" :value="stateFolder.series?.ended_at" />
                            <FolderInfoRow v-if="stateFolder.series?.rating" title="Average Score" :value="`${stateFolder.series?.rating}%`" />
                            <FolderInfoRow v-if="stateFolder.series?.studio" title="Studio" :value="stateFolder.series?.studio" />
                            <FolderInfoRow v-if="stateFolder.series?.folder_tags?.length" title="Tags">
                                <div class="flex gap-0.5 *:w-fit *:max-w-full *:truncate sm:flex-col">
                                    <div class="text-foreground-1 text-xs" :title="tag.name" v-for="tag in stateFolder.series?.folder_tags" :key="tag.id">{{ tag.name }}</div>
                                </div>
                            </FolderInfoRow>
                        </div>
                    </div>
                    <div :class="cn('flex-1', { 'bg-surface-2/60 ring-r-default/5 rounded-xl p-3 shadow-md ring-1': activeTab !== 'files' })">
                        <TableBase
                            ref="mediaTable"
                            :class="'h-full flex-1'"
                            :data="stateFilteredPlaylist"
                            :row="VideoCard"
                            :otherAction="handleVideoAction"
                            :loading="isLoadingContent"
                            :useToolbar="true"
                            :sortAction="handleSort"
                            :sortingOptions="sortingOptions.filter((s) => !s.hidden)"
                            :selectedID="stateVideo?.id"
                            :startAscending="true"
                            :currentIndex="currentMediaIndex"
                            v-model="searchQuery"
                        />
                    </div>
                </div>
            </div>
        </template>
        <template v-slot:sidebar>
            <VideoSidebar />
        </template>
    </LayoutBase>
</template>
