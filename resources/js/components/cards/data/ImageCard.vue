<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import type { ImageResource } from '@/contracts/media';

import { CedarDelete2 } from '@/components/cedar-ui/icons';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import VideoControlWrapper from '@/components/video/VideoControlWrapper.vue';

import LazyImage from '@/components/lazy/LazyImage.vue';
import MediaTag from '@/components/labels/MediaTag.vue';

import ProiconsDelete from '~icons/proicons/delete';
import PrimeSave from '~icons/prime/save';

const props = withDefaults(defineProps<{ data: ImageResource; isPrimary?: boolean; isActive?: boolean; isAudio?: boolean }>(), {
    isPrimary: false,
    isActive: false,
    isAudio: false,
});

const filename = computed(() => props.data.path.split('/').at(-1));
const tags = computed(() => [props.data.type, props.data.source]);

const generatePosterStyle = (url?: string): HTMLAttributes['style'] => {
    if (!url) return {};

    // I could use blur hash instead
    return {
        backgroundImage: `url("${url}")`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
    };
};

const emit = defineEmits({
    select: () => true,
    deselect: () => true,
    delete: () => true,
});
</script>
<template>
    <div
        :class="[
            'relative flex flex-wrap items-center justify-between',
            'w-full rounded-lg text-sm shadow-sm transition',
            'data-card',
            `ring-2 ${isPrimary ? 'ring-primary-active/60' : 'ring-transparent dark:ring-neutral-700/20'}`,
        ]"
    >
        <div :class="cn('relative flex max-h-48 w-full items-center overflow-clip rounded-t-lg text-xs select-none sm:max-h-80')">
            <div :class="cn({ 'aspect-[1.91/1]!': data.type === 'preview', 'aspect-square': isAudio, 'aspect-video': !isAudio }, 'size-full', $attrs.class)">
                <div class="absolute inset-0 scale-120 blur-sm" :style="generatePosterStyle(data.path)"></div>

                <LazyImage
                    :src="data.path"
                    alt="poster"
                    :animate="false"
                    loading="lazy"
                    :wrapper-class="cn('transition-opacity duration-input')"
                    :class="cn('absolute inset-0 size-full object-contain', { 'aspect-[1.91/1]': data.type === 'preview' })"
                />
            </div>

            <!-- Overlay -->
            <div :class="cn('duration-input pointer-events-none absolute inset-0 z-3 flex items-start justify-between gap-1 p-2 transition-[translate,margin]')">
                <div class="h-fit">
                    <VideoControlWrapper :class="cn('w-fit transition-opacity duration-150')" v-if="isPrimary">
                        <p :class="cn('font-figtree px-1 text-white tabular-nums text-shadow-lg')">Primary</p>
                    </VideoControlWrapper>
                </div>
                <div class="text-foreground-i dark:text-foreground-0 pointer-events-auto flex gap-1" v-if="data.type !== 'preview'">
                    <ButtonIcon
                        class="overlay-button hover:ring-1"
                        :type="'button'"
                        :variant="'ghost'"
                        :title="isPrimary ? 'Already Selected' : 'Select'"
                        :disabled="isPrimary"
                        v-if="!isPrimary"
                        @click="$emit('select')"
                    >
                        <template #icon>
                            <PrimeSave class="size-4" />
                        </template>
                    </ButtonIcon>
                    <ButtonIcon v-else class="overlay-button hover:ring-1" :type="'button'" title="Remove Selection" :variant="'ghost'" @click="$emit('deselect')">
                        <template #icon>
                            <CedarDelete2 class="size-4" />
                        </template>
                    </ButtonIcon>
                    <ButtonIcon
                        v-if="!data.blurHash || data.source !== 'generated'"
                        class="overlay-button hover:ring-1"
                        :type="'button'"
                        title="Delete"
                        :variant="'ghost'"
                        @click="$emit('delete')"
                    >
                        <template #icon>
                            <ProiconsDelete class="size-4" />
                        </template>
                    </ButtonIcon>
                </div>
            </div>
        </div>
        <div class="text-foreground-1 flex w-full flex-col items-start justify-between gap-x-4 gap-y-2 p-3 dark:text-inherit">
            <p class="w-full truncate" :title="filename">{{ filename }}</p>
            <span class="-ms-0.5 flex w-full flex-wrap gap-1 overflow-clip text-xs [overflow-clip-margin:4px]">
                <MediaTag
                    v-for="(tag, index) in tags"
                    :key="index"
                    :label="tag"
                    :class="
                        cn(
                            'text-xs',
                            'hover:bg-primary dark:hover:bg-primary/90 hover:text-foreground-i text-foreground-7 bg-neutral-200 leading-none shadow-sm dark:bg-neutral-900',
                        )
                    "
                />
            </span>
        </div>
    </div>
</template>
<style scoped lang="css">
.overlay-button {
    --tw-ring-color: var(--color-primary) /* var(--color-purple-600) */;

    width: calc(var(--spacing) * 6) /* 1.5rem = 24px */;
    height: calc(var(--spacing) * 6) /* 1.5rem = 24px */;
    padding: calc(var(--spacing) * 1) /* 0.25rem = 4px */;
    background-color: color-mix(in oklab, var(--color-neutral-900) /* oklch(20.5% 0 0) = #171717 */ 40%, transparent);

    transition-property: text-decoration-color, background-color, box-shadow;

    &:hover {
        @media (hover: hover) {
            background-color: color-mix(in oklab, var(--color-neutral-900) /* oklch(20.5% 0 0) = #171717 */ 60%, transparent);
        }
    }
}
</style>
