<script setup lang="ts">
import type { FolderResource, SeriesResource } from '@/types/resources';

import { ref, watch, type Ref } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import ButtonClipboard from '@/components/pinesUI/ButtonClipboard.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import FolderCard from '@/components/cards/FolderCard.vue';
import RecordCard from '@/components/cards/RecordCard.vue';
import EditFolder from '@/components/forms/EditFolder.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import TableBase from '@/components/table/TableBase.vue';
import useModal from '@/composables/useModal';

const editFolderModal = useModal({ title: 'Edit Folder Details', submitText: 'Submit Details' });
const shareModal = useModal({ title: 'Share Video' });
const cachedFolder = ref<FolderResource>();
const shareLink = ref('');

const { stateRecords, stateDirectory, stateFolder } = storeToRefs(useContentStore()) as unknown as {
    stateRecords: any;
    stateDirectory: Ref<{ name: string; folders: FolderResource[] }>;
    stateFolder: Ref<FolderResource>;
};
const { updateFolderData } = useContentStore();
const { selectedSideBar } = storeToRefs(useAppStore());

const handleShare = (link: string) => {
    if (!link || link[0] !== '/') return;

    shareLink.value = window.location.origin + link;
    shareModal.toggleModal(true);
};

const handleFolderAction = (id: number, action: 'edit' | 'share' = 'edit') => {
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
    <div class="flex py-1 flex-col gap-2">
        <h2 id="sidebar-title" class="text-2xl h-8 w-full capitalize dark:text-white truncate">{{ selectedSideBar }}</h2>
        <hr class="" />
    </div>

    <section v-if="selectedSideBar === 'folders'" id="list-content-folders" class="flex gap-2 flex-wrap">
        <TableBase
            :data="stateDirectory.folders"
            :row="FolderCard"
            :clickAction="handleFolderAction"
            :useToolbar="false"
            :startAscending="true"
            :row-attributes="{
                categoryName: stateDirectory.name,
                stateFolderName: stateFolder?.name,
            }"
            :items-per-page="10"
            :max-visible-pages="3"
            :pagination-class="'!justify-center !flex-col-reverse'"
            :use-pagination-icons="true"
        />
    </section>
    <section v-if="selectedSideBar === 'history'" id="list-content-history" class="flex gap-2 flex-wrap">
        <RecordCard v-for="(record, index) in stateRecords.slice(0, 10)" :key="record.id" :record="record" :index="index" @clickAction="handleShare" />
        <ButtonText
            v-if="stateRecords.length > 0"
            to="/history"
            :title="'View All Watch History'"
            :class="'text-sm h-8 mx-auto mt-2 mb-2 hover:!bg-white dark:!bg-primary-dark-800/70 !bg-primary-800 dark:hover:!bg-primary-dark-600 line-clamp-1 truncate !rounded-full hover:!ring-violet-400 hover:dark:!ring-violet-700 hover:ring-[0.125rem]'"
            :variant="'form'"
            target=""
        >
            <template #text>View More</template>
        </ButtonText>
        <h3 v-show="stateRecords.length < 1" class="text-gray-500 dark:text-gray-400 tracking-wider w-full py-2">Nothing Yet...</h3>
    </section>
    <ModalBase :modalData="shareModal">
        <template #description> Copy link to clipboard to share it.</template>
        <template #controls>
            <ButtonClipboard :text="shareLink" />
        </template>
    </ModalBase>
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #content>
            <EditFolder v-if="cachedFolder" :folder="cachedFolder" @handleFinish="handleSeriesUpdate" />
        </template>
    </ModalBase>
</template>
