<script setup lang="ts">
import type { PopoverItem } from '@/types/types';

import { onMounted, nextTick, computed, watch, provide, defineAsyncComponent, useTemplateRef } from 'vue';
import { ButtonBase, ButtonIcon, ButtonText } from '@/components/cedar-ui/button';
import { handleEditFolderImages } from '@/service/folder/folderActions';
import { startScanFilesTask } from '@/service/siteAPI';
import { getScreenSizeRank } from '@/service/util';
import { ContextMenuItem } from '@/components/cedar-ui/context-menu';
import { useContentStore } from '@/stores/ContentStore';
import { useFolderTabs } from '@/components/folders/FolderTabs';
import { useModalStore } from '@/stores/ModalStore';
import { BasePopover } from '@/components/cedar-ui/popover';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { cn, toast } from '@aminnausin/cedar-ui';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/auth/useAuth';

import SidebarSkeleton from '@/components/skeleton/composites/SidebarSkeleton.vue';
import FolderTabSkeleton from '@/components/folders/FolderTabSkeleton.vue';
import EditFolderModal from '@/components/modals/EditFolderModal.vue';
import ShareModal from '@/components/modals/ShareModal.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsPhoto from '~icons/proicons/photo';
import CircumMonitor from '~icons/circum/monitor';
import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';

const VALID_TABS = new Set(['overview', 'files', 'images', 'metadata', 'stats']);

const { stateDirectory, stateFolder, isLoadingContent } = storeToRefs(useContentStore());
const { activeFolderTab, tabs, setTab } = useFolderTabs();
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { getCategory, getFolder } = useContentStore();
const { isAuthenticated } = useAuth();

const baseUrl = computed(() => (stateDirectory.value.name && stateFolder.value.name ? `/${stateDirectory.value.name}/${stateFolder.value.title}` : undefined));

const popover = useTemplateRef('popover');

const popoverItems = computed<PopoverItem[]>(() => [
    {
        icon: CircumEdit,
        text: 'Edit',
        action: () => modal.open(EditFolderModal, { cachedFolder: stateFolder }),
        hidden: !isAuthenticated.value || activeFolderTab.value?.name === 'metadata',
    },
    {
        icon: CircumShare1,
        text: 'Share',
        action: () => modal.open(ShareModal, { title: 'Share Folder', shareLink: window.location.href }),
    },

    {
        icon: ProiconsArrowSync,
        text: 'Scan Library',
        action: () => handleStartScan(),
        hidden: !isAuthenticated.value,
    },
    { divider: true, hidden: !isAuthenticated.value },
    {
        icon: ProiconsPhoto,
        text: 'Edit Images',
        action: () => handleEditFolderImages(stateFolder.value),
        hidden: !isAuthenticated.value,
    },
]);

const modal = useModalStore();

const FolderOverview = defineAsyncComponent(() => import('@/components/folders/FolderOverview.vue'));
const FolderMetadata = defineAsyncComponent(() => import('@/components/folders/FolderMetadata.vue'));
const FolderImages = defineAsyncComponent(() => import('@/components/folders/FolderImages.vue'));
const FolderHeader = defineAsyncComponent(() => import('@/components/folders/FolderHeader.vue'));
const FolderStats = defineAsyncComponent(() => import('@/components/folders/FolderStats.vue'));
const FolderMedia = defineAsyncComponent(() => import('@/components/folders/FolderMedia.vue'));

const FolderSidebarAsync = defineAsyncComponent(async () => await import('@/components/panels/FolderSidebar.vue'));

const activeComponent = computed(() => {
    switch (activeFolderTab.value?.name) {
        case 'overview':
            return FolderOverview;
        case 'files':
            return FolderMedia;
        case 'images':
            return FolderImages;
        case 'metadata':
            return FolderMetadata;
        case 'stats':
            return FolderStats;
        default:
            return null;
    }
});

const route = useRoute();

async function reload() {
    if (isLoadingContent.value) return;

    try {
        const toSingleParam = (p: string | string[]) => (Array.isArray(p) ? p[0] : p);

        const URL_CATEGORY = toSingleParam(route.params.category);
        const URL_FOLDER = toSingleParam(route.params.folder);

        await nextTick();
        document.body.scrollTo({ top: 0, behavior: 'instant' });

        if (stateDirectory.value?.name && stateDirectory.value.name === URL_CATEGORY && URL_FOLDER) {
            await getFolder(URL_FOLDER, false);
        } else {
            isLoadingContent.value = true;
            await getCategory(URL_CATEGORY, URL_FOLDER, false);
        }
    } catch (error) {
        console.log(error);
    } finally {
        isLoadingContent.value = false;
        setFolderAsPageTitle();
    }
}

const setFolderAsPageTitle = () => {
    if (isLoadingContent.value) return;

    const title = stateFolder.value.id ? `${stateFolder.value?.series?.title ?? stateFolder?.value?.name}` : 'Folder not found';
    pageTitle.value = title && activeFolderTab.value ? `Folder ${activeFolderTab.value.name}` : title;
    document.title = title;
};

