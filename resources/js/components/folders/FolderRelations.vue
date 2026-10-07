<script setup lang="ts">
import type { SeriesResource } from '@/contracts/media';
import type { ComputedRef } from 'vue';

import { computed, inject, ref } from 'vue';
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import RelatedFolderCard from '@/components/cards/data/relations/RelatedFolderCard.vue';
import FolderTab from '@/components/folders/FolderTab.vue';

import LucideWaypoints from '~icons/lucide/waypoints';

declare type RelationType = 'files' | 'folders';

defineProps<{ hideIfEmpty?: boolean; showTitle?: boolean }>();

const data = inject<ComputedRef<SeriesResource>>('series');

const activeFilters = computed<RelationType[]>(() => ['folders']);
const filteredRelations = computed(() => data?.value.related_series ?? []);

const filteredType = ref<RelationType>(activeFilters.value[0]);
</script>
<template>
    <FolderTab class="flex-1 gap-2" v-if="data.related_series.length > 0 || !hideIfEmpty">
        <div class="flex w-full flex-wrap justify-between gap-0.5" v-if="showTitle && data.related_series.length > 0">
            <span class="text-nowrap">Relations</span>
            <span class="text-foreground-1">
                {{ filteredRelations.length }}
            </span>
        </div>
        <div v-if="activeFilters.length > 1" class="bg-surface-3/50 dark:bg-surface-3 flex w-fit gap-0.5 rounded-lg p-0.5 text-xs">
            <ButtonBase
                v-for="filter in activeFilters"
                :key="filter"
                :class="
                    cn('h-7 rounded-md px-3 py-1 capitalize transition-colors', {
                        'bg-surface-1 dark:bg-surface-4 text-primary-active dark:text-primary-muted shadow-sm': filter === filteredType,
                        'text-foreground-2 hover:text-foreground-0 hover:bg-surface-1/50': filter !== filteredType,
                    })
                "
                @click="filteredType = filter"
            >
                Related {{ filter }}
            </ButtonBase>
        </div>

        <div class="xms:text-sm @container flex flex-1 flex-col gap-4 text-xs">
            <div v-if="filteredRelations.length > 0" :class="['grid w-full grid-cols-[repeat(auto-fill,minmax(clamp(6.5rem,8cqw,8rem),1fr))] gap-2']">
                <RelatedFolderCard v-for="relation in filteredRelations" :key="relation.folder.id" :folder="relation.folder" :relation="relation" class="w-full" />
            </div>
            <div v-else class="text-foreground-1 my-auto flex w-full items-center justify-center gap-1 py-8 tracking-widest">
                <LucideWaypoints class="size-6 *:stroke-[1.4]" />
                <span> No relations yet </span>
            </div>
        </div>
    </FolderTab>
</template>
