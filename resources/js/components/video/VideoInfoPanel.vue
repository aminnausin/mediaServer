<script setup lang="ts">
import { computed, onMounted, ref, useTemplateRef, watch, nextTick } from 'vue';
import { handleStorageURL, toTimeSpan, formatFileSize } from '@/service/util';
import { getMediaDateDescription } from '@/service/media/mediaFormatter';
import { ButtonIcon, ButtonText } from '@/components/cedar-ui/button';
import { getUserViewCount } from '@/service/mediaAPI';
import { ContextMenuItem } from '@/components/cedar-ui/context-menu';
import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { useAuthStore } from '@/stores/AuthStore';
import { BasePopover } from '@/components/cedar-ui/popover';
import { storeToRefs } from 'pinia';
import { HoverCard } from '@/components/cedar-ui/hover-card';
import { MediaType } from '@/types/types';
import { BadgeTag } from '@/components/cedar-ui/badge';
import { emitSeek } from '@/service/player/seekBus';
import { useRoute } from 'vue-router';
import { toast } from '@aminnausin/cedar-ui';

import EditFolderModal from '@/components/modals/EditFolderModal.vue';
import EditMediaModal from '@/components/modals/EditMediaModal.vue';
import useMetaData from '@/composables/useMetaData';
import ShareModal from '@/components/modals/ShareModal.vue';

import ProiconsArrowDownload from '~icons/proicons/arrow-download';
import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import CircumShare1 from '~icons/circum/share-1';
import ProiconsEye from '~icons/proicons/eye';
import CircumEdit from '~icons/circum/edit';
import LazyImage from '../lazy/LazyImage.vue';

const defaultDescription = `No description yet.`;

const { stateVideo, stateFolder } = storeToRefs(useContentStore());
const { userData } = storeToRefs(useAuthStore());
const { title, description: parsedDescription, views } = useMetaData(stateVideo);

const descriptionRef = useTemplateRef('description');
const popover = useTemplateRef('popover');
const modal = useModalStore();
const route = useRoute();

const personalViewCount = ref<number | null>(null);
const isOverflowing = ref(false);
const isExpanded = ref(false);

const mediaTypeDescription = computed(() => {
    return stateVideo.value?.metadata?.media_type === MediaType.AUDIO || stateFolder.value?.is_majority_audio ? 'Track' : 'Video';
});

const mediaDateDescription = computed(() => getMediaDateDescription(stateVideo.value));

const handleShare = () => {
    if (!stateVideo.value.id) {
        toast.error('ID Missing');
        return;
    }

    modal.open(ShareModal, { title: `Share ${mediaTypeDescription.value}`, shareLink: `${document.location.origin}${route.path}?video=${stateVideo.value.id}` });
};

const handleEdit = () => {
    if (!stateVideo.value.id) {
        toast.error('ID Missing');
        return;
    }

    const metadataInfo = stateVideo.value.metadata ? { titleTooltip: `UUID: ${stateVideo.value.metadata.uuid}` } : {};
    modal.open(EditMediaModal, {
        title: `Edit ${mediaTypeDescription.value} Metadata`,
        mediaResource: stateVideo.value,
        ...metadataInfo,
    });
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
            personalViewCount.value = null;
            return;
        }

        const { data } = await getUserViewCount(stateVideo.value.metadata.id);

        personalViewCount.value = Number.isNaN(Number.parseInt(data)) ? null : Number.parseInt(data);
    },
    { immediate: true, deep: true },
);

watch(
    () => userData.value,
    () => {
        if (!userData.value?.id) personalViewCount.value = null;
    },
);

