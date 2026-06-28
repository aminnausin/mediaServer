<script setup lang="ts" generic="T extends ImageType">
import type { ImageType, SeriesResource } from '@/contracts/media';
import type { ComputedRef } from 'vue';

import { computed, inject, ref } from 'vue';
import { ButtonText } from '@/components/cedar-ui/button';
import { toPlural } from '@/service/util.ts';
import { cn } from '@aminnausin/cedar-ui';

import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';
import ButtonBase from '@/components/cedar-ui/button/ButtonBase.vue';
import ImageCard from '@/components/cards/data/ImageCard.vue';
import FolderTab from '@/components/folders/FolderTab.vue';

const primaryIds = inject<ComputedRef<Record<T, number>>>('primaryImageIds');
const isAudio = inject<ComputedRef<boolean>>('isAudio');
const data = inject<ComputedRef<SeriesResource>>('series');

const activeFilters = computed<ImageType[]>(() => ['poster', 'banner', 'preview']);
const filteredImages = computed(() => (data?.value.images ?? []).filter((i) => i.type === filteredType.value && !i.replaced_at));
const replacedImages = computed(() => (data?.value.images ?? []).filter((i) => i.type === filteredType.value && i.replaced_at));

const filteredType = ref<ImageType>(activeFilters.value[0]);
const filteredPrimaryId = computed(() => primaryIds?.value?.[filteredType.value as T]);

const isShowingReplaced = ref(false);
</script>
<template>
    <FolderTab class="flex-1">
        <div class="bg-surface-3/50 dark:bg-surface-3 flex w-fit gap-0.5 rounded-lg p-0.5 text-xs">
            <ButtonBase
                v-for="filter in activeFilters"
                :key="filter"
                :class="
                    cn('h-7 rounded-md px-3 py-1 capitalize transition-colors', {
                        'bg-surface-1 dark:bg-surface-4 text-primary-active dark:text-primary-muted shadow-sm': filter === filteredType,
                        'text-foreground-2 hover:text-foreground-1 hover:bg-surface-1/50': filter !== filteredType,
                    })
                "
                @click="filteredType = filter"
            >
                {{ filter }}
            </ButtonBase>
        </div>

        <div class="xms:text-sm flex flex-1 flex-col gap-4 text-xs">
            <div
                v-if="filteredImages.length > 0"
                :class="['grid w-full grid-cols-1 gap-2', { 'md:grid-cols-3': filteredImages.length > 2, 'xms:grid-cols-2': filteredImages.length >= 2 }]"
            >
                <ImageCard v-for="image in filteredImages" :data="image" :key="image.id" :is-read-only="true" :is-primary="image.id == filteredPrimaryId" :is-folder="true" />
            </div>
            <div v-else class="text-foreground-1 my-auto flex w-full items-center justify-center gap-1 py-8 tracking-widest">
                <ProIconsPhotoOff class="size-6" />
                <span> No images yet </span>
            </div>

            <template v-if="replacedImages.length > 0">
                <div class="text-foreground-2 flex w-full items-center justify-between gap-2 text-xs">
                    <ButtonText
                        :variant="'ghost'"
                        type="button"
                        class="hover:text-foreground-0 duration-input h-fit gap-1 bg-transparent! p-0 uppercase transition-colors"
                        @click="isShowingReplaced = !isShowingReplaced"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" :class="'size-3 shrink-0'">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ isShowingReplaced ? 'Hide' : 'Show' }} deleted
                    </ButtonText>
                    <p>{{ replacedImages.length }} image{{ toPlural(replacedImages.length) }}</p>
                </div>

                <div :class="cn('xms:grid-cols-3 grid max-h-0 w-full gap-2 overflow-hidden', { 'max-h-none': isShowingReplaced })" v-if="isShowingReplaced">
                    <ImageCard v-for="image in replacedImages" :key="image.id" :data="image" :is-audio="isAudio" :is-read-only="true" :is-folder="true" />
                </div>
            </template>
        </div>
    </FolderTab>
</template>
