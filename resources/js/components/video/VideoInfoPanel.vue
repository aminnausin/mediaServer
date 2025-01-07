<script setup>
import { useContentStore } from '@/stores/ContentStore';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { watch } from 'vue';

import ButtonClipboard from '@/components/pinesUI/ButtonClipboard.vue';
import useMetaData from '@/composables/useMetaData';
import EditFolder from '@/components/forms/EditFolder.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import EditVideo from '@/components/forms/EditVideo.vue';
import HoverCard from '@/components/cards/HoverCard.vue';
import useModal from '@/composables/useModal';
import ChipTag from '@/components/labels/ChipTag.vue';

import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';

const { stateVideo, stateFolder } = storeToRefs(useContentStore());
const { userData } = storeToRefs(useAuthStore());
const { updateVideoData, updateFolderData } = useContentStore();

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

const metaData = useMetaData({ ...stateVideo.value, id: stateVideo.value.id }, stateVideo.value);
const editFolderModal = useModal({ title: 'Edit Folder Details', submitText: 'Submit Details' });
const editVideoModal = useModal({ title: 'Edit Video Details', submitText: 'Submit Details' });
const shareVideoModal = useModal({ title: 'Share Video' });

const handlePropsUpdate = () => {
    metaData.updateData({ ...stateVideo.value, id: stateVideo.value.id });
};

const handleVideoDetailsUpdate = (res) => {
    updateVideoData(res?.data);
    editVideoModal.toggleModal(false);
};

const handleSeriesUpdate = (res) => {
    updateFolderData(res?.data);
    editFolderModal.toggleModal(false);
};

watch(() => stateVideo.value, handlePropsUpdate, { immediate: true, deep: true });
</script>

<template>
    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 p-3 overflow-clip w-full rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-primary-800">
        <div id="mp4-header-mobile" class="flex items-center justify-between w-full sm:hidden gap-2 flex-wrap">
            <h2 class="text-xl font-medium line-clamp-1 capitalize">
                {{ metaData?.fields.title ?? '[Video Name]' }}
            </h2>
            <span class="flex gap-1 flex-row flex-wrap h-[22px] overflow-hidden">
                <ChipTag v-for="(tag, index) in stateVideo?.video_tags" v-bind:key="index" :label="tag.name" />
            </span>
        </div>

        <div id="mp4-description-desktop" class="flex gap-4 sm:flex-1 shrink-0">
            <div class="h-32 my-auto object-cover rounded-md shadow-md aspect-2/3 mb-auto relative group">
                <img
                    id="folder-thumbnail"
                    class="h-full object-cover rounded-md aspect-2/3 ring-1 ring-gray-900/5"
                    :src="
                        stateFolder?.series?.thumbnail_url ??
                        'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'
                    "
                    alt="Folder Cover Art"
                />

                <ButtonIcon
                    v-if="userData"
                    class="absolute bottom-1 right-1 h-8 shadow-md shadow-violet-700 opacity-0 group-hover:opacity-100 transition-opacity duration-200 ease-in-out"
                    title="Edit Folder Details"
                    @click="
                        () => {
                            if (userData) editFolderModal.toggleModal();
                        }
                    "
                >
                    <template #icon>
                        <CircumEdit height="16" width="16" />
                    </template>
                </ButtonIcon>
            </div>
            <div class="flex flex-col gap-2 w-full group">
                <h2 id="mp4-title" class="text-xl font-medium line-clamp-1 capitalize hidden sm:block h-8">
                    {{ metaData?.fields.title ?? '[Video Name]' }}
                </h2>
                <HoverCard :content="metaData?.fields?.description ?? defaultDescription" :hover-card-delay="800" :margin="10">
                    <template #trigger>
                        <div :class="`h-[3.75rem] overflow-y-auto cursor-pointer dark:text-slate-400 text-slate-500 text-sm whitespace-pre-wrap scrollbar-minimal scrollbar-hover`">
                            {{ metaData?.fields?.description ?? defaultDescription }}
                        </div>
                    </template>
                </HoverCard>

                <span class="flex flex-1 gap-2 items-end justify-between text-sm dark:text-slate-400 text-slate-500 max-w-full">
                    <p class="text-nowrap text-ellipsis flex-1 h-8 sm:h-[22px] flex items-center justify-start">
                        {{ metaData?.fields.views }}
                    </p>
                    <section class="flex gap-2 justify-end h-8 sm:hidden">
                        <ButtonIcon v-if="userData" aria-label="edit details" title="Edit Video Details" @click="editVideoModal.toggleModal()">
                            <template #icon>
                                <CircumEdit height="16" width="16" />
                            </template>
                        </ButtonIcon>
                        <ButtonIcon aria-label="share" title="Share Video" @click="shareVideoModal.toggleModal()">
                            <template #icon>
                                <CircumShare1 height="16" width="16" />
                            </template>
                        </ButtonIcon>
                    </section>
                </span>
            </div>
        </div>

        <div id="mp4-details" class="hidden sm:flex flex-col lg:min-w-32 max-w-64 w-fit gap-4 justify-between" role="group">
            <section class="flex gap-2 justify-end h-8">
                <ButtonText v-if="userData" aria-label="edit details" title="Edit Video Details" @click="editVideoModal.toggleModal()" class="text-sm">
                    <template #text>
                        <p class="text-nowrap">Edit Details</p>
                        <!-- <CircumEdit height="24" width="24" /> -->
                    </template>
                </ButtonText>
                <ButtonIcon aria-label="share" title="Share Video" @click="shareVideoModal.toggleModal()">
                    <template #icon>
                        <CircumShare1 height="16" width="16" />
                    </template>
                </ButtonIcon>
            </section>
            <section class="flex flex-col justify-end text-end text-sm dark:text-slate-400 text-slate-500 max-w-full overflow-clip gap-1">
                <span class="flex gap-1 flex-row flex-wrap max-h-[22px] overflow-hidden justify-end">
                    <ChipTag v-for="(tag, index) in stateVideo?.video_tags" v-bind:key="index" :label="tag.name" />
                </span>
            </section>
        </div>
    </div>
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #content>
            <div class="pt-2">
                <EditFolder :folder="stateFolder" @handleFinish="handleSeriesUpdate" />
            </div>
        </template>
    </ModalBase>
    <ModalBase :modalData="editVideoModal" :useControls="false">
        <template #content>
            <div class="pt-2">
                <EditVideo :video="stateVideo" @handleFinish="handleVideoDetailsUpdate" />
            </div>
        </template>
    </ModalBase>
    <ModalBase :modalData="shareVideoModal">
        <template #content>
            <div class="py-3">Copy link to clipboard to share it.</div>
        </template>
        <template #controls>
            <ButtonClipboard :text="metaData.fields.url" tabindex="1" />
        </template>
    </ModalBase>
</template>

<style lang="css">
/* Custom scrollbar styling */
.custom-scrollbar::-webkit-scrollbar {
    width: 8px; /* Width of the scrollbar */
    opacity: 0; /* Initially hidden */
    transition: opacity 0.3s ease; /* Fade-in effect */
} /* Show scrollbar on parent hover */
.hover\:scrollbar-visible:hover .custom-scrollbar::-webkit-scrollbar {
    opacity: 1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #6b7280; /* Use Tailwind color for thumb */
    border-radius: 10px; /* Roundness of the thumb */
    border: 2px solid #f9fafb; /* Border around the thumb */
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f3f4f6; /* Use Tailwind color for track */
    border-radius: 10px; /* Roundness of the track */
} /* Hide scrollbar arrows on Windows */
.custom-scrollbar::-webkit-scrollbar-button {
    display: none;
}
</style>
