<script setup>
import ButtonClipboard from '../pinesUI/ButtonClipboard.vue';
import useMetaData from '../../composables/useMetaData';
import ModalBase from '../pinesUI/ModalBase.vue';
import useModal from '../../composables/useModal';
import EditVideo from '../forms/EditVideo.vue';
import EditFolder from '../forms/EditFolder.vue';

import CircumShare1 from '~icons/circum/share-1';

import { watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useContentStore } from '../../stores/ContentStore';
import { useAuthStore } from '../../stores/AuthStore';
import ButtonIcon from '../inputs/ButtonIcon.vue';
import ButtonText from '../inputs/ButtonText.vue';


const ContentStore = useContentStore();
const AuthStore = useAuthStore();
const { stateVideo } = storeToRefs(ContentStore);
const { userData } = storeToRefs(AuthStore);
const { updateVideoData } = ContentStore;


const defaultDescription = `After defeating the
                    Demon Lord, Himmel the Hero, priest Heiter, dwarf warrior Eisen, and elf mage
                    Frieren return to the royal capital. After their procession, they view a meteor
                    shower and discuss their future plans. Himmel, Heiter, and Eisen are ready to retire
                    from adventuring after their ten-year quest. Frieren, whose lifespan is much longer,
                    considers the ten years to be trivially short and plans to travel and learn new
                    spells. To her colleagues' amusement, she promises to show them a better site to
                    observe the meteor shower at its next occurrence in 50 years. Frieren keeps her word
                    and returns 50 years later to find that Himmel and Heiter have become elderly, and
                    Eisen is middle-aged. The week-long journey to Frieren's viewing site reminds the
                    party of their past adventures. Himmel the Hero dies of old age shortly after the
                    expedition. At his funeral, Frieren tearfully realizes she did not adequately get to
                    know him and decides to learn as much about humans as possible. 20 years later, she
                    visits Heiter to find that he has adopted a war orphan, nine year old Fern. Heiter,
                    suffering from death anxiety in his advanced age, asks Frieren to research
                    life-extending magic and tutor Fern in magic in her spare time. She agrees after
                    seeing Fern is already remarkably skilled despite her youth.`;

const metaData = useMetaData({ ...stateVideo.value.attributes, id: stateVideo.value.id }, stateVideo.value);
const editFolderModal = useModal({ title: 'Edit Folder Details', submitText: 'Submit Details' });
const editVideoModal = useModal({ title: 'Edit Video Details', submitText: 'Submit Details' });
const shareVideoModal = useModal({ title: 'Share Video' });

const handlePropsUpdate = () => {
    metaData.updateData({ ...stateVideo.value.attributes, id: stateVideo.value.id });
}

const handleVideoDetailsUpdate = (res) => {
    if (res?.data) {
        stateVideo.value = { index: stateVideo.value.index, ...res.data }
        updateVideoData({ index: stateVideo.value.index, ...res.data }, stateVideo.value.index);
    }
    editVideoModal.toggleModal(false);
}

const handleSeriesUpdate = (res) => {
    if (res?.data) {
        stateVideo.value = { index: stateVideo.value.index, ...res.data }
        updateVideoData({ index: stateVideo.value.index, ...res.data }, stateVideo.value.index);
    }
    editVideoModal.toggleModal(false);
}

watch(() => stateVideo.value, handlePropsUpdate, { immediate: true, deep: true });
</script>

<template>
    <div
        class="p-6 w-full mx-auto dark:bg-primary-dark-800/70 bg-primary-800 rounded-xl shadow-lg flex justify-center sm:justify-between gap-4 flex-wrap sm:flex-nowrap overflow-hidden">
        <div id="mp4-description" class="flex items-center gap-4 w-full md:w-2/3 ">
            <img id="folder-thumbnail" class="h-28 object-contain rounded-md shadow-md"
                :src="stateVideo?.attributes?.thumbnail?.url ?? 'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'"
                alt="Folder Cover Art"
                @click="() => {if(userData) editFolderModal.toggleModal()}"
                title="Edit Folder Details">
            <div class="h-full flex flex-col gap-2">
                <div id="mp4-title" class="text-xl font-medium line capitalize">
                    {{ metaData?.fields.title ?? '[Video Name]' }}
                </div>
                <p class="dark:text-slate-400 text-slate-500 line-clamp-3 text-sm">
                    {{ metaData?.fields?.description ?? defaultDescription }}
                </p>
            </div>
        </div>
        <div id="mp4-details"
            class="container flex sm:w-auto sm:flex-col justify-between lg:min-w-32 items-center sm:items-end gap-3 flex-wrap flex-1 w-full"
            role="group">
            <section class="flex gap-2 justify-end">
                <ButtonText v-if="userData" aria-label="edit details" title="Edit Video Details" @click="editVideoModal.toggleModal()">
                    <template #text>
                        <p class="text-nowrap">
                            Edit Details
                        </p>
                    </template>
                </ButtonText>
                <ButtonIcon aria-label="share" title="Share Video" @click="shareVideoModal.toggleModal()">
                    <template #icon>
                        <CircumShare1 height="24" width="24" />
                    </template>
                </ButtonIcon>
            </section>
            <section
                class="flex flex-1 sm:flex-none gap flex-col items-end text-sm dark:text-slate-400 text-slate-500 max-w-full">
                <p>
                    {{ metaData?.fields.views }}
                </p>
                <p class="line-clamp-1">
                    {{ stateVideo?.tags ?? '#atmospheric #sad #action' }}
                </p>
            </section>
        </div>
    </div>
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #content>
            <div class="pt-3">
                <EditFolder :video="stateVideo" @handleFinish="handleSeriesUpdate" />
            </div>
        </template>
    </ModalBase>
    <ModalBase :modalData="editVideoModal" :useControls="false">
        <template #content>
            <div class="pt-3">
                <EditVideo :video="stateVideo" @handleFinish="handleVideoDetailsUpdate" />
            </div>
        </template>
    </ModalBase>
    <ModalBase :modalData="shareVideoModal">
        <template #content>
            <div class="py-3">
                Copy link to clipboard to share it.
            </div>
        </template>
        <template #controls>
            <ButtonClipboard :text="metaData.fields.url" tabindex="1"/>
        </template>
    </ModalBase>
</template>
