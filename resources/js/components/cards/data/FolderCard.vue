<script setup lang="ts">
import type { FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { RelativeHoverCard } from '@/components/cedar-ui/hover-card';
import { ButtonCorner } from '@/components/cedar-ui/button';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import SidebarCard from '@/components/cards/sidebar/SidebarCard.vue';
import MediaTag from '@/components/labels/MediaTag.vue';

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
    <RelativeHoverCard class="w-full" positionClasses="p-0 border-none z-50 -top-5 lg:-left-24" iconHidden :hoverCardDelay="50" :hoverCardLeaveDelay="50">
        <template #content>
            <img
                :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Thumbnail"
                class="aspect-2-3 hidden h-32 object-cover shadow-md lg:block"
                loading="lazy"
            />
        </template>
        <template #trigger>
            <SidebarCard
                :to="`/${categoryName}/${data.name}`"
                class="text-foreground-1 p-0 lg:p-3"
                @contextmenu="
                    (e: any) => {
                        setContextMenu(e, { items: contextMenuItems });
                    }
                "
            >
                <img
                    :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                    alt="Folder Thumbnail"
                    :class="['aspect-square max-h-16 rounded-t-lg object-cover', 'sm:aspect-2-3 sm:max-h-none sm:w-16 sm:rounded-t-none sm:rounded-l-sm sm:shadow-md', 'lg:hidden']"
                    loading="lazy"
                />

                <div class="flex w-full flex-1 flex-col sm:p-3 lg:contents lg:p-0">
                    <div
                        :class="[
                            'relative flex w-full flex-1 flex-col flex-wrap gap-4 p-3 text-left',
                            'sm:min-h-18 sm:justify-between sm:p-0',
                            'lg:min-h-0 lg:flex-row lg:justify-normal lg:gap-2',
                        ]"
                    >
                        <section class="flex h-fit w-full items-center justify-between gap-4">
                            <h3
                                class="text-foreground-0 w-full truncate"
                                :title="`${data.id}: ${data.series?.title || data.name}\nCreated: ${toFormattedDate(data.created_at || '')}\nUpdated: ${toFormattedDate(data.updated_at || '')}\nScanned: ${toFormattedDate(data.scanned_at || '')}`"
                            >
                                {{ data.series?.title || data.name }}
                            </h3>
                            <div class="flex justify-end gap-1">
                                <ButtonCorner
                                    class="hover:text-primary dark:hover:text-primary-muted hover:dark:bg-surface-1 hover:bg-surface-6 size-7"
                                    :label="'Share Folder'"
                                    :use-default-style="false"
                                    @click.stop.prevent="emit('otherAction', data.id, 'share')"
                                >
                                    <template #icon>
                                        <CircumShare1 width="20" height="20" stroke-width="1" stroke="currentColor" />
                                    </template>
                                </ButtonCorner>
                                <ButtonCorner
                                    :label="'Open Folder'"
                                    :class="
                                        cn(
                                            data.name === props.stateFolderName ? 'text-primary' : 'hover:text-primary',
                                            'dark:hover:text-primary-muted hover:dark:bg-surface-1 hover:bg-surface-6 size-7',
                                        )
                                    "
                                    :use-default-style="false"
                                >
                                    <template #icon>
                                        <CircumFolderOn width="20" height="20" stroke-width="1" stroke="currentColor" />
                                    </template>
                                </ButtonCorner>
                            </div>
                        </section>
                        <section class="flex h-fit w-full flex-col flex-wrap gap-2 text-sm sm:flex-row sm:justify-between">
                            <h4 class="w-full flex-1 truncate text-wrap sm:text-nowrap" :title="`${data.file_count} ${mediaType}${data.file_count !== 1 ? 's' : ''}`">
                                {{ data.file_count }} {{ mediaType }}{{ data.file_count !== 1 ? 's' : '' }}
                            </h4>
                            <h4 class="w-fit truncate text-nowrap sm:text-right lg:hidden xl:block">
                                {{ formatFileSize(data.total_size ?? 0) }}
                            </h4>
                        </section>
                    </div>
                    <div
                        v-if="data.series?.folder_tags?.length"
                        :class="[
                            'flex w-full flex-wrap gap-1 overflow-clip p-3 pt-0 transition-all duration-200 group-hover:[overflow-clip-margin:4px]',
                            'sm:max-h-0 sm:p-0 sm:group-hover:max-h-[26px] sm:group-hover:pt-1',
                        ]"
                        title="Tags"
                    >
                        <MediaTag v-for="(tag, index) in data.series.folder_tags" :key="index" :label="tag.name" />
                    </div>
                </div>
            </SidebarCard>
        </template>
    </RelativeHoverCard>
</template>
