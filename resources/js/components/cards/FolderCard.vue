<script setup lang="ts">
import type { ContextMenuItem } from '@/types/types';
import type { FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL } from '@/service/util';
import { computed, ref } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { RouterLink } from 'vue-router';

import RelativeHoverCard from '@/components/cards/RelativeHoverCard.vue';
import ButtonCorner from '@/components/inputs/ButtonCorner.vue';

import CircumFolderOn from '~icons/circum/folder-on';
import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';

const emit = defineEmits(['clickAction', 'otherAction']);
const props = defineProps<{
    folder: FolderResource;
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
                if (!props.folder?.id) return;
                emit('otherAction', props.folder.id);
            },
        },
    ];
    return items;
});

const folderLink = ref(`/${props.categoryName}/${props.folder.name}`);
</script>

<template>
    <RelativeHoverCard class="w-full" positionClasses="!p-0 !border-none z-50 -top-5 lg:-left-24" iconHidden :hoverCardDelay="50" :hoverCardLeaveDelay="50">
        <template #content>
            <img
                :src="
                    handleStorageURL(folder.series?.thumbnail_url) ??
                    'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'
                "
                alt="Folder Thumbnail"
                class="hidden lg:block h-32 aspect-2/3 object-cover shadow-md"
            />
        </template>
        <template #trigger>
            <RouterLink
                :to="folderLink"
                class="text-left relative flex flex-col sm:gap-4 sm:flex-row flex-wrap rounded-lg sm:p-3 dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 hover:bg-gray-200 dark:text-white shadow w-full group cursor-pointer divide-gray-300 dark:divide-gray-400"
                @contextmenu="
                    (e: any) => {
                        setContextMenu(e, { items: contextMenuItems });
                    }
                "
            >
                <img
                    :src="
                        handleStorageURL(folder.series?.thumbnail_url) ??
                        'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'
                    "
                    alt="Folder Thumbnail"
                    class="hidden xs:block lg:hidden max-h-16 sm:w-12 aspect-square object-cover shadow-md rounded-t-lg sm:rounded-sm"
                />
                <span class="w-full flex-1 text-left relative flex flex-col gap-4 lg:gap-2 sm:flex-row flex-wrap p-3 sm:p-0">
                    <section class="flex justify-between gap-4 w-full items-center">
                        <h2 class="w-full truncate" :title="props.folder.series?.title ?? props.folder.name">{{ props.folder.series?.title ?? props.folder.name }}</h2>
                        <div class="flex justify-end gap-1">
                            <ButtonCorner
                                :positionClasses="'w-7 h-7'"
                                :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                                :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                                :label="'Share Folder'"
                                @click.stop.prevent="$emit('clickAction', folderLink)"
                            >
                                <template #icon>
                                    <CircumShare1 width="20" height="20" />
                                </template>
                            </ButtonCorner>
                            <ButtonCorner
                                :positionClasses="'w-7 h-7'"
                                :textClasses="`${props.folder.name === props.stateFolderName ? 'text-violet-600' : 'hover:text-violet-600'} dark:hover:text-violet-500`"
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
                            :title="`${props.folder.file_count} Episode${props.folder.file_count !== 1 ? 's' : ''}`"
                        >
                            {{ props.folder.file_count }} Episode{{ props.folder.file_count !== 1 ? 's' : '' }}
                        </h3>
                        <h3 class="truncate text-nowrap sm:text-right text-neutral-500 w-fit lg:hidden xl:block">
                            <!-- some other folder statistic or data like number of seasons or if its popular or something -->
                            {{ props.folder.total_size ? formatFileSize(props.folder.total_size) : '' }}
                        </h3>
                    </section>
                </span>
            </RouterLink>
        </template>
    </RelativeHoverCard>
</template>
