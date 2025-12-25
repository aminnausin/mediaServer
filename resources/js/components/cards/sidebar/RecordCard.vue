<script setup lang="ts">
import { type RecordResource } from '@/types/resources';

import { toFormattedDate, toTimeSpan } from '@/service/util';
import { computed, ref, watch } from 'vue';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';

import CircumShare1 from '~icons/circum/share-1';
import CircumPlay1 from '~icons/circum/play-1';
import SidebarCard from '@/components/cards/sidebar/SidebarCard.vue';

const props = defineProps<{
    record: RecordResource;
    index: number;
}>();

const rawDate = new Date((props.record.created_at ?? '').replace(' ', 'T'));
const timeSpan = ref(toTimeSpan(rawDate));
const videoLink = computed(() => {
    if (!(props.record.video_id ?? props.record.metadata?.video_id) || !props.record.category?.name || !props.record.folder_name) return false;
    return `/${encodeURIComponent(props.record?.category?.name ?? '')}/${encodeURIComponent(props.record.folder_name ?? '')}?video=${props.record.video_id ?? props.record.metadata?.video_id}`;
});

watch(
    () => props.index,
    () => {
        timeSpan.value = toTimeSpan(rawDate);
    },
);
</script>

<template>
    <SidebarCard
        :to="videoLink ? videoLink : ''"
        class="dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 group relative flex w-full cursor-pointer flex-col flex-wrap gap-4 divide-gray-300 rounded-lg p-3 text-left text-neutral-600 shadow-sm hover:bg-gray-200 sm:flex-row lg:gap-2 dark:divide-gray-400 dark:text-neutral-400"
    >
        <section class="flex w-full items-center justify-between gap-4">
            <h3 class="w-full truncate text-gray-900 dark:text-white" :title="props.record.video_name">
                {{ props.record.video_name }}
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
            <div class="flex w-full justify-end gap-1 truncate" v-else>
                {{ 'Deleted' }}
            </div>
        </section>
        <section class="flex w-full flex-wrap gap-x-2 text-sm sm:flex-nowrap sm:justify-between">
            <h4 class="hidden w-full shrink-0 truncate text-wrap sm:shrink sm:text-nowrap lg:block" :title="props.record.folder_name">
                {{ props.record.folder_name }}
            </h4>
            <h4 class="line-clamp-2 hidden w-full truncate text-right lg:block" :title="toFormattedDate(rawDate)">
                {{ timeSpan }}
            </h4>
            <h4 class="mr-auto truncate text-wrap sm:text-nowrap lg:hidden">{{ props.record.folder_name }} · {{ timeSpan }}</h4>
            <h4 class="line-clamp-2 truncate text-wrap sm:text-right sm:text-nowrap lg:hidden">
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
    </SidebarCard>
</template>
