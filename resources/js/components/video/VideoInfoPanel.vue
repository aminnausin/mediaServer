<script setup lang="ts">
import { computed, onMounted, ref, useTemplateRef, watch, nextTick } from 'vue';
import { handleStorageURL, toFormattedDate, toTimeSpan } from '@/service/util';
import { ButtonIcon, ButtonText } from '@/components/cedar-ui/button';
import { getUserViewCount } from '@/service/mediaAPI';
import { ContextMenuItem } from '@/components/cedar-ui/context-menu';
import { CopyToClipboard } from '@/components/cedar-ui/clipboard';
import { useContentStore } from '@/stores/ContentStore';
import { useAuthStore } from '@/stores/AuthStore';
import { BasePopover } from '@/components/cedar-ui/popover';
import { storeToRefs } from 'pinia';
import { HoverCard } from '@/components/cedar-ui/hover-card';
import { BadgeTag } from '@/components/cedar-ui/badge';
import { emitSeek } from '@/service/player/seekBus';
import { useRoute } from 'vue-router';

import useMetaData from '@/composables/useMetaData';
import EditFolder from '@/components/forms/EditFolder.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import EditVideo from '@/components/forms/EditVideo.vue';
import useModal from '@/composables/useModal';

import ProiconsArrowDownload from '~icons/proicons/arrow-download';
import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import CircumShare1 from '~icons/circum/share-1';
import ProiconsEye from '~icons/proicons/eye';
import CircumEdit from '~icons/circum/edit';

const defaultDescription = `No description yet.`;

const { updateVideoData, updateFolderData } = useContentStore();
const { stateVideo, stateFolder } = storeToRefs(useContentStore());
const { userData } = storeToRefs(useAuthStore());

const descriptionRef = useTemplateRef('description');
const popover = useTemplateRef('popover');
const route = useRoute();

const personalViewCount = ref(-1);
const isOverflowing = ref(false);
const isExpanded = ref(false);

const { title, description: parsedDescription, views } = useMetaData(stateVideo);

const editFolderModal = useModal({ title: 'Edit Folder Metadata', submitText: 'Submit Metadata' });
const editVideoModal = useModal({ title: 'Edit Metadata', submitText: 'Submit Metadata' });
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

function handleSeek(seconds: number) {
    emitSeek(seconds);
}

function checkOverflow() {
    if (!descriptionRef.value) return;
    isOverflowing.value = descriptionRef.value.scrollHeight > descriptionRef.value.clientHeight;
}

