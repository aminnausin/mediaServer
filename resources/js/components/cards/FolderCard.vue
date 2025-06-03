<script setup lang="ts">
import type { ContextMenuItem } from '@/types/types';
import type { FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
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

const { setContextMenu } = useAppStore();

const contextMenuItems = computed(() => {
    const items: ContextMenuItem[] = [
        {
            text: 'Edit',
            icon: CircumEdit,
            action: () => {
                if (!props.data?.id) return;
                emit('clickAction', props.data.id, 'edit');
            },
        },
    ];
    return items;
});
</script>

<template>
    <RelativeHoverCard class="w-full" positionClasses="!p-0 !border-none z-50 -top-5 lg:-left-24" iconHidden :hoverCardDelay="50" :hoverCardLeaveDelay="50">
        <template #content>
            <img
                :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                alt="Folder Thumbnail"
                class="hidden lg:block h-32 aspect-2/3 object-cover shadow-md"
            />
        </template>
        <template #trigger>
            <RouterLink
                :to="`/${data.path}`"
                class="text-left relative flex flex-col sm:flex-row flex-wrap rounded-lg sm:p-3 dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 hover:bg-gray-200 text-neutral-600 dark:text-neutral-400 shadow w-full group cursor-pointer divide-gray-300 dark:divide-gray-400"
                @contextmenu="
                    (e: any) => {
                        setContextMenu(e, { items: contextMenuItems });
                    }
                "
            >
                <img
                    :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                    alt="Folder Thumbnail"
                    class="hidden xs:block lg:hidden max-h-16 sm:w-12 aspect-square object-cover shadow-md rounded-t-lg sm:rounded-sm sm:me-4"
                />
                <span class="w-full flex-1 text-left relative flex flex-col gap-4 lg:gap-2 sm:flex-row flex-wrap p-3 sm:p-0">
                    <section class="flex justify-between gap-4 w-full items-center">
                        <h3 class="w-full truncate text-gray-900 dark:text-white" :title="props.data.series?.title ?? props.data.name">
                            {{ props.data.series?.title ?? props.data.name }}
                        </h3>
                        <div class="flex justify-end gap-1">
                            <ButtonCorner
                                :positionClasses="'w-7 h-7'"
                                :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                                :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                                :label="'Share Folder'"
                                @click.stop.prevent="emit('clickAction', props.data.id, 'share')"
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
                    <section class="flex flex-col sm:flex-row sm:justify-between w-full flex-wrap gap-2 text-sm">
                        <h4 class="w-full text-wrap truncate sm:text-nowrap flex-1" :title="`${props.data.file_count} Episode${props.data.file_count !== 1 ? 's' : ''}`">
                            {{ props.data.file_count }} Episode{{ props.data.file_count !== 1 ? 's' : '' }}
                        </h4>
                        <h4 class="truncate text-nowrap sm:text-right w-fit lg:hidden xl:block">
                            <!-- some other folder statistic or data like number of seasons or if its popular or something -->
                            {{ props.data.total_size ? formatFileSize(props.data.total_size) : '' }}
                        </h4>
                    </section>
                </span>
                <section
                    v-if="props.data.series?.folder_tags?.length"
                    class="flex gap-1 p-3 sm:p-0 pt-0 transition-all sm:max-h-[0px] md:group-hover:max-h-[26px] md:group-hover:pt-1 w-full overflow-clip flex-wrap group-hover:[overflow-clip-margin:4px]"
                    title="Tags"
                >
                    <ChipTag
                        v-for="(tag, index) in props.data.series.folder_tags"
                        :key="index"
                        :label="tag.name"
                        :colour="'bg-neutral-200 leading-none shadow dark:bg-neutral-900 hover:bg-violet-600 text-neutral-500 hover:text-neutral-50 hover:dark:bg-violet-600/90 !max-h-[22px]'"
                    />
                </section>
            </RouterLink>
        </template>
    </RelativeHoverCard>
</template>
