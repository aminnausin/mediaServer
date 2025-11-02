<script setup lang="ts">
import type { FolderResource, SeriesResource } from '@/types/resources';
import type { GenericSortOption, SortDir } from '@/types/types';
import type { Ref } from 'vue';

import { computed, ref, watch } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { toFormattedDate } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { sortObject } from '@/service/sort/baseSort';

import ButtonClipboard from '@/components/pinesUI/ButtonClipboard.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import FolderCard from '@/components/cards/FolderCard.vue';
import RecordCard from '@/components/cards/RecordCard.vue';
import EditFolder from '@/components/forms/EditFolder.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

import ProiconsFilterCancel from '~icons/proicons/filter-cancel';
import ProiconsFilter from '~icons/proicons/filter';
import ButtonIcon from '../inputs/ButtonIcon.vue';

const folderSortingOptions: GenericSortOption<FolderResource>[] = [
    {
        title: 'Title',
        value: 'name',
        disabled: false,
    },
    {
        title: 'Date Created',
        value: 'created_at',
        disabled: false,
    },
    {
        title: 'Size',
        value: 'total_size',
        disabled: false,
    },
    {
        title: 'File Count',
        value: 'file_count',
        disabled: false,
    },
];

const editFolderModal = useModal({ title: 'Edit Folder Details', submitText: 'Submit Details' });
const shareModal = useModal({ title: 'Share Video' });
const cachedFolder = ref<FolderResource>();
const shareLink = ref('');

const folderSortDir = ref<SortDir>(1);
const folderSortKey = ref<keyof FolderResource>(folderSortingOptions[0].value);
const showFilters = ref(false);

const { stateRecords, stateDirectory, stateFolder } = storeToRefs(useContentStore()) as unknown as {
    stateRecords: any;
    stateDirectory: Ref<{ name: string; folders: FolderResource[] }>;
    stateFolder: Ref<FolderResource>;
};

const { updateFolderData } = useContentStore();
const { selectedSideBar } = storeToRefs(useAppStore());

const sortedFolders = computed<FolderResource[]>(() => {
    return [...stateDirectory.value.folders].sort(sortObject<FolderResource>(folderSortKey.value, folderSortDir.value));
});

const handleShare = (link: string) => {
    if (!link || link[0] !== '/') return;

    shareLink.value = window.location.origin + link;
    shareModal.toggleModal(true);
};

const handleFolderAction = (e: Event, id: number, action: 'edit' | 'share' = 'edit') => {
    const folder = stateDirectory.value?.folders?.find((folder: FolderResource) => folder.id === id);
    if (!folder?.id) return;

    cachedFolder.value = folder;
    if (action === 'edit') editFolderModal.toggleModal();
    else {
        shareLink.value = encodeURI(window.location.origin + '/' + folder.path);
        shareModal.toggleModal(true);
    }
};

const handleSeriesUpdate = async (res: any) => {
    if (res?.data?.id) updateFolderData(res.data as SeriesResource);
    editFolderModal.toggleModal(false);
};

watch(
    () => selectedSideBar.value,
    () => {
        shareModal.setTitle(`${selectedSideBar.value === 'folders' ? 'Share Folder' : 'Share Video'}`);
    },
);
</script>

<template>
    <div class="flex flex-col gap-2 py-1">
        <div class="flex items-center justify-between">
            <h2 id="sidebar-title" class="h-8 w-full truncate text-2xl capitalize dark:text-white">
                {{ selectedSideBar }}
            </h2>
            <ButtonIcon
                v-if="stateDirectory.folders.length > 10 && selectedSideBar === 'folders'"
                class="!size-8 !p-0 dark:!ring-transparent hover:dark:!bg-violet-700 [&>*]:size-6"
                @click="showFilters = !showFilters"
                title="Toggle Filters"
            >
                <template #icon>
                    <component :is="showFilters ? ProiconsFilterCancel : ProiconsFilter" />
                </template>
            </ButtonIcon>
        </div>

        <hr class="" />
    </div>
    <TableBase
        v-if="selectedSideBar === 'folders'"
        id="list-content-folders"
        :data="sortedFolders"
        :row="FolderCard"
        :otherAction="handleFolderAction"
        :useToolbar="stateDirectory.folders.length > 10 && showFilters"
        :startAscending="true"
        :row-attributes="{
            categoryName: stateDirectory.name,
            stateFolderName: stateFolder?.name,
        }"
        :items-per-page="10"
        :max-visible-pages="3"
        :pagination-class="'!justify-center !flex-col-reverse'"
        :use-pagination-icons="true"
        :sort-action="
            (sortKey: keyof FolderResource, sortDir: SortDir) => {
                folderSortDir = sortDir;
                folderSortKey = sortKey;
            }
        "
        :sorting-options="folderSortingOptions"
    />
    <section v-if="selectedSideBar === 'history'" id="list-content-history" class="flex flex-wrap gap-2">
        <RecordCard v-for="(record, index) in stateRecords.slice(0, 10)" :key="record.id" :record="record" :index="index" @clickAction="handleShare" />
        <ButtonText
            v-if="stateRecords.length > 0"
            to="/history"
            :title="'View All Watch History'"
            :class="'mx-auto mb-2 mt-2 line-clamp-1 h-8 truncate !rounded-full !bg-primary-800 text-sm hover:!bg-white hover:ring-[0.125rem] hover:!ring-violet-400 dark:!bg-primary-dark-800/70 dark:hover:!bg-primary-dark-600 hover:dark:!ring-violet-700'"
            :variant="'form'"
            target=""
        >
            <template #text>View More</template>
        </ButtonText>
        <h3 v-show="stateRecords.length < 1" class="w-full py-2 tracking-wider text-gray-500 dark:text-gray-400">Nothing Yet...</h3>
    </section>
    <ModalBase :modalData="shareModal">
        <template #description> Copy link to clipboard to share it.</template>
        <template #controls>
            <ButtonClipboard :text="shareLink" />
        </template>
    </ModalBase>
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #description v-if="cachedFolder && cachedFolder.series?.editor_id && cachedFolder.series.date_updated">
            Last edited by
            <a title="Editor profile" target="_blank" :href="`/profile/${cachedFolder.series.editor_id}`" class="hover:text-purple-600 dark:hover:text-purple-500"
                >@{{ cachedFolder.series.editor_id }}</a
            >
            at
            {{ toFormattedDate(new Date(cachedFolder.series.date_updated)) }}
        </template>
        <template #content>
            <EditFolder v-if="cachedFolder" :folder="cachedFolder" @handleFinish="handleSeriesUpdate" />
        </template>
    </ModalBase>
</template>
