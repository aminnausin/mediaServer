<script setup lang="ts">
import type { CategoryResource } from '@/types/resources';

import CircumEdit from '~icons/circum/edit';
import { toFormattedDate } from '@/service/util';
import ButtonCorner from '../inputs/ButtonCorner.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';

const props = defineProps<{ data?: CategoryResource }>();
</script>

<template>
    <div class="flex gap-2 p-4 overflow-clip rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5 w-full">
        <div class="h-28 object-cover rounded-md shadow-md aspect-2/3 mb-auto relative group">
            <img
                id="folder-thumbnail"
                class="h-28 object-cover rounded-md aspect-2/3 ring-1 ring-gray-900/5"
                :src="
                    data?.folders[0]?.series?.thumbnail_url ??
                    'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'
                "
                alt="Folder Cover Art"
            />
        </div>
        <section class="flex flex-1 h-full flex-col">
            <h3 class="capitalize text-lg flex items-start justify-between">
                {{ data?.name }}
                <ButtonIcon class="!w-6 !h-6 !p-1 !static">
                    <template #icon>
                        <CircumEdit class="w-full h-full" />
                    </template>
                </ButtonIcon>
            </h3>
            <span class="flex items-center justify-between gap-2 text-sm mt-auto" v-if="data?.folders[0]">
                <p class="text-slate-400">First Folder: {{ data.folders[0].name }}</p>
                <p class="text-slate-400">Videos: {{ data.folders[0].file_count }}</p>
            </span>
            <span class="flex items-center justify-between gap-2 text-sm">
                <p class="text-slate-400">Folders: {{ data?.folders_count }}</p>
                <p class="text-slate-400">Added: {{ data?.created_at ? toFormattedDate(new Date(data?.created_at + ' EST')) : 'N/A' }}</p>
            </span>
        </section>
    </div>
</template>
