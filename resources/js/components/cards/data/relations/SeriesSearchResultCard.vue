<script setup lang="ts">
import type { SeriesResource } from '@/contracts/media';

import { MdiLightPlus } from '@/components/cedar-ui/icons';
import { ButtonBase } from '@/components/cedar-ui/button';

import BlurhashImage from '@/components/lazy/BlurhashImage.vue';

defineProps<{ series: SeriesResource; posterUrl: string }>();
</script>

<template>
    <ButtonBase
        :use-size="false"
        type="button"
        class="group data-card library-card bg-surface-2 dark:bg-surface-1/50 flex-row items-stretch gap-0 rounded-sm text-left shadow-none"
    >
        <BlurhashImage
            alt="Poster"
            loading="lazy"
            :src="posterUrl"
            :blurhash="series.poster_image?.blur_hash"
            :animate="false"
            wrapper-class="relative aspect-2/3 w-11 shrink-0 overflow-hidden bg-black/10 rounded-l-sm"
            class="size-full object-cover"
        />

        <div class="hover:bg-surface-3/70 flex min-w-0 flex-1 items-center justify-between gap-3 rounded-r-sm p-2 transition-colors">
            <div class="flex min-w-0 flex-1 flex-col justify-center gap-1">
                <p class="truncate">{{ series.title }}</p>
                <p class="text-foreground-2 truncate text-xs">
                    {{ series.library_name }}
                </p>
            </div>

            <MdiLightPlus class="text-foreground-2 size-4 shrink-0 transition-transform *:stroke-2 group-hover:scale-125" />
        </div>
    </ButtonBase>
</template>
