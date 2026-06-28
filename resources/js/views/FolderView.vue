<script setup lang="ts">
import type { SidebarTabItem } from '@/types/types';

import { onMounted, nextTick, computed, watch, provide, defineAsyncComponent } from 'vue';
import { ButtonBase, ButtonIcon, ButtonText } from '@/components/cedar-ui/button';
import { handleEditFolderImages } from '@/service/folder/folderActions';
import { getScreenSizeRank } from '@/service/util';
import { useContentStore } from '@/stores/ContentStore';
import { createTabStore } from '@/stores/TabStore';
import { useModalStore } from '@/stores/ModalStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/auth/useAuth';
import { cn } from '@aminnausin/cedar-ui';

import EditFolderModal from '@/components/modals/EditFolderModal.vue';
import ShareModal from '@/components/modals/ShareModal.vue';
import VideoSidebar from '@/components/panels/VideoSidebar.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

import ProiconsPhoto from '~icons/proicons/photo';
import CircumMonitor from '~icons/circum/monitor';
import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';

const VALID_TABS = new Set(['overview', 'files', 'images', 'metadata', 'stats']);

const { stateDirectory, stateFolder, isLoadingContent } = storeToRefs(useContentStore());
const { pageTitle, selectedSideBar } = storeToRefs(useAppStore());
const { isAuthenticated } = useAuth();

const { getCategory } = useContentStore();

const baseUrl = computed(() => (stateDirectory.value.name && stateFolder.value.name ? `/${stateDirectory.value.name}/${stateFolder.value.title}` : false));
const currentUrl = computed(() => window.location.origin + baseUrl.value);

const modal = useModalStore();
const tabsStore = createTabStore(
    `folder-info`,
    () => [{ name: 'overview' }, { name: 'files' }, { name: 'images' }, { name: 'metadata' }, { name: 'stats' }],
    (tab: SidebarTabItem) => `${stateFolder.value.title} ${tab.name}`,
)();

const { activeTab: activeFolderTab, tabs } = storeToRefs(tabsStore);

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

const route = useRoute();

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
    } catch (error) {
        console.log(error);
    } finally {
        isLoadingContent.value = false;
    }
}

const setFolderAsPageTitle = () => {
    const title = `${stateFolder.value?.series?.title ?? stateFolder?.value?.name}`;

    pageTitle.value = title || 'Folder not Found';
    document.title = title || 'Folder not Found';
};

watch(() => `${route.params.category}/${route.params.folder}`, reload, { immediate: false });

watch(
    () => route.params.tab,
    (tab) => {
        let parsedTab = tab as string;

        if (!VALID_TABS.has(parsedTab)) {
            parsedTab = 'overview';
        }

        tabsStore.setTab(parsedTab);
        setFolderAsPageTitle();
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
                            <div class="scrollbar-minimal flex w-full flex-1 overflow-x-auto">
                                <ButtonBase
                                    v-for="tab in tabs"
                                    :key="tab.name"
                                    :class="
                                        cn('hover:text-primary dark:hover:text-primary-muted capitalize', {
                                            'text-primary-active dark:text-primary-muted': activeFolderTab?.name === tab.name,
                                        })
                                    "
                                    :to="`${baseUrl}/info/${tab.name}`"
                                >
                                    {{ tab.name }}
                                </ButtonBase>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <ButtonText class="h-7 min-w-7 gap-1 px-0 py-0 sm:px-2" title="Watch" :to="baseUrl">
                                    <CircumMonitor class="size-4" />
                                    <span class="hidden leading-none sm:block">Watch</span>
                                </ButtonText>
                                <ButtonIcon
                                    v-if="activeFolderTab?.name === 'images' && isAuthenticated"
                                    class="size-7 p-0 shadow-md"
                                    title="Edit Folder Images"
                                    @click="handleEditFolderImages(stateFolder)"
                                >
                                    <template #icon>
                                        <ProiconsPhoto class="size-4" />
                                    </template>
                                </ButtonIcon>
                                <ButtonIcon
                                    v-if="activeFolderTab?.name === 'metadata' && isAuthenticated"
                                    class="size-7 p-0 shadow-md"
                                    title="Edit Folder Metadata"
                                    @click="modal.open(EditFolderModal, { cachedFolder: stateFolder })"
                                >
                                    <template #icon>
                                        <CircumEdit class="size-4" />
                                    </template>
                                </ButtonIcon>
                                <ButtonText
                                    class="size-7 p-0"
                                    title="Share Folder"
                                    @click="modal.open(ShareModal, { title: 'Share Folder', shareLink: `${encodeURI(currentUrl)}` })"
                                >
                                    <CircumShare1 class="size-4" />
                                </ButtonText>
                            </div>
                        </div>
                    </div>
                </FolderHeader>
                <component :is="activeComponent" />
            </div>
        </template>
        <template v-slot:sidebar>
            <VideoSidebar />
        </template>
    </LayoutBase>
</template>
