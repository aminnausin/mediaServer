<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import type { ImageResource } from '@/contracts/media';

import { CedarDelete2 } from '@/components/cedar-ui/icons';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { toTimeSpan } from '@/service/util';
import { computed } from 'vue';
import { useAuth } from '@/composables/auth/useAuth';
import { cn } from '@aminnausin/cedar-ui';

import VideoControlWrapper from '@/components/video/VideoControlWrapper.vue';
import LazyImage from '@/components/lazy/LazyImage.vue';

import ProiconsDelete from '~icons/proicons/delete';
import ProiconsStar from '~icons/proicons/star';
import PrimeSave from '~icons/prime/save';

const props = withDefaults(defineProps<{ data: ImageResource; isPrimary?: boolean; isAudio?: boolean; isPendingDelete?: boolean }>(), {
    isPrimary: false,
    isAudio: false,
    isPendingDelete: false,
});

const { userData } = useAuth();

const deletionDate = computed(() => {
    if (!props.data.replaced_at) return null;
    const replacedAt = new Date(props.data.replaced_at);
    return new Date(replacedAt.getTime() + 30 * 24 * 60 * 60 * 1000);
});

const isDisabled = computed(() => !!deletionDate.value);

const filename = computed(() => props.data.path.split('/').at(-1));
const tags = computed(() => {
    if (deletionDate.value) return [`deleted ${toTimeSpan(deletionDate.value)}`];

    const imageTags = [`${props.data.source} ${toTimeSpan(new Date(props.data.created_at ?? ''))}`];

    return imageTags;
});

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
    restore: () => true,
});
</script>
<template>
    <div
        :class="
            cn('group relative flex w-full flex-col items-start rounded-lg text-xs shadow-sm transition', 'data-card ring-2 ring-transparent', {
                'pointer-events-none! opacity-50': isDisabled,
                'ring-primary-active/60': isPrimary,
            })
        "
    >
        <div :class="cn('relative flex max-h-48 w-full items-center overflow-clip rounded-t-lg select-none sm:max-h-80')">
            <div :class="cn({ 'aspect-40/21!': data.type === 'preview', 'aspect-square': isAudio, 'aspect-video': !isAudio }, 'size-full', $attrs.class)">
                <div class="absolute inset-0 scale-120 blur-sm" :style="generatePosterStyle(data.path)"></div>

                <LazyImage
                    :src="data.path"
                    alt="poster"
                    :animate="false"
                    loading="lazy"
                    :wrapper-class="cn('transition-opacity duration-input')"
                    :class="cn('absolute inset-0 size-full object-contain', { 'aspect-40/21': data.type === 'preview' })"
                />
            </div>

            <!-- Overlay -->
            <div :class="cn('duration-input pointer-events-none absolute inset-0 z-3 flex flex-wrap items-start justify-between gap-1 p-2 transition-[translate,margin]')">
                <VideoControlWrapper :class="cn('w-fit opacity-0 transition-opacity duration-100', { 'opacity-100': isPrimary, 'backdrop-blur-none': !isPrimary })">
                    <p :class="cn('pointer-events-auto px-1 text-white text-shadow-lg')">Primary</p>
                </VideoControlWrapper>
                <div class="text-foreground-i dark:text-foreground-0 pointer-events-auto ms-auto flex gap-1" v-if="data.type !== 'preview'">
                    <ButtonIcon v-if="isPendingDelete" class="overlay-button hover:ring-1" :type="'button'" title="Undo delete" :variant="'ghost'" @click="$emit('restore')">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="size-4">
                                <path d="M0 0h24v24H0z" fill="none" />
                                <path
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M9.25 12L5.957 8.707A1 1 0 0 1 5.664 8M9.25 4L5.957 7.293A1 1 0 0 0 5.664 8M7.25 19.5h6.25a5.75 5.75 0 0 0 0-11.5H5.664"
                                />
                            </svg>
                        </template>
                    </ButtonIcon>
                    <template v-else-if="!isDisabled">
                        <ButtonIcon
                            class="overlay-button hover:ring-1"
                            :type="'button'"
                            :variant="'ghost'"
                            :title="isPrimary ? 'Already Selected' : 'Select as primary'"
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
                            v-if="data.source !== 'generated' && (!data.blur_hash || (data.blur_hash && data.user_id))"
                            class="overlay-button hover:ring-1"
                            :type="'button'"
                            title="Delete image"
                            :variant="'ghost'"
                            @click="$emit('delete')"
                        >
                            <template #icon>
                                <ProiconsDelete class="size-4" />
                            </template>
                        </ButtonIcon>
                    </template>
                </div>
                <RouterLink :to="`/profile/${data.user.id}`" :target="'_blank'" class="pointer-events-auto mt-auto flex h-4 w-full items-center gap-1" v-if="data.user">
                    <LazyImage
                        :wrapper-class="'w-fit shrink-0 relative size-4 peer'"
                        class="aspect-square rounded-full object-cover"
                        :src="`https://ui-avatars.com/api/?name=${data.user?.name[0] ?? 'S'}&amp;color=7F9CF5&amp;background=random`"
                        alt="user"
                    />
                    <div
                        :class="
                            cn(
                                'pointer-events-auto flex h-4 items-center overflow-clip rounded-full bg-neutral-900/60 lowercase drop-shadow-md',
                                'max-w-0 origin-left transition-[max-width,padding] ease-out',
                                'peer-hover:max-w-32 peer-hover:px-1 peer-hover:ease-in',
                            )
                        "
                    >
                        <span class="w-full truncate">
                            {{ data.user?.name ?? 'system' }}
                        </span>
                    </div>
                </RouterLink>
            </div>
        </div>
        <div class="text-foreground-1 flex h-full w-full flex-1 flex-col items-start gap-x-4 gap-y-2 p-3 dark:text-inherit">
            <div class="flex w-full items-center gap-1">
                <p class="w-full truncate sm:text-sm" :title="filename">{{ filename }}</p>

                <ProiconsStar v-if="isPrimary" :class="cn('ml-auto size-4 opacity-0 transition-opacity duration-100', { 'opacity-100': isPrimary })" />
            </div>

            <div class="text-foreground-2 -mt-1 flex w-full flex-wrap items-center gap-1">
                <span v-for="tag in tags" :key="tag">{{ tag }}</span>
            </div>
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
