<script setup>
import ButtonClipboard from '../pinesUI/ButtonClipboard.vue';
import FolderCard from '../cards/FolderCard.vue';
import RecordCard from '../cards/RecordCard.vue';
import ModalBase from '../pinesUI/ModalBase.vue';
import useModal from '../../composables/useModal';

import { useContentStore } from '../../stores/ContentStore';
import { useAppStore } from '../../stores/AppStore';
import { storeToRefs } from 'pinia';
import { RouterLink } from 'vue-router';
import { ref } from 'vue';

const appStore = useAppStore();
const contentStore = useContentStore();
const shareModal = useModal({ title: 'Share Video' });
const shareLink = ref('');

const { selectedSideBar } = storeToRefs(appStore);
const { records, stateDirectory, stateFolder } = storeToRefs(contentStore);

const handleShare = (link) => {
    if (!link || link[0] !== '/') return;

    shareLink.value = window.location.origin + link;
    shareModal.toggleModal(true);
};
</script>

<template>
    <div class="flex p-1 text-ri">
        <h1 id="sidebar-title" class="text-2xl w-full capitalize dark:text-white">{{ selectedSideBar }}</h1>
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
        <RecordCard v-for="record in records.slice(0, 10)" :key="record.id" :record="record" />
        <RouterLink to="/history" class="text-center text-sm dark:text-neutral-400 mx-auto p-3 hover:underline">View More</RouterLink>
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
