<script setup lang="ts">
import type { FolderResource, SeriesResource } from '@/types/resources';

import { ref, watch, type Ref } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { RouterLink } from 'vue-router';

import ButtonClipboard from '@/components/pinesUI/ButtonClipboard.vue';
import FolderCard from '@/components/cards/FolderCard.vue';
import RecordCard from '@/components/cards/RecordCard.vue';
import EditFolder from '@/components/forms/EditFolder.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import useModal from '@/composables/useModal';
import TableBase from '@/components/table/TableBase.vue';

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
    let folder = stateDirectory.value?.folders?.find((folder: FolderResource) => folder.id === id);
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
    <div class="p-3 flex flex-col gap-3">
        <div class="flex py-1 flex-col gap-2">
            <h1 id="sidebar-title" class="text-2xl h-8 w-full capitalize dark:text-white">{{ selectedSideBar }}</h1>
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
                :pagination-class="'!justify-center !flex-col-reverse'"
            />
        </section>
        <section v-if="selectedSideBar === 'history'" id="list-content-history" class="flex gap-2 flex-wrap">
            <RecordCard v-for="(record, index) in stateRecords.slice(0, 10)" :key="record.id" :record="record" :index="index" @clickAction="handleShare" />
            <RouterLink v-if="stateRecords.length != 0" to="/history" class="text-center text-sm dark:text-neutral-400 mx-auto p-3 hover:underline">View More</RouterLink>
            <h2 v-else class="text-gray-500 dark:text-gray-400 tracking-wider w-full py-2">Nothing Yet...</h2>
        </section>
        <ModalBase :modalData="shareModal">
            <template #content>
                <div class="pb-2">Copy link to clipboard to share it.</div>
            </template>
            <template #controls>
                <ButtonClipboard :text="shareLink" />
            </template>
        </ModalBase>
        <ModalBase :modalData="editFolderModal" :useControls="false">
            <template #content>
                <div class="pt-2">
                    <EditFolder v-if="cachedFolder" :folder="cachedFolder" @handleFinish="handleSeriesUpdate" />
                </div>
            </template>
        </ModalBase>
    </div>
</template>
