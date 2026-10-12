<script setup lang="ts">
import type { FolderResource, SeriesResource } from '@/contracts/media';
import type { ComputedRef } from 'vue';

import { handleEditRelations } from '@/service/folder/folderActions';

import { computed, inject, ref } from 'vue';
import { MdiLightPlus } from '@/components/cedar-ui/icons';
import { ButtonBase } from '@/components/cedar-ui/button';
import { useRouter } from 'vue-router';
import { toPlural } from '@/service/util';
import { useAuth } from '@/composables/auth/useAuth';
import { cn } from '@aminnausin/cedar-ui';

import RelatedFolderCard from '@/components/cards/data/relations/RelatedFolderCard.vue';
import FolderTab from '@/components/folders/FolderTab.vue';

import LucideWaypoints from '~icons/lucide/waypoints';

declare type RelationType = 'files' | 'folders';

defineProps<{ hideIfEmpty?: boolean; showTitle?: boolean }>();

const { isAdmin } = useAuth();

const router = useRouter();

const folder = inject<ComputedRef<FolderResource>>('data');
const data = inject<ComputedRef<SeriesResource>>('series');

const activeFilters = computed<RelationType[]>(() => ['folders']);
const filteredRelations = computed(() => data?.value.related_series ?? []);
const filteredType = ref<RelationType>(activeFilters.value[0]);
</script>
<template>
    <FolderTab :class="['gap-2', { 'flex-1': filteredRelations.length === 0 }]" v-if="data && (filteredRelations.length > 0 || !hideIfEmpty)">
        <div
            class="flex w-full flex-wrap justify-between gap-0.5"
            v-if="showTitle && filteredRelations.length > 0"
            :title="`${filteredRelations.length} Relation${toPlural(filteredRelations.length)}`"
        >
            Relations
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
                <RelatedFolderCard
                    v-for="relation in filteredRelations"
                    :key="relation.folder.id"
                    :folder="relation.folder"
                    :relation="relation"
                    class="w-full"
                    :to="`/${relation.folder.category_id}/${relation.folder.id}/details/${router.currentRoute.value.params['tab']}`"
                />
            </div>

            <div v-else :class="cn('my-auto flex w-full flex-col items-center justify-center gap-3 px-8 py-8 text-center', $attrs.class)" aria-live="polite">
                <div class="text-foreground-1 flex w-full items-center justify-center gap-2 tracking-widest">
                    <LucideWaypoints class="size-6 *:stroke-[1.4]" />
                    <span> No relations yet </span>
                </div>
                <ButtonBase
                    v-if="isAdmin"
                    type="button"
                    class="mt-1 flex items-center gap-1.5 rounded-full bg-white p-1.5 font-medium text-neutral-900 transition hover:bg-white/90 disabled:cursor-not-allowed disabled:opacity-60"
                    :use-size="false"
                    @click="handleEditRelations(folder)"
                >
                    <MdiLightPlus :class="cn('size-3.5')" />
                </ButtonBase>
            </div>
        </div>
    </FolderTab>
</template>
