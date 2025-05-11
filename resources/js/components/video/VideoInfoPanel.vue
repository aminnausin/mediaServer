<script setup lang="ts">
import type { FolderResource, UserResource, VideoResource } from '@/types/resources';

import { computed, ref, useTemplateRef, watch, type Ref } from 'vue';
import { handleStorageURL, toFormattedDate, toTimeSpan } from '@/service/util';
import { getUserViewCount } from '@/service/mediaAPI';
import { useContentStore } from '@/stores/ContentStore';
import { useAuthStore } from '@/stores/AuthStore';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';

import ButtonClipboard from '@/components/pinesUI/ButtonClipboard.vue';
import ContextMenuItem from '@/components/pinesUI/ContextMenuItem.vue';
import useMetaData from '@/composables/useMetaData';
import EditFolder from '@/components/forms/EditFolder.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import EditVideo from '@/components/forms/EditVideo.vue';
import HoverCard from '@/components/cards/HoverCard.vue';
import useModal from '@/composables/useModal';
import ChipTag from '@/components/labels/ChipTag.vue';
import Popover from '@/components/pinesUI/Popover.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import CircumShare1 from '~icons/circum/share-1';
import ProiconsEye from '~icons/proicons/eye';
import CircumEdit from '~icons/circum/edit';

const { updateVideoData, updateFolderData } = useContentStore();
const { userData } = storeToRefs(useAuthStore()) as unknown as {
    userData: Ref<UserResource>;
};
const { stateVideo, stateFolder } = storeToRefs(useContentStore()) as unknown as {
    stateVideo: Ref<VideoResource>;
    stateFolder: Ref<FolderResource>;
};

const popover = useTemplateRef('popover');
const route = useRoute();

const personalViewCount = ref(-1);
const defaultDescription = `No description yet.`;

const metaData = useMetaData(stateVideo.value);
const editFolderModal = useModal({ title: 'Edit Folder Details', submitText: 'Submit Details' });
const editVideoModal = useModal({ title: 'Edit Video Details', submitText: 'Submit Details' });
const shareVideoModal = useModal({ title: 'Share Video' });

const videoURL = computed(() => {
    return document.location.origin + route.path + (stateVideo.value.id ? `?video=${stateVideo.value.id}` : '');
});

const handleVideoDetailsUpdate = (res: any) => {
    updateVideoData(res?.data);
    editVideoModal.toggleModal(false);
};

const handleSeriesUpdate = (res: any) => {
    updateFolderData(res?.data);
    editFolderModal.toggleModal(false);
};

watch(
    () => stateVideo.value,
    async () => {
        metaData.updateData(stateVideo.value);
        if (!userData.value?.id || !stateVideo.value.metadata) {
            personalViewCount.value = -1;
            return;
        }

        const { data } = await getUserViewCount(stateVideo.value.metadata.id);

        personalViewCount.value = isNaN(parseInt(data)) ? -1 : parseInt(data);
    },
    { immediate: true, deep: true },
);

watch(
    () => userData.value,
    () => {
        if (!userData.value?.id) personalViewCount.value = -1;
    },
);
</script>

