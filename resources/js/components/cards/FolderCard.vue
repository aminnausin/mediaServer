<script setup lang="ts">
import type { ContextMenuItem } from '@/types/types';
import type { FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { RouterLink } from 'vue-router';
import { computed } from 'vue';

import RelativeHoverCard from '@/components/cards/RelativeHoverCard.vue';
import ButtonCorner from '@/components/inputs/ButtonCorner.vue';

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
    let items: ContextMenuItem[] = [
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
                class="text-left relative flex flex-col sm:gap-4 sm:flex-row flex-wrap rounded-lg sm:p-3 dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 hover:bg-gray-200 dark:text-white shadow w-full group cursor-pointer divide-gray-300 dark:divide-gray-400"
                @contextmenu="
                    (e: any) => {
                        setContextMenu(e, { items: contextMenuItems });
                    }
                "
            >
                <img
                    :src="handleStorageURL(data.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                    alt="Folder Thumbnail"
                    class="hidden xs:block lg:hidden max-h-16 sm:w-12 aspect-square object-cover shadow-md rounded-t-lg sm:rounded-sm"
                />
                <span class="w-full flex-1 text-left relative flex flex-col gap-4 lg:gap-2 sm:flex-row flex-wrap p-3 sm:p-0">
                    <section class="flex justify-between gap-4 w-full items-center">
                        <h2 class="w-full truncate" :title="props.data.series?.title ?? props.data.name">{{ props.data.series?.title ?? props.data.name }}</h2>
                        <div class="flex justify-end gap-1">
                            <ButtonCorner
                                :positionClasses="'w-7 h-7'"
                                :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                                :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                                :label="'Share Folder'"
                                @click.stop.prevent="emit('clickAction', props.data.id, 'share')"
                            >
                                <template #icon>
                                    <CircumShare1 width="20" height="20" />
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
                                    <CircumFolderOn width="20" height="20" />
                                </template>
                            </ButtonCorner>
                        </div>
                    </section>
                    <section class="flex flex-col sm:flex-row sm:justify-between w-full flex-wrap gap-2 text-sm">
                        <h3
                            class="text-neutral-500 w-full text-wrap truncate sm:text-nowrap flex-1"
                            :title="`${props.data.file_count} Episode${props.data.file_count !== 1 ? 's' : ''}`"
                        >
                            {{ props.data.file_count }} Episode{{ props.data.file_count !== 1 ? 's' : '' }}
                        </h3>
                        <h3 class="truncate text-nowrap sm:text-right text-neutral-500 w-fit lg:hidden xl:block">
                            <!-- some other folder statistic or data like number of seasons or if its popular or something -->
                            {{ props.data.total_size ? formatFileSize(props.data.total_size) : '' }}
                        </h3>
                    </section>
                </span>
            </RouterLink>
        </template>
    </RelativeHoverCard>
</template>