const handleStartScan = async () => {
    if (!stateFolder.value.category_id) {
        toast('Error', { description: 'Invalid Library ID!', type: 'danger' });
        popover.value?.handleClose();
        return;
    }

    try {
        await startScanFilesTask(stateFolder.value.category_id);
        toast.add('Success', { type: 'success', description: `Submitted scan request!` });
        popover.value?.handleClose();
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit scan request.` });
        console.error(error);
    }
};

watch(() => `${route.params.category}/${route.params.folder}`, reload, { immediate: false });

watch(
    () => route.params.tab,
    (tab) => {
        let parsedTab = tab as string;

        if (!VALID_TABS.has(parsedTab)) {
            parsedTab = 'overview';
        }
        setTab(parsedTab);
    },
    { immediate: true },
);

onMounted(async () => {
    await reload();
    await nextTick();
    if (getScreenSizeRank() >= 3 && selectedSideBar.value !== 'folders') useAppStore().cycleSideBar('folders', 'list-card');
});

provide('data', stateFolder);
provide(
    'series',
    computed(() => stateFolder.value.series),
);
provide(
    'isAudio',
    computed(() => stateFolder.value.is_majority_audio),
);
provide(
    'primaryImageIds',
    computed(() => ({ poster: stateFolder.value.series?.poster_image?.id, banner: stateFolder.value.series?.primary_banner_id })),
);
</script>

<template>
    <LayoutBase>
        <template #content>
            <div id="content-folder" class="page-height @container flex flex-col gap-3 text-sm">
                <FolderHeader>
                    <div class="z-3 flex w-full flex-col justify-between gap-4 p-3">
                        <div class="flex flex-wrap items-center gap-2" v-if="baseUrl && !isLoadingContent">
                            <div class="scrollbar-minimal flex w-full flex-1 flex-wrap overflow-x-auto">
                                <ButtonBase
                                    v-for="tab in tabs"
                                    :key="tab.name"
                                    :class="
                                        cn('hover:text-primary dark:hover:text-primary-muted capitalize', {
                                            'text-primary-active dark:text-primary-muted': activeFolderTab?.name === tab.name,
                                        })
                                    "
                                    :to="`${baseUrl}/details/${tab.name}`"
                                >
                                    {{ tab.name }}
                                </ButtonBase>
                            </div>
                            <div class="mb-auto flex h-8 flex-wrap gap-2 py-0.5 *:h-7 *:min-w-7 *:p-0">
                                <ButtonText class="gap-1 sm:px-2" title="Watch" :to="baseUrl">
                                    <CircumMonitor class="size-4" />
                                    <span class="hidden leading-none sm:block">Watch</span>
                                </ButtonText>
                                <ButtonIcon v-if="activeFolderTab?.name === 'images' && isAuthenticated" title="Edit Folder Images" @click="handleEditFolderImages(stateFolder)">
                                    <ProiconsPhoto class="size-4" />
                                </ButtonIcon>
                                <ButtonIcon
                                    v-if="activeFolderTab?.name === 'metadata' && isAuthenticated"
                                    title="Edit Folder Metadata"
                                    @click="modal.open(EditFolderModal, { cachedFolder: stateFolder })"
                                >
                                    <CircumEdit class="size-4" />
                                </ButtonIcon>
                                <BasePopover ref="popover" popoverClass="max-w-38 p-1 text-xs" :buttonClass="'size-7 p-0'" :button-component="ButtonIcon">
                                    <template #buttonIcon>
                                        <ProiconsMoreVertical class="size-4" />
                                    </template>
                                    <template #content>
                                        <ContextMenuItem
                                            v-for="popoverItem in popoverItems.filter((itm) => !itm.hidden)"
                                            v-bind="popoverItem"
                                            :key="popoverItem.text"
                                            :action="
                                                () => {
                                                    popover?.handleClose();
                                                    popoverItem.action?.();
                                                }
                                            "
                                        />
                                    </template>
                                </BasePopover>
                            </div>
                        </div>
                        <div v-else class="flex animate-pulse justify-between gap-4 py-1.5">
                            <div class="scrollbar-minimal flex w-full flex-1 flex-wrap gap-2 overflow-x-auto">
                                <div v-for="tab in tabs" :key="tab.name" :class="['bg-foreground-1/20 h-5 rounded text-transparent select-none']">{{ tab.name }}</div>
                            </div>
                            <div class="mb-auto flex gap-1">
                                <div class="bg-foreground-1/10 size-5 rounded sm:w-19"></div>
                                <div class="bg-foreground-1/10 size-5 rounded"></div>
                            </div>
                        </div>
                    </div>
                </FolderHeader>
                <FolderTabSkeleton v-if="isLoadingContent || !stateFolder.id" />
                <component v-else :is="activeComponent" />
            </div>
        </template>
        <template v-slot:sidebar>
            <Suspense v-if="selectedSideBar === 'folders'">
                <FolderSidebarAsync :url-suffix="`${['details', activeFolderTab?.name].filter(Boolean).join('/')}`" />

                <template #fallback>
                    <SidebarSkeleton />
                </template>
            </Suspense>
        </template>
    </LayoutBase>
</template>
