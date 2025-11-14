<script setup lang="ts">
import type { RecordResource } from '@/types/resources';
import { toTimeSpan } from '@/service/util';
import { computed } from 'vue';

import ButtonCorner from '../inputs/ButtonCorner.vue';
import CircumPlay1 from '~icons/circum/play-1';

const props = defineProps<{
    data: RecordResource;
}>();

const rawDate = new Date((props.data?.attributes.created_at ?? '').replace(' ', 'T'));
const timeSpan = toTimeSpan(rawDate);

const videoLink = computed(() => {
    if (!props.data.relationships.video_id || !props.data.relationships.category?.name || !props.data.relationships.folder?.name) return false;
    return `/${encodeURIComponent(props.data.relationships?.category?.name ?? '')}/${encodeURIComponent(props.data.relationships.folder?.name ?? '')}?video=${props.data.relationships.video_id}`;
});
</script>

<template>
    <section
        class="text-left relative flex flex-col gap-4 sm:flex-row flex-wrap rounded-xl dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5 dark:hover:bg-primary-dark-600 hover:bg-primary-800 dark:text-white shadow-sm p-3 w-full group cursor-pointer divide-gray-300 dark:divide-neutral-400"
    >
        <RouterLink v-if="videoLink" :to="videoLink" class="absolute w-full h-full top-0 left-0 rounded-xl" title="Watch Video" />

        <section class="flex justify-between gap-4 w-full">
            <h2 class="truncate z-10 flex items-center" :title="props.data.relationships.file_name">
                {{ props.data.relationships.video_name }}
            </h2>
            <div class="flex justify-end gap-1 cursor-auto z-10" @click.stop.prevent="">
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
        <section class="flex flex-col sm:flex-row sm:justify-between w-full text-neutral-600 dark:text-neutral-400 text-sm">
            <h3
                class="w-full text-wrap truncate sm:text-nowrap cursor-auto z-10"
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
                {{ props.data.relationships.folder?.name }} · {{ timeSpan }}
            </h3>
            <h3 class="truncate sm:text-right w-full line-clamp-2 cursor-auto z-10" @click.stop.prevent="">
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
