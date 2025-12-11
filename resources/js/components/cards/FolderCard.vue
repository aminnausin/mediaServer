<script setup lang="ts">
import type { FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { RouterLink } from 'vue-router';
import { computed } from 'vue';

import RelativeHoverCard from '@/components/cards/RelativeHoverCard.vue';
import ButtonCorner from '@/components/inputs/ButtonCorner.vue';
import ChipTag from '@/components/labels/ChipTag.vue';

import CircumFolderOn from '~icons/circum/folder-on';
import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';

const emit = defineEmits(['clickAction', 'otherAction']);
const props = defineProps<{
    data: FolderResource;
    categoryName: string;
    stateFolderName: string;
}>();

const { userData } = storeToRefs(useAuthStore());
const { setContextMenu } = useAppStore();

const contextMenuItems = computed(() => {
    if (!userData.value) return [];
    return [
        {
            text: 'Edit',
            icon: CircumEdit,
            action: () => {
                if (!props.data?.id) return;
                emit('otherAction', props.data.id, 'edit');
            },
        },
    ];
});

const mediaType = computed(() => {
    return props.data.is_majority_audio ? 'Track' : 'Episode';
});
</script>

<template>
    <RelativeHoverCard class="w-full" positionClasses="p-0! border-none! z-50 -top-5 lg:-left-24" iconHidden :hoverCardDelay="50" :hoverCardLeaveDelay="50">
        <template #content>
            <img
                :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Thumbnail"
                class="aspect-2-3 hidden h-32 object-cover shadow-md lg:block"
                loading="lazy"
            />
        </template>
        <template #trigger>
            <RouterLink
                :to="`/${categoryName}/${data.name}`"
                class="dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 group relative flex w-full cursor-pointer flex-col flex-wrap divide-gray-300 rounded-lg text-left text-neutral-600 shadow-sm hover:bg-gray-200 sm:flex-row sm:p-3 dark:divide-gray-400 dark:text-neutral-400"
                @contextmenu="
                    (e: any) => {
                        setContextMenu(e, { items: contextMenuItems });
                    }
                "
            >
                <img
                    :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                    alt="Folder Thumbnail"
                    class="xs:block hidden aspect-square max-h-16 rounded-t-lg object-cover shadow-md sm:me-4 sm:w-12 sm:rounded-xs lg:hidden"
                    loading="lazy"
                />
                <span class="relative flex w-full flex-1 flex-col flex-wrap gap-4 p-3 text-left sm:flex-row sm:p-0 lg:gap-2">
                    <section class="flex w-full items-center justify-between gap-4">
                        <h3
                            class="w-full truncate text-gray-900 dark:text-white"
                            :title="`${data.id}: ${props.data.series?.title || props.data.name}\nCreated: ${toFormattedDate(props.data.created_at || '')}\nUpdated: ${toFormattedDate(props.data.updated_at || '')}\nScanned: ${toFormattedDate(props.data.scanned_at || '')}`"
                        >
                            {{ props.data.series?.title || props.data.name }}
                        </h3>
                        <div class="flex justify-end gap-1">
                            <ButtonCorner
                                :positionClasses="'w-7 h-7'"
                                :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                                :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                                :label="'Share Folder'"
                                @click.stop.prevent="emit('otherAction', props.data.id, 'share')"
                            >
                                <template #icon>
                                    <CircumShare1 width="20" height="20" stroke-width="1" stroke="currentColor" />
                                </template>
                            </ButtonCorner>
                            <ButtonCorner
                                :positionClasses="'w-7 h-7'"
                                :textClasses="`${props.data.name === props.stateFolderName ? 'text-violet-600' : 'hover:text-violet-600'} dark:hover:text-violet-500`"
                                :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                                :to="''"
                                :label="'Open Folder'"
                            >
                                <template #icon>
                                    <CircumFolderOn width="20" height="20" stroke-width="1" stroke="currentColor" />
                                </template>
                            </ButtonCorner>
                        </div>
                    </section>
                    <section class="flex w-full flex-col flex-wrap gap-2 text-sm sm:flex-row sm:justify-between">
                        <h4 class="w-full flex-1 truncate text-wrap sm:text-nowrap" :title="`${props.data.file_count} ${mediaType}${props.data.file_count !== 1 ? 's' : ''}`">
                            {{ props.data.file_count }} {{ mediaType }}{{ props.data.file_count !== 1 ? 's' : '' }}
                        </h4>
                        <h4 class="w-fit truncate text-nowrap sm:text-right lg:hidden xl:block">
                            <!-- some other folder statistic or data like number of seasons or if its popular or something -->
                            {{ props.data.total_size ? formatFileSize(props.data.total_size) : '' }}
                        </h4>
                    </section>
                </span>
                <section
                    v-if="props.data.series?.folder_tags?.length"
                    class="flex w-full flex-wrap gap-1 overflow-clip p-3 pt-0 transition-all group-hover:[overflow-clip-margin:4px] sm:max-h-0 sm:p-0 sm:group-hover:max-h-[26px] sm:group-hover:pt-1"
                    title="Tags"
                >
                    <ChipTag
                        v-for="(tag, index) in props.data.series.folder_tags"
                        :key="index"
                        :label="tag.name"
                        :colour="'bg-neutral-200 leading-none shadow-sm dark:bg-neutral-900 hover:bg-violet-600 text-neutral-500 hover:text-neutral-50 dark:hover:bg-violet-600/90 max-h-[22px]!'"
                    />
                </section>
            </RouterLink>
        </template>
    </RelativeHoverCard>
</template>