<template>
    <section class="flex flex-wrap gap-4 p-3 w-full rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-primary-800 z-[3] text-neutral-500 dark:text-neutral-400">
        <section id="mp4-header-mobile" class="flex items-center w-full sm:hidden flex-wrap gap-2">
            <HoverCard :content="`You have viewed this ${personalViewCount} time${personalViewCount == 1 ? '' : 's'}`" class="flex-1 min-w-10">
                <template #trigger>
                    <h2 class="text-xl capitalize truncate text-gray-900 dark:text-white">
                        {{ metaData?.fields.title ?? '[File Not Found]' }}
                    </h2>
                </template>
            </HoverCard>

            <section :class="`contents sm:hidden`">
                <Popover popoverClass="!max-w-32 !p-1 !rounded-md !shadow-sm" :vertical-offset-pixels="36" :buttonClass="'!p-1 w-6 h-6 ml-auto mt-auto'" ref="popover">
                    <template #buttonIcon>
                        <ProiconsMoreVertical class="h-4 w-4" />
                    </template>
                    <template #content>
                        <ContextMenuItem
                            :icon="CircumEdit"
                            :text="'Edit'"
                            :action="
                                () => {
                                    popover?.handleClose();
                                    editVideoModal.toggleModal();
                                }
                            "
                        />
                        <ContextMenuItem
                            :icon="CircumShare1"
                            :text="'Share'"
                            :action="
                                () => {
                                    popover?.handleClose();
                                    shareVideoModal.toggleModal();
                                }
                            "
                        />
                    </template>
                </Popover>
            </section>

            <span class="sm:hidden flex flex-wrap w-full gap-1 gap-y-4 overflow-clip [overflow-clip-margin:4px] h-[20px]">
                <ChipTag
                    v-if="stateVideo.date_uploaded"
                    :title="`Date Uploaded: ${toFormattedDate(new Date(stateVideo.date_uploaded))}`"
                    :label="toTimeSpan(stateVideo.date_uploaded, '')"
                    :colour="'bg-neutral-200 leading-none shadow dark:bg-neutral-900 hover:bg-neutral-600 text-neutral-500 hover:text-neutral-50 hover:dark:bg-neutral-600/90 !max-h-[22px] text-xs flex items-center'"
                />
                <ChipTag
                    :class="'flex gap-0.5 items-center'"
                    :colour="'bg-neutral-200 leading-none shadow dark:bg-neutral-900 hover:bg-neutral-600 text-neutral-500 hover:text-neutral-50 hover:dark:bg-neutral-600/90 !max-h-[22px] text-xs'"
                >
                    <template #content>
                        {{ metaData?.fields.views }}
                        <HoverCard :content="`You have viewed this ${personalViewCount} time${personalViewCount == 1 ? '' : 's'}`">
                            <template #trigger>
                                <ProiconsEye class="w-4 h-4 scale-90 hover:scale-100 transition-all hover:text-neutral-400 dark:hover:text-white" v-if="personalViewCount > 0" />
                            </template>
                        </HoverCard>
                    </template>
                </ChipTag>

                <ChipTag
                    v-if="stateVideo?.metadata?.resolution_height"
                    :label="stateVideo?.metadata?.resolution_height + 'p'"
                    :colour="'bg-neutral-200 leading-none shadow dark:bg-neutral-900 hover:bg-neutral-600 text-neutral-500 hover:text-neutral-50 hover:dark:bg-neutral-600/90 !max-h-[22px] text-xs flex items-center'"
                />
            </span>
        </section>
        <section id="mp4-folder-info" class="hidden xs:block h-32 my-auto object-cover rounded-md shadow-md aspect-2/3 mb-auto relative group">
            <img
                id="folder-thumbnail"
                class="h-full object-cover rounded-md aspect-2/3 ring-1 ring-gray-900/5"
                :src="handleStorageURL(stateFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
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
        </section>
        <section class="flex flex-col gap-2 flex-1 min-w-0 w-full group">
            <section class="hidden sm:flex justify-between gap-2">
                <h2 id="mp4-title" class="text-xl truncate capitalize h-8 text-gray-900 dark:text-white" :title="metaData?.fields.title ?? 'no file was found at this location'">
                    {{ metaData?.fields.title ?? '[File Not Found]' }}
                </h2>
                <section class="flex gap-2 justify-end h-8 lg:min-w-32 max-w-64 w-fit">
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
            </section>
            <HoverCard :content="metaData?.fields?.description" :hover-card-delay="800" :margin="10">
                <template #trigger>
                    <div :class="`h-[3.75rem] overflow-y-auto overflow-x-clip text-sm whitespace-pre-wrap scrollbar-minimal scrollbar-hover `">
                        {{ metaData?.fields?.description || defaultDescription }}
                    </div>
                </template>
            </HoverCard>
            <span class="flex gap-2 items-end justify-between text-sm w-full flex-1">
                <span class="hidden sm:flex items-center justify-start gap-1 truncate h-[22px]">
                    <p class="lowercase">{{ metaData?.fields.views }}</p>

                    <HoverCard :content="`You have viewed this ${personalViewCount} time${personalViewCount == 1 ? '' : 's'}`">
                        <template #trigger>
                            <ProiconsEye class="w-4 h-4 scale-90 hover:scale-100 transition-all hover:text-neutral-400 dark:hover:text-white" v-if="personalViewCount > 0" />
                        </template>
                    </HoverCard>

                    <p v-if="stateVideo.date_uploaded" :title="`Date Uploaded: ${toFormattedDate(new Date(stateVideo.date_uploaded))}`" class="text-nowrap text-start truncate">
                        {{ ' | ' + toTimeSpan(stateVideo.date_uploaded, '') }}
                    </p>
                    <p class="text-nowrap text-start truncate hidden xs:block" v-else-if="stateVideo?.metadata?.resolution_height">
                        {{ ` | ${stateVideo?.metadata?.resolution_height}p` }}
                    </p>
                </span>
                <section class="flex justify-end text-end text-sm max-w-full overflow-clip [overflow-clip-margin:4px] gap-1 flex-wrap max-h-[22px]">
                    <ChipTag v-for="(tag, index) in stateVideo?.video_tags" v-bind:key="index" :label="tag.name" />
                </section>
            </span>
        </section>
    </section>
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
            <ButtonClipboard :text="videoURL" />
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
