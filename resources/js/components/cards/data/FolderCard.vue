<script setup lang="ts">
import type { FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { breakpointsTailwind, useBreakpoints } from '@vueuse/core';
import { RelativeHoverCard } from '@/components/cedar-ui/hover-card';
import { ButtonCorner } from '@/components/cedar-ui/button';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import SidebarCard from '@/components/cards/sidebar/SidebarCard.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';
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

const breakPoints = useBreakpoints(breakpointsTailwind);
const isDesktop = computed(() => breakPoints.isGreaterOrEqual('lg'));

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
        {
            text: 'Open in New Tab',
            icon: CircumFolderOn,
            action: () => {
                if (!props.data?.id) return;
                window.open(`/${props.categoryName}/${props.data.name}`, '_blank');
            },
        },
    ];
});

const mediaType = computed(() => {
    return props.data.is_majority_audio ? 'Track' : 'Episode';
});
</script>

<template>
    <RelativeHoverCard
        class="static w-full lg:relative"
        positionClasses="p-0 border-none -top-5 lg:-left-24"
        iconHidden
        :hoverCardDelay="50"
        :hoverCardLeaveDelay="50"
        :use-background="false"
    >
        <template #content>
            <LazyImage
                :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Thumbnail"
                class="aspect-2-3 content-auto hidden h-32 object-cover shadow-md [contain-intrinsic-size:auto_128px] lg:block"
                loading="lazy"
            />
        </template>
        <template #trigger>
            <SidebarCard
                :to="`/${categoryName}/${data.name}`"
                class="text-foreground-1 p-0 [--tw-ring-inset:initial]! [contain-intrinsic-size:auto_180px] sm:[contain-intrinsic-size:auto_96px] lg:p-3 lg:[contain-intrinsic-size:auto_80px] lg:ring-inset"
                @contextmenu="
                    (e: any) => {
                        setContextMenu(e, { items: contextMenuItems });
                    }
                "
            >
                <LazyImage
                    alt="Folder Thumbnail"
                    :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                    :wrapper-class="
                        cn('w-full sm:w-fit h-fit relative dark:bg-black/10', {
                            'sm:h-24 sm:group-hover:h-31 transition-height lg:h-fit lg:group-hover:h-fit': (data.series?.folder_tags?.length ?? 0) > 0,
                        })
                    "
                    :class="[
                        'aspect-square max-h-16 w-full rounded-t-lg object-cover',
                        'sm:max-h-none sm:w-16 sm:rounded-t-none sm:rounded-l-lg sm:shadow-md',
                        'lg:hidden',
                        'content-auto sm:[contain-intrinsic-size:auto_96px]',
                        (data.series?.folder_tags?.length ?? 0) === 0 ? 'sm:aspect-2-3' : 'min-h-full',
                    ]"
                    loading="lazy"
                    v-if="!isDesktop"
                />

                <div class="flex w-full flex-1 flex-col sm:p-3 lg:contents lg:p-0">
                    <div
                        :class="[
                            'flex w-full flex-1 flex-col flex-wrap gap-4 p-3 text-left',
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
                            <h4 class="w-fit truncate text-nowrap sm:text-right">
                                {{ formatFileSize(data.total_size ?? 0) }}
                            </h4>
                        </section>
                    </div>
                    <div
                        v-if="data.series?.folder_tags?.length"
                        :class="[
                            'transition-height flex w-full flex-wrap gap-1 p-3 pt-0',
                            'sm:-ms-1 sm:max-h-0 sm:overflow-clip sm:p-0 sm:group-hover:max-h-7.5 sm:group-hover:pt-2 sm:group-hover:[overflow-clip-margin:4px]',
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
<style lang="css" scoped>
.transition-height {
    transition-property: height max-height overflow-clip-margin;
    transition-timing-function: var(--tw-ease, var(--default-transition-timing-function) /* cubic-bezier(0.4, 0, 0.2, 1) */);
    transition-duration: var(--tw-duration, 200ms);
    will-change: height max-height overflow-clip-margin;
}
</style>
