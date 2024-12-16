<script setup>
import { useContentStore } from '../../stores/ContentStore';
import { useAppStore } from '../../stores/AppStore';
import { storeToRefs } from 'pinia';
import { RouterLink } from 'vue-router';
import { ref, watch } from 'vue';

import ButtonClipboard from '../pinesUI/ButtonClipboard.vue';
import FolderCard from '../cards/FolderCard.vue';
import RecordCard from '../cards/RecordCard.vue';
import ModalBase from '../pinesUI/ModalBase.vue';
import useModal from '../../composables/useModal';

const appStore = useAppStore();
const contentStore = useContentStore();
const shareModal = useModal({ title: 'Share Video' });
const shareLink = ref('');

const { stateRecords, stateDirectory, stateFolder } = storeToRefs(contentStore);
const { selectedSideBar } = storeToRefs(appStore);

const handleShare = (link) => {
    if (!link || link[0] !== '/') return;

    shareLink.value = window.location.origin + link;
    shareModal.toggleModal(true);
};

watch(selectedSideBar, () => {
    shareModal.setTitle(`${selectedSideBar === 'folders' ? 'Share Folder' : 'Share Video'}`);
});
</script>

<template>
    <div class="flex p-1 text-ri">
        <h1 id="sidebar-title" class="text-xl h-8 w-full capitalize dark:text-white">{{ selectedSideBar }}</h1>
    </div>

    <hr class="mt-2 mb-3" />
    <section v-if="selectedSideBar === 'folders'" id="list-content-folders" class="flex space-y-2 flex-wrap">
        <FolderCard
            v-for="folder in stateDirectory.folders"
            :key="folder.id"
            :folder="folder"
            :categoryName="stateDirectory.name"
            :stateFolderName="stateFolder?.name"
            @clickAction="handleShare"
        />
    </section>
    <section v-if="selectedSideBar === 'history'" id="list-content-history" class="flex space-y-2 flex-wrap">
        <RecordCard v-for="record in stateRecords.slice(0, 10)" :key="record.id" :record="record" @clickAction="handleShare" />
        <RouterLink
            v-if="stateRecords.length != 0"
            to="/history"
            class="text-center text-sm dark:text-neutral-400 mx-auto p-3 hover:underline"
            >View More</RouterLink
        >
        <h2 v-else class="text-gray-500 dark:text-gray-400 tracking-wider w-full py-2">Nothing Yet...</h2>
    </section>
    <ModalBase :modalData="shareModal">
        <template #content>
            <div class="py-3">Copy link to clipboard to share it.</div>
        </template>
        <template #controls>
            <ButtonClipboard :text="shareLink" />
        </template>
    </ModalBase>
</template>
