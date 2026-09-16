<script setup lang="ts">
import type { ImageType, VideoResource } from '@/contracts/media';

import { computed } from 'vue';

import ImageCard from '@/components/cards/data/ImageCard.vue';

import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';

const props = defineProps<{ data: VideoResource }>();

const primaryImageIds = computed<Record<ImageType, number | undefined>>(() => ({ poster: props.data.metadata?.poster_image?.id }) as Record<ImageType, number | undefined>);

const imageGroups = computed(() =>
    [
        {
            label: 'Poster',
            images: props.data.metadata?.images?.filter((img) => img.type === 'poster') ?? [],
        },
        {
            label: 'Preview',
            images: props.data.metadata?.images?.filter((img) => img.type === 'preview') ?? [],
        },
    ].filter((g) => g.images.length),
);
</script>

<template>
    <div class="space-y-6">
        <div v-for="imageGroup in imageGroups" class="space-y-2" :key="imageGroup.label">
            <p>{{ imageGroup.label }}s</p>
            <div class="xms:text-sm @container flex flex-1 flex-col gap-4 text-xs">
                <div
                    v-if="imageGroup.images.length > 0"
                    :class="[
                        'grid w-full grid-cols-1 gap-2',
                        {
                            '@4xl:grid-cols-4': imageGroup.images.length >= 4,
                            '@[42rem]:grid-cols-3': imageGroup.images.length > 2,
                            '@sm:grid-cols-2': imageGroup.images.length >= 2,
                        },
                    ]"
                >
                    <ImageCard
                        v-for="image in imageGroup.images"
                        :data="image"
                        :key="image.id"
                        :is-read-only="true"
                        :is-primary="image.id == primaryImageIds[image.type]"
                        :is-folder="false"
                    />
                </div>
                <div v-else class="text-foreground-1 my-auto flex w-full items-center justify-center gap-1 py-8 tracking-widest">
                    <ProIconsPhotoOff class="size-6" />
                    <span> No images yet </span>
                </div>
            </div>
        </div>
    </div>
</template>
