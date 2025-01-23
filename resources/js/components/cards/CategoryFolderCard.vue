<script setup lang="ts">
import { type FolderResource } from '@/types/resources';

import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { useTemplateRef } from 'vue';

import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import Popover from '@/components/pinesUI/Popover.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import ProiconsDelete from '~icons/proicons/delete';
import ProiconsLock from '~icons/proicons/lock';
import CircumShare1 from '~icons/circum/share-1';
import CircumEdit from '~icons/circum/edit';

const props = defineProps<{ data?: FolderResource }>();
const popover = useTemplateRef('popover');
</script>

<template>
    <div class="flex flex-col rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white dark:hover:bg-primary-dark-600 hover:bg-primary-800 ring-1 ring-gray-900/5 w-full group">
        <RouterLink :to="`/${data?.path}`" class="w-full h-40">
            <img
                class="w-full h-full object-cover rounded-t-md shadow-sm mb-auto ring-1 ring-gray-900/5"
                :src="
                    handleStorageURL(data?.series?.thumbnail_url) ??
                    'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'
                "
                alt="Folder Cover Art"
            />
        </RouterLink>
        <section class="flex flex-1 h-full flex-col p-3 gap-2">
            <div class="flex items-start justify-between gap-1 flex-wrap xs:flex-nowrap">
                <h3 class="capitalize group-hover:text-purple-600">
                    {{ data?.series?.title ?? data?.name }}
                </h3>
                <span class="flex gap-2 [&>*]:h-6 text-sm">
                    <ButtonIcon :title="'Open Folder In New Tab'" :to="`/${data?.path}`">
                        <template #icon><CircumShare1 class="h-4 w-4" /></template>
                    </ButtonIcon>
                    <Popover popoverClass="!max-w-56 rounded-lg" :buttonClass="'!p-1 ml-auto'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="h-4 w-4" />
                        </template>
                        <template #content>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <h4 class="font-medium leading-none">Manage Folder</h4>
                                    <p class="text-sm text-muted-foreground">Set Folder Properties.</p>
                                </div>

                                <div class="space-y-2 [&>*]:w-full">
                                    <ButtonText
                                        class="h-8 dark:!bg-neutral-950 disabled:opacity-60"
                                        :title="'Edit Folder'"
                                        @click.stop.prevent="
                                            () => {
                                                popover?.handleClose();
                                                $emit('clickAction', data?.id);
                                            }
                                        "
                                    >
                                        <template #text> Edit Folder</template>
                                        <template #icon> <CircumEdit class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText class="h-8 dark:!bg-neutral-950 disabled:opacity-60" :title="'Manage Metadata Settings'" disabled>
                                        <template #text> Manage Metadata </template>
                                        <template #icon> <ProiconsLock class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText class="h-8 text-rose-600 dark:!bg-rose-700 disabled:opacity-60" :title="'Remove From Server'" disabled>
                                        <template #text> Remove Folder </template>
                                        <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                                    </ButtonText>
                                </div>
                            </div>
                        </template>
                    </Popover>
                </span>
            </div>
            <span class="w-full text-sm text-neutral-500 dark:text-neutral-400 flex flex-col gap-1 h-full mt-auto" v-if="data">
                <span class="flex items-start justify-between flex-wrap mt-auto">
                    <p class="">Videos: {{ data?.file_count ?? '?' }}</p>
                </span>

                <span class="flex items-center justify-between gap-x-2 flex-wrap">
                    <p class="" :title="`Date Added ${data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A'}`">
                        {{ data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A' }}
                    </p>
                    <p class="" :title="`Total Size ${formatFileSize(data.total_size)}`">
                        {{ formatFileSize(data.total_size) }}
                    </p>
                </span>
            </span>
        </section>
    </div>
</template>

<style lang="css" scoped>
img {
    image-rendering: auto;
    image-rendering: crisp-edges;
    image-rendering: pixelated;

    /* Safari seems to support, but seems deprecated and does the same thing as the others. */
    image-rendering: -webkit-optimize-contrast;
}
</style>