watch([() => stateVideo.value.description, () => isExpanded.value], (values, oldValues) => {
    nextTick(() => checkOverflow());
    if (!values[1] && oldValues[0]) {
        document.getElementById('mp4-info-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

onMounted(() => {
    checkOverflow();
});
</script>

<template>
    <section
        class="dark:bg-primary-dark-800/70 text-foreground-0 group bg-surface-2 z-3 flex w-full scroll-mt-16 flex-wrap gap-4 rounded-lg p-3 text-sm shadow-sm sm:bg-neutral-50"
        id="mp4-info-panel"
        aria-labelledby="mp4-title"
    >
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
                    <ProiconsMoreVertical class="size-4" />
                </template>
                <template #content>
                    <ContextMenuItem
                        :icon="CircumEdit"
                        :text="'Edit'"
                        :action="
                            () => {
                                popover?.handleClose();
                                handleEdit();
                            }
                        "
                    />
                    <ContextMenuItem
                        :icon="CircumShare1"
                        :text="'Share'"
                        :action="
                            () => {
                                popover?.handleClose();
                                handleShare();
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
                        <HoverCard :content="`You have viewed this ${personalViewCount} time${personalViewCount == 1 ? '' : 's'}`" v-if="personalViewCount">
                            <template #trigger>
                                <ProiconsEye class="size-4 scale-90 transition-all hover:scale-100 hover:text-neutral-400 dark:hover:text-white" />
                            </template>
                        </HoverCard>
                    </BadgeTag>
                </li>

                <li v-if="stateVideo.metadata?.resolution_height">
                    <HoverCard :class="'shadow-none!'">
                        <template #trigger>
                            <BadgeTag :label="stateVideo.metadata.resolution_height + 'p'" :class="'meta-badge shadow-sm'" />
                        </template>
                        <template #content>
                            <p class="text-foreground-1">Resolution: {{ `${stateVideo.metadata.resolution_width}x${stateVideo.metadata.resolution_height}` }}</p>
                            <p class="text-foreground-1" v-if="stateVideo.file_size">Size: {{ formatFileSize(stateVideo.file_size) }}</p>
                            <p class="text-foreground-1">Codec: {{ stateVideo.metadata.codec ?? 'Unknown' }}</p>
                        </template>
                    </HoverCard>
                </li>
                <li v-if="stateVideo.file_modified_at">
                    <HoverCard :content="mediaDateDescription" :class="'shadow-none!'">
                        <template #trigger>
                            <BadgeTag
                                class="shadow-sm"
                                :title="toTimeSpan(stateVideo.file_modified_at, '')"
                                :label="toTimeSpan(stateVideo.file_modified_at, '')"
                                :class="'meta-badge'"
                            />
                        </template>
                    </HoverCard>
                </li>

                <li v-if="stateVideo.metadata?.codec">
                    <BadgeTag :title="`Media Codec: ${stateVideo.metadata?.codec}`" :label="stateVideo.metadata?.codec" :class="'meta-badge uppercase'" />
                </li>
            </ul>
        </section>
        <div id="mp4-folder-info" class="xs:block aspect-2-3 relative hidden h-32 rounded-md object-cover shadow-md">
            <LazyImage
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
                @click="if (userData) modal.open(EditFolderModal, { cachedFolder: stateFolder });"
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
                    :class="['flex-1 truncate text-xl capitalize', { 'suspense-rounded h-6': stateVideo.id < 1 }, { 'h-8': stateVideo.id > 1 }]"
                    :title="title ?? 'No file was found at this location'"
                >
                    {{ stateVideo.id < 1 ? '' : (title ?? '[File Not Found]') }}
                </h2>
                <div class="flex h-8 w-fit justify-end gap-2 select-none *:ring-inset lg:min-w-32">
                    <ButtonText v-if="userData" aria-label="edit details" title="Edit Metadata" @click="handleEdit">
                        <p class="text-nowrap">Edit Metadata</p>
                    </ButtonText>
                    <ButtonIcon aria-label="download" :title="`Download ${mediaTypeDescription}`" class="hidden">
                        <template #icon>
                            <ProiconsArrowDownload height="16" width="16" />
                        </template>
                    </ButtonIcon>
                    <ButtonIcon aria-label="share" :title="`Share ${mediaTypeDescription}`" @click="handleShare">
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
                        { 'h-25.5 sm:h-15': !isExpanded && !isOverflowing }, // otherwise, fill space... I think this makes sense?
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
                    <div class="hidden h-5.5 items-center justify-start gap-1 truncate *:cursor-default sm:flex">
                        <template v-if="personalViewCount">
                            <HoverCard :content="`You have viewed this ${personalViewCount} time${personalViewCount == 1 ? '' : 's'}`">
                                <template #trigger>
                                    <div class="hover:text-primary group flex cursor-default items-center justify-start gap-1 truncate transition-colors">
                                        <p class="lowercase">{{ views }}</p>
                                        <ProiconsEye class="size-4 scale-90 transition-transform group-hover:scale-100" />
                                    </div>
                                </template>
                            </HoverCard>
                        </template>
                        <template v-else>
                            <p class="lowercase">{{ views }}</p>
                        </template>
                        <template v-if="stateVideo?.metadata?.resolution_height">
                            <p>|</p>

                            <HoverCard>
                                <template #trigger>
                                    <p class="xs:block hover:text-primary hidden truncate text-start text-nowrap transition-all">
                                        {{ `${stateVideo.metadata.resolution_height}p` }}
                                    </p>
                                </template>
                                <template #content>
                                    <p class="text-foreground-1">Resolution: {{ `${stateVideo.metadata.resolution_width}x${stateVideo.metadata.resolution_height}` }}</p>
                                    <p class="text-foreground-1" v-if="stateVideo.file_size">Size: {{ formatFileSize(stateVideo.file_size) }}</p>
                                    <p class="text-foreground-1">Codec: {{ stateVideo.metadata.codec ?? 'Unknown' }}</p>
                                </template>
                            </HoverCard>
                        </template>
                        <template v-if="stateVideo.file_modified_at">
                            <p>|</p>
                            <HoverCard :content="mediaDateDescription">
                                <template #trigger>
                                    <p class="hover:text-primary truncate text-start text-nowrap transition-colors">
                                        {{ toTimeSpan(stateVideo.file_modified_at, '') }}
                                    </p>
                                </template>
                            </HoverCard>
                        </template>
                    </div>
                    <div class="flex max-h-5.5 max-w-full flex-wrap justify-end gap-1 overflow-clip text-end [overflow-clip-margin:4px]">
                        <BadgeTag v-for="(tag, index) in stateVideo?.video_tags" :key="index" :label="tag.name" />
                    </div>
                </div>
            </article>
        </div>
    </section>
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
@reference "@/../css/app.css";
.meta-badge {
    @apply h-5.5 bg-neutral-800 opacity-70 transition-opacity hover:text-white hover:opacity-100 dark:bg-neutral-900 dark:hover:bg-neutral-600/90;
}
</style>
