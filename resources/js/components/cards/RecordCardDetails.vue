<script setup lang="ts">
import type { RecordResource } from '@/types/resources';
import { toTimeSpan } from '@/service/util';
import { computed } from 'vue';

import ButtonCorner from '../inputs/ButtonCorner.vue';
import CircumPlay1 from '~icons/circum/play-1';

const props = defineProps<{
    data: RecordResource;
}>();

const rawDate = new Date((props.data?.created_at ?? '').replace(' ', 'T'));
const timeSpan = toTimeSpan(rawDate);

const videoLink = computed(() => {
    if (!props.data.video_id || !props.data.category?.name || !props.data.folder_name) return false;
    return `/${encodeURIComponent(props.data?.category?.name ?? '')}/${encodeURIComponent(props.data.folder_name ?? '')}?video=${props.data.video_id}`;
});
</script>

<template>
    <section
        class="dark:bg-primary-dark-800/70 dark:hover:bg-primary-dark-600 hover:bg-primary-800 group relative flex w-full cursor-pointer flex-col flex-wrap gap-4 divide-gray-300 rounded-xl bg-white p-3 text-left shadow-sm ring-1 ring-gray-900/5 sm:flex-row dark:divide-neutral-400 dark:text-white"
    >
        <RouterLink v-if="videoLink" :to="videoLink" class="absolute top-0 left-0 h-full w-full rounded-xl" title="Watch Video" />

        <section class="flex w-full justify-between gap-4">
            <h2 class="z-10 flex items-center truncate" :title="props.data.file_name">
                {{ props.data.video_name }}
            </h2>
            <div class="z-10 flex cursor-auto justify-end gap-1" @click.stop.prevent="">
                <ButtonCorner
                    v-if="videoLink"
                    :positionClasses="'w-7 h-7'"
                    :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                    :to="videoLink"
                    :label="'Watch Video'"
                >
                    <template #icon>
                        <CircumPlay1 width="20" height="20" />
                    </template>
                </ButtonCorner>
                <ButtonCorner
                    :positionClasses="'w-7 h-7'"
                    :textClasses="'text-rose-700'"
                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                    :label="'Delete'"
                    @click.stop.prevent="$emit('clickAction')"
                />
            </div>
        </section>
        <section class="flex w-full flex-col text-sm text-neutral-600 sm:flex-row sm:justify-between dark:text-neutral-400">
            <h3
                class="z-10 w-full cursor-auto truncate text-wrap sm:text-nowrap"
                @click.stop.prevent=""
                :title="`Watched on ${rawDate.toLocaleDateString('en-ca', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    hour12: false,
                    minute: '2-digit',
                })}`"
            >
                {{ props.data.folder_name }} · {{ timeSpan }}
            </h3>
            <h3 class="z-10 line-clamp-2 w-full cursor-auto truncate sm:text-right" @click.stop.prevent="">
                {{
                    `${rawDate.toLocaleDateString('en-ca', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        hour12: false,
                        minute: '2-digit',
                    })}`
                }}
            </h3>
        </section>
    </section>
</template>
