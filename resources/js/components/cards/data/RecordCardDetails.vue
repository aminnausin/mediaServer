<script setup lang="ts">
import type { RecordResource } from '@/types/resources';

import { ButtonCorner } from '@/components/cedar-ui/button';
import { toTimeSpan } from '@/service/util';
import { computed } from 'vue';

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
    <section :class="['data-card', 'group relative flex w-full cursor-pointer flex-col flex-wrap gap-4 rounded-xl p-3 text-left shadow-sm ring-1 ring-gray-900/5 sm:flex-row']">
        <RouterLink v-if="videoLink" :to="videoLink" class="absolute top-0 left-0 h-full w-full rounded-xl" title="Watch Video" />

        <header class="flex w-full justify-between gap-4">
            <h2 class="z-10 flex items-center truncate" :title="data.file_name">
                {{ data.video_name ?? `[Deleted] ${data.file_name}` }}
            </h2>
            <div class="z-10 flex cursor-auto justify-end gap-1" @click.stop.prevent="">
                <ButtonCorner
                    v-if="videoLink"
                    :useDefaultStyle="false"
                    :to="videoLink"
                    :label="'Watch Video'"
                    class="hover:text-primary dark:hover:text-primary-muted hover:dark:bg-surface-1 hover:bg-surface-6 size-7 transition-none"
                >
                    <template #icon>
                        <CircumPlay1 width="20" height="20" />
                    </template>
                </ButtonCorner>
                <ButtonCorner
                    @click.stop.prevent="$emit('clickAction')"
                    :useDefaultStyle="false"
                    :label="'Delete'"
                    class="text-danger-3/80 hover:text-danger-2 hover:dark:bg-surface-1 hover:bg-surface-6 size-7 transition-none *:size-5"
                />
            </div>
        </header>
        <div class="text-foreground-1 flex w-full flex-col text-sm sm:flex-row sm:justify-between">
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
        </div>
    </section>
</template>
