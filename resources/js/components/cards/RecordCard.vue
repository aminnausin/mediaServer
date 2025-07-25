<script setup lang="ts">
import { type RecordResource } from '@/types/resources';

import { toFormattedDate, toTimeSpan } from '@/service/util';
import { computed, ref, watch } from 'vue';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';

import CircumShare1 from '~icons/circum/share-1';
import CircumPlay1 from '~icons/circum/play-1';

const props = defineProps<{
    record: RecordResource;
    index: number;
}>();

const rawDate = new Date((props.record.attributes.created_at ?? '').replace(' ', 'T'));
const timeSpan = ref(toTimeSpan(rawDate));
const videoLink = computed(() => {
    if (
        !(props.record.relationships.video_id ?? props.record.relationships.metadata?.video_id) ||
        !props.record.relationships.category?.name ||
        !props.record.relationships.folder?.name
    )
        return false;
    return `/${encodeURIComponent(props.record.relationships?.category?.name ?? '')}/${encodeURIComponent(props.record.relationships.folder?.name ?? '')}?video=${props.record.relationships.video_id ?? props.record.relationships.metadata?.video_id}`;
});

watch(
    () => props.index,
    () => {
        timeSpan.value = toTimeSpan(rawDate);
    },
);
</script>

<template>
    <RouterLink
        :to="videoLink ? videoLink : ''"
        class="text-left relative flex flex-col gap-4 lg:gap-2 sm:flex-row flex-wrap rounded-lg dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 hover:bg-gray-200 text-neutral-600 dark:text-neutral-400 shadow p-3 w-full group cursor-pointer divide-gray-300 dark:divide-gray-400"
    >
        <section class="flex justify-between gap-4 w-full items-center">
            <h3 class="w-full truncate text-gray-900 dark:text-white" :title="props.record.relationships.video_name">
                {{ props.record.relationships.video_name }}
            </h3>
            <div class="flex justify-end gap-1" v-if="videoLink">
                <ButtonCorner
                    :positionClasses="'w-7 h-7'"
                    :textClasses="'hover:text-gray-900 dark:hover:text-violet-400'"
                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                    :label="'Share Video'"
                    @click.stop.prevent="$emit('clickAction', videoLink)"
                >
                    <template #icon>
                        <CircumShare1 width="20" height="20" stroke-width="1" stroke="currentColor" />
                    </template>
                </ButtonCorner>
                <ButtonCorner
                    :positionClasses="'w-7 h-7'"
                    :textClasses="`hover:text-violet-600 dark:hover:text-violet-500`"
                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                    :to="videoLink"
                    :label="'Watch Video'"
                >
                    <template #icon>
                        <CircumPlay1 width="20" height="20" stroke-width="1" stroke="currentColor" />
                    </template>
                </ButtonCorner>
            </div>
            <div class="flex justify-end gap-1 w-full truncate" v-else>
                {{ 'Deleted' }}
            </div>
        </section>
        <section class="flex flex-wrap sm:flex-nowrap sm:justify-between w-full gap-x-2 text-sm">
            <h4 class="hidden lg:block w-full text-wrap truncate sm:text-nowrap shrink-0 sm:shrink" :title="props.record.relationships.folder?.name">
                {{ props.record.relationships.folder?.name }}
            </h4>
            <h4 class="hidden lg:block truncate text-right w-full line-clamp-2" :title="toFormattedDate(rawDate)">
                {{ timeSpan }}
            </h4>
            <h4 class="lg:hidden text-wrap truncate sm:text-nowrap mr-auto">{{ props.record.relationships.folder?.name }} · {{ timeSpan }}</h4>
            <h4 class="lg:hidden truncate sm:text-right line-clamp-2 text-wrap sm:text-nowrap">
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
            </h4>
        </section>
    </RouterLink>
</template>
