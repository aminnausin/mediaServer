<script setup lang="ts">
import type { PopoverItem } from '@/types/types';

import { onMounted, nextTick, computed, watch, provide, defineAsyncComponent, useTemplateRef } from 'vue';
import { ButtonBase, ButtonIcon, ButtonText } from '@/components/cedar-ui/button';
import { getScreenSizeRank, toTitleCase } from '@/service/util';
import { handleEditFolderImages } from '@/service/folder/folderActions';
import { startScanFilesTask } from '@/service/siteAPI';
import { cn, toast, drawer } from '@aminnausin/cedar-ui';
import { ContextMenuItem } from '@/components/cedar-ui/context-menu';
import { useContentStore } from '@/stores/ContentStore';
import { useFolderTabs } from '@/components/folders/FolderTabs';
import { useModalStore } from '@/stores/ModalStore';
import { BasePopover } from '@/components/cedar-ui/popover';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/auth/useAuth';

import FolderDetailsNavDrawer from '@/components/drawers/FolderDetailsNavDrawer.vue';
import FolderDetailsSidebar from '@/components/panels/FolderDetailsSidebar.vue';
import FolderTabSkeleton from '@/components/folders/FolderTabSkeleton.vue';
import EditFolderModal from '@/components/modals/EditFolderModal.vue';
import ShareModal from '@/components/modals/ShareModal.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsPhoto from '~icons/proicons/photo';
import ProiconsPlay from '~icons/proicons/play';
import ProiconsMenu from '~icons/proicons/menu';
import IconShare from '@/components/icons/IconShare.vue';
import IconEdit from '@/components/icons/IconEdit.vue';

const VALID_TABS = new Set(['overview', 'files', 'images', 'metadata', 'stats']);

const { stateDirectory, stateFolder, isLoadingContent, isStateFolderAudio: isAudio } = storeToRefs(useContentStore());
const { activeFolderTab, tabs, setTab } = useFolderTabs(stateFolder, isAudio);
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { getCategory, getFolder } = useContentStore();
const { isAuthenticated } = useAuth();

const modal = useModalStore();
const route = useRoute();

const popover = useTemplateRef('popover');

const FolderOverview = defineAsyncComponent(() => import('@/components/folders/FolderOverview.vue'));
const FolderMetadata = defineAsyncComponent(() => import('@/components/folders/FolderMetadata.vue'));
const FolderImages = defineAsyncComponent(() => import('@/components/folders/FolderImages.vue'));
const FolderHeader = defineAsyncComponent(() => import('@/components/folders/FolderHeader.vue'));
const FolderStats = defineAsyncComponent(() => import('@/components/folders/FolderStats.vue'));
const FolderMedia = defineAsyncComponent(() => import('@/components/folders/FolderMedia.vue'));

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

const popoverItems = computed<PopoverItem[]>(() => [
    {
        icon: IconEdit,
        text: 'Edit',
        action: () => modal.open(EditFolderModal, { cachedFolder: stateFolder }),
        hidden: !isAuthenticated.value || activeFolderTab.value?.name === 'metadata',
    },
    {
        icon: IconShare,
        text: 'Share',
        action: () => modal.open(ShareModal, { title: 'Share Folder', shareLink: globalThis.location.href }),
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

const baseUrl = computed(() => (stateDirectory.value.name && stateFolder.value.name ? `/${stateDirectory.value.name}/${stateFolder.value.title}` : undefined));

async function reload() {
    if (isLoadingContent.value) return;

    try {
        const toSingleParam = (p: string | string[]) => (Array.isArray(p) ? p[0] : p);

        const URL_CATEGORY = toSingleParam(route.params.category);
        const URL_FOLDER = toSingleParam(route.params.folder);

        await nextTick();
        document.body.scrollTo({ top: 0, behavior: 'instant' });

        if (stateDirectory.value?.name && stateDirectory.value.name === URL_CATEGORY && URL_FOLDER) {
            await getFolder(URL_FOLDER, false, false);
        } else {
            isLoadingContent.value = true;
            await getCategory(URL_CATEGORY, URL_FOLDER, false);
        }
    } catch (error) {
        console.error(error);
    } finally {
        isLoadingContent.value = false;
        nextTick(setFolderAsPageTitle);
    }
}

const setFolderAsPageTitle = () => {
    if (isLoadingContent.value) return;

    if (!stateFolder.value.title) {
        pageTitle.value = 'Folder not found';
        document.title = 'Folder Details';
        return;
    }

    document.title = activeFolderTab.value ? `${stateFolder.value.title} · ${toTitleCase(activeFolderTab.value.name)}` : stateFolder.value.title;
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
provide('isAudio', isAudio);
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
                        <div class="@container flex flex-wrap justify-between gap-2" v-if="baseUrl && !isLoadingContent">
                            <ButtonBase
                                :class="cn('h-8 gap-1 ps-0 @md:hidden')"
                                @click="
                                    drawer.open(FolderDetailsNavDrawer, {
                                        showHeader: false,
                                        showFooter: false,
                                        payload: {
                                            tabs,
                                            baseUrl,
                                        },
                                    })
                                "
                            >
                                <ProiconsMenu class="size-4" />
                                <span class="capitalize">{{ activeFolderTab?.name ?? 'Overview' }}</span>
                            </ButtonBase>
                            <div class="bg-surface-3/50 dark:bg-surface-3 scrollbar-minimal mr-auto hidden h-fit w-fit flex-wrap gap-0.5 overflow-x-auto rounded-lg p-0.5 @md:flex">
                                <ButtonBase
                                    v-for="tab in tabs"
                                    :key="tab.name"
                                    :class="
                                        cn('h-7 rounded-md px-3 py-1 capitalize transition-colors', {
                                            'bg-surface-1 dark:bg-surface-4 text-primary-active dark:text-primary-muted shadow-sm': activeFolderTab?.name === tab.name,
                                            'text-foreground-2 hover:text-foreground-0 hover:bg-surface-1/50': activeFolderTab?.name !== tab.name,
                                        })
                                    "
                                    :to="`${baseUrl}/details/${tab.name}`"
                                >
                                    {{ tab.name }}
                                </ButtonBase>
                            </div>
                            <div class="mb-auto flex h-8 flex-wrap gap-2 py-0.5 *:h-7 *:min-w-7 *:p-0">
                                <ButtonText class="gap-1 sm:px-2" title="Play" :to="baseUrl">
                                    <ProiconsPlay class="size-4" />
                                    <span class="hidden leading-none sm:block">Play</span>
                                </ButtonText>
                                <ButtonIcon v-if="activeFolderTab?.name === 'images' && isAuthenticated" title="Edit Folder Images" @click="handleEditFolderImages(stateFolder)">
                                    <IconEdit class="size-4" />
                                </ButtonIcon>
                                <ButtonIcon
                                    v-if="activeFolderTab?.name === 'metadata' && isAuthenticated"
                                    title="Edit Folder Metadata"
                                    @click="modal.open(EditFolderModal, { cachedFolder: stateFolder })"
                                >
                                    <IconEdit class="size-4" />
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
            <FolderDetailsSidebar />
        </template>
    </LayoutBase>
</template>