watch(
    () => stateVideo.value,
    async (current, prev) => {
        if (current.id !== prev?.id) {
            isExpanded.value = false;
        }

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

watch([() => stateVideo.value.description, () => isExpanded.value], () => {
    nextTick(() => checkOverflow());
});

onMounted(() => {
    checkOverflow();
});
</script>

<template>
    <section class="bg-primary-800 dark:bg-primary-dark-800/70 text-foreground-0 z-3 flex w-full flex-wrap gap-4 rounded-xl p-3 text-sm shadow-lg" aria-labelledby="mp4-title">
        <section id="mp4-header-mobile" aria-labelledby="mp4-title-mobile" class="flex w-full flex-wrap items-center gap-1 gap-x-2 sm:hidden">
            <HoverCard :content="title ?? '[File Not Found]'" class="min-w-10 flex-1">
                <template #trigger>
                    <h2
                        id="mp4-title-mobile"
                        :class="['truncate text-xl capitalize', { 'my-auto h-5 w-full animate-pulse rounded-full bg-neutral-300 dark:bg-neutral-700': !stateVideo.id }]"
                    >
                        {{ !stateVideo.id ? '' : (title ?? '[File Not Found]') }}
                    </h2>
                </template>
            </HoverCard>

            <BasePopover
                class="sm:hidden"
                popoverClass="max-w-32! p-1! rounded-md! shadow-xs!"
                :vertical-offset-pixels="36"
                :buttonClass="'p-1! size-6! ml-auto mt-auto'"
                ref="popover"
            >
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
                    <ContextMenuItem
                        disabled
                        :icon="ProiconsArrowDownload"
                        :text="'Download'"
                        :action="
                            () => {
                                popover?.handleClose();
                            }
                        "
                    />
                </template>
            </BasePopover>

            <ul class="flex max-h-5 w-full flex-wrap gap-1 gap-y-4 overflow-clip [overflow-clip-margin:4px] *:*:shadow-sm **:flex **:items-center **:text-xs sm:hidden">
                <li>
                    <BadgeTag :class="'meta-badge gap-0.5'">
                        {{ views }}
                        <HoverCard :content="`You have viewed this ${personalViewCount} time${personalViewCount == 1 ? '' : 's'}`">
                            <template #trigger>
                                <ProiconsEye class="size-4 scale-90 transition-all hover:scale-100 hover:text-neutral-400 dark:hover:text-white" v-if="personalViewCount > 0" />
                            </template>
                        </HoverCard>
                    </BadgeTag>
                </li>

                <li v-if="stateVideo?.metadata?.resolution_height">
                    <BadgeTag :label="stateVideo?.metadata?.resolution_height + 'p'" :class="'meta-badge'" />
                </li>
                <li v-if="stateVideo.date_uploaded">
                    <BadgeTag
                        :title="`Date Uploaded: ${toFormattedDate(new Date(stateVideo.date_uploaded))}\nDate Added: ${toFormattedDate(new Date(stateVideo.date_created))}`"
                        :label="toTimeSpan(stateVideo.date_uploaded, '')"
                        :class="'meta-badge'"
                    />
                </li>

                <li v-if="stateVideo.metadata?.codec">
                    <BadgeTag :title="`Media Codec: ${stateVideo.metadata?.codec}`" :label="stateVideo.metadata?.codec" :class="'meta-badge uppercase'" />
                </li>
            </ul>
        </section>
        <div id="mp4-folder-info" class="group xs:block aspect-2-3 relative hidden h-32 rounded-md object-cover shadow-md">
            <img
                id="folder-thumbnail"
                class="aspect-2-3 h-full rounded-md object-cover ring-1 ring-gray-900/5"
                alt="Folder Cover Art"
                fetchpriority="high"
                :src="handleStorageURL(stateFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
            />

            <ButtonIcon
                v-if="userData"
                class="absolute right-1 bottom-1 size-7 p-0 opacity-0 shadow-md transition-opacity group-hover:opacity-100 focus:opacity-100"
                title="Edit Folder Metadata"
                @click="if (userData) editFolderModal.toggleModal();"
            >
                <template #icon>
                    <CircumEdit height="16" width="16" />
                </template>
            </ButtonIcon>
        </div>
        <div class="group flex w-full min-w-0 flex-1 flex-col gap-2">
            <header class="hidden justify-between gap-2 sm:flex">
                <h2
                    id="mp4-title"
                    :class="[
                        'flex-1 truncate text-xl capitalize',
                        { 'my-auto h-5 animate-pulse rounded-full bg-neutral-300 dark:bg-neutral-700': !stateVideo.id },
                        { 'h-8': stateVideo.id },
                    ]"
                    :title="title ?? 'No file was found at this location'"
                >
                    {{ !stateVideo.id ? '' : (title ?? '[File Not Found]') }}
                </h2>
                <div class="flex h-8 w-fit justify-end gap-2 *:ring-inset lg:min-w-32">
                    <ButtonText v-if="userData" aria-label="edit details" title="Edit Metadata" @click="editVideoModal.toggleModal()">
                        <p class="text-nowrap">Edit Metadata</p>
                    </ButtonText>
                    <ButtonIcon aria-label="download" title="Download Video" class="hidden">
                        <template #icon>
                            <ProiconsArrowDownload height="16" width="16" />
                        </template>
                    </ButtonIcon>
                    <ButtonIcon aria-label="share" title="Share Video" @click="shareVideoModal.toggleModal()">
                        <template #icon>
                            <CircumShare1 height="16" width="16" />
                        </template>
                    </ButtonIcon>
                </div>
            </header>
            <article :class="['text-foreground-1 flex w-full flex-1 flex-col justify-between gap-1', { 'max-h-32': !isExpanded }]">
                <div
                    :class="[
                        `scrollbar-minimal scrollbar-hover overflow-x-clip overflow-y-auto whitespace-pre-wrap`,
                        { 'h-20 sm:h-10': !isExpanded && isOverflowing }, // h-16 and 2.5rem on big screens if show more button exists and not expanded
                        { 'h-[102px] sm:h-15': !isOverflowing }, // otherwise, fill space... I think this makes sense?
                    ]"
                    ref="description"
                    id="media-description"
                >
                    <template v-if="stateVideo.description && parsedDescription">
                        <span v-for="(segment, i) in parsedDescription" :key="i">
                            <template v-if="segment.type === 'timestamp' && segment.seconds !== undefined">
                                <a
                                    :href="`?video=${stateVideo.id}&t=${segment.seconds}`"
                                    @click.prevent="handleSeek(segment.seconds)"
                                    class="text-primary dark:text-foreground-0 hover:underline"
                                    :title="`Seek to ${segment.seconds}`"
                                >
                                    {{ segment.raw }}
                                </a>
                            </template>
                            <template v-else>
                                {{ segment.text }}
                            </template>
                        </span>
                    </template>
                    <template v-else>
                        {{ defaultDescription }}
                    </template>
                </div>
                <ButtonText
                    v-if="isOverflowing || isExpanded"
                    @click="isExpanded = !isExpanded"
                    :class="['hocus:text-foreground-0 block h-auto w-fit p-0 transition-colors', { 'leading-none': !isExpanded }]"
                    :title="isExpanded ? 'Hide expanded description' : 'Show expanded description'"
                    :variant="'ghost'"
                    :aria-expanded="isExpanded"
                    aria-controls="media-description"
                >
                    {{ isExpanded ? 'Show less' : '...more' }}
                </ButtonText>
                <div class="flex w-full flex-1 items-end justify-between gap-2">
                    <div class="hidden h-[22px] items-center justify-start gap-1 truncate sm:flex">
                        <p class="lowercase">{{ views }}</p>

                        <HoverCard :content="`You have viewed this ${personalViewCount} time${personalViewCount == 1 ? '' : 's'}`">
                            <template #trigger>
                                <ProiconsEye class="h-4 w-4 scale-90 transition-all hover:scale-100 hover:text-neutral-400 dark:hover:text-white" v-if="personalViewCount > 0" />
                            </template>
                        </HoverCard>
                        <template v-if="stateVideo?.metadata?.resolution_height">
                            <p>|</p>

                            <HoverCard :content="`Codec: ${stateVideo.metadata.codec ?? 'Unknown'}`">
                                <template #trigger>
                                    <p class="xs:block hidden truncate text-start text-nowrap transition-all hover:text-neutral-400 dark:hover:text-white">
                                        {{ `${stateVideo.metadata.resolution_height}p` }}
                                    </p>
                                </template>
                            </HoverCard>
                        </template>
                        <template v-if="stateVideo.date_uploaded">
                            <p>|</p>
                            <p
                                :title="`Date Uploaded: ${toFormattedDate(new Date(stateVideo.date_uploaded))}\nDate Added: ${toFormattedDate(new Date(stateVideo.date_created))}`"
                                class="truncate text-start text-nowrap"
                            >
                                {{ toTimeSpan(stateVideo.date_uploaded, '') }}
                            </p>
                        </template>
                    </div>
                    <div class="flex max-h-[22px] max-w-full flex-wrap justify-end gap-1 overflow-clip text-end [overflow-clip-margin:4px]">
                        <BadgeTag v-for="(tag, index) in stateVideo?.video_tags" :key="index" :label="tag.name" />
                    </div>
                </div>
            </article>
        </div>
    </section>
    <ModalBase :modalData="editFolderModal" :useControls="false">
        <template #description v-if="stateFolder.series?.editor_id && stateFolder.series.date_updated">
            Last edited by
            <a title="Editor profile" target="_blank" :href="`/profile/${stateFolder.series.editor_id}`" class="hover:text-primary dark:hover:text-primary-muted"
                >@{{ stateFolder.series.editor_id }}</a
            >
            at
            {{ toFormattedDate(new Date(stateFolder.series.date_updated)) }}
        </template>
        <template #content>
            <EditFolder :folder="stateFolder" @handleFinish="handleSeriesUpdate" />
        </template>
    </ModalBase>
    <ModalBase :modalData="editVideoModal" :useControls="false">
        <template #description v-if="stateVideo.metadata?.editor_id && stateVideo.metadata.updated_at">
            Last edited by
            <a title="Editor profile" target="_blank" :href="`/profile/${stateVideo.metadata.editor_id}`" class="hover:text-primary dark:hover:text-primary-muted">
                @{{ stateVideo.metadata.editor_id }}
            </a>
            at
            {{ toFormattedDate(new Date(stateVideo.metadata.updated_at)) }}
        </template>
        <template #content>
            <EditVideo :video="stateVideo" @handleFinish="handleVideoDetailsUpdate" />
        </template>
    </ModalBase>
    <ModalBase :modalData="shareVideoModal">
        <template #description> Copy link to clipboard to share it.</template>

        <template #controls>
            <CopyToClipboard :text="videoURL" />
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

<style lang="css" scoped>
@reference "../../../css/app.css";
.meta-badge {
    @apply h-[22px] bg-neutral-800 opacity-70 transition-opacity hover:text-white hover:opacity-100 dark:bg-neutral-900 dark:hover:bg-neutral-600/90;
}
</style>
