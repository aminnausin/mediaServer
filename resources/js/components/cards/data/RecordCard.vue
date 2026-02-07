<script setup lang="ts">
import { type RecordResource } from '@/types/resources';

import { toFormattedDate, toTimeSpan } from '@/service/util';
import { computed, ref, watch } from 'vue';
import { ButtonCorner } from '@/components/cedar-ui/button';

import SidebarCard from '@/components/cards/sidebar/SidebarCard.vue';

import CircumShare1 from '~icons/circum/share-1';
import CircumPlay1 from '~icons/circum/play-1';

const props = defineProps<{
    record: RecordResource;
    index: number;
}>();

const rawDate = new Date((props.record.created_at ?? '').replace(' ', 'T'));
const timeSpan = ref(toTimeSpan(rawDate));
const videoLink = computed(() => {
    if (!(props.record.video_id ?? props.record.metadata?.video_id) || !props.record.category?.name || !props.record.folder_name) return undefined;
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
    <SidebarCard :to="videoLink" class="text-foreground-1 gap-4 lg:gap-2">
        <section class="flex w-full items-center justify-between gap-4">
            <h3 class="text-foreground-0 w-full truncate" :title="record.video_name ?? record.file_name">
                {{ record.video_name ?? `Deleted (${record.file_name})` }}
            </h3>
            <div class="flex justify-end gap-1" v-if="videoLink">
                <ButtonCorner
                    :class="'hover:text-primary dark:hover:text-primary-muted hover:dark:bg-surface-1 hover:bg-surface-6 size-7'"
                    :label="'Share Track/Video'"
                    :use-default-style="false"
                    @click.stop.prevent="$emit('clickAction', videoLink)"
                >
                    <template #icon>
                        <CircumShare1 width="20" height="20" stroke-width="1" stroke="currentColor" />
                    </template>
                </ButtonCorner>
                <ButtonCorner
                    :class="'hover:text-primary dark:hover:text-primary-muted hover:dark:bg-surface-1 hover:bg-surface-6 size-7'"
                    :to="videoLink"
                    :label="'Watch Track/Video'"
                    :use-default-style="false"
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
            <h4 class="hidden w-full shrink-0 truncate text-wrap sm:shrink sm:text-nowrap lg:block" :title="record.folder_name">
                {{ record.folder_name }}
            </h4>
            <h4 class="line-clamp-2 hidden w-full truncate text-right lg:block" :title="toFormattedDate(rawDate)">
                {{ timeSpan }}
            </h4>
            <h4 class="mr-auto truncate text-wrap sm:text-nowrap lg:hidden">{{ record.folder_name }} · {{ timeSpan }}</h4>
            <h4 class="line-clamp-2 truncate text-wrap sm:text-right sm:text-nowrap lg:hidden">
                {{
                    rawDate.toLocaleDateString('en-ca', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        hour12: false,
                        minute: '2-digit',
                    })
                }}
            </h4>
        </section>
    </SidebarCard>
</template>
