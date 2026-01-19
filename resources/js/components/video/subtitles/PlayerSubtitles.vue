<script setup lang="ts">
import type { SubtitleResource } from '@/types/resources';
import { cn, type PopoverItem } from '@aminnausin/cedar-ui';
import type { Ref } from 'vue';

import { computed, inject, ref, useTemplateRef } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';

import VideoPopoverItem from '@/components/video/VideoPopoverItem.vue';
import VideoPopover from '@/components/video/VideoPopover.vue';

import ProiconsCheckmark from '~icons/proicons/checkmark';
import LucideCaptionsOff from '~icons/lucide/captions-off';
import LucideCaptions from '~icons/lucide/captions';

interface PlayerSubtitlesProps {
    videoButtonOffset: number;
    usingPlayerModernUI?: boolean;
}

const props = defineProps<PlayerSubtitlesProps>();

//#region Shared State
const { stateVideo } = storeToRefs(useContentStore());
const player = inject<Ref<HTMLVideoElement>>('player');

//#endregion

//#region Local State
const currentSubtitleTrack = ref<SubtitleResource>();
const isShowingSubtitles = ref(false);

const subtitlesPopover = useTemplateRef('subtitles-popover');
const playerSubtitleItems = computed(() => {
    const items: PopoverItem[] = stateVideo.value.subtitles.map((track) => {
        const isCurrentTrack = isShowingSubtitles.value && currentSubtitleTrack.value?.track_id === track.track_id;

        const lang = track.language ?? 'Und';
        const isDefault = track.is_default ? '(default)' : '';

        return {
            icon: LucideCaptions,
            text: [lang, isDefault].join(' '),
            title: `Track ${track.track_id}`,
            selected: isCurrentTrack,
            selectedIcon: ProiconsCheckmark,
            selectedIconStyle: 'text-primary',
            action: () => {
                handleSubtitles(track);
            },
        };
    });

    const subtitlesOff: PopoverItem = {
        icon: LucideCaptionsOff,
        text: 'Off',
        selected: !isShowingSubtitles.value,
        selectedIcon: ProiconsCheckmark,
        selectedIconStyle: 'text-primary',
        action: clearSubtitles,
    };

    return [subtitlesOff, ...items];
});

const defaultSubtitleTrack = computed<SubtitleResource | undefined>(() => {
    const subtitles = stateVideo.value.subtitles;
    if (!subtitles.length) return undefined;

    return subtitles.find((s) => s.is_default) ?? subtitles[0];
});

//#region Functions
/**
 * Handle Subtitles Toggle
 * @param track -> Defaults to first available subtitle only if not currently showing anything
 */
const handleSubtitles = (track?: SubtitleResource) => {
    const nextTrack = track ?? (!isShowingSubtitles.value ? defaultSubtitleTrack.value : undefined);

    isShowingSubtitles.value = !!nextTrack;
    subtitlesPopover.value?.handleClose();

    if (currentSubtitleTrack.value?.track_id === nextTrack?.track_id) return;

    currentSubtitleTrack.value = nextTrack;

    if (!player?.value) return;

    for (const textTrack of player.value.textTracks) {
        textTrack.mode = isShowingSubtitles.value && textTrack.language === currentSubtitleTrack.value?.language ? 'showing' : 'hidden';
    }
};

/**
 * Set subtitles to blank.
 */
const clearSubtitles = () => {
    currentSubtitleTrack.value = undefined;
    isShowingSubtitles.value = false;
    subtitlesPopover.value?.handleClose();
};
//#endregion

defineExpose({
    handleSubtitles,
    clearSubtitles,
    isShowingSubtitles,
    currentSubtitleTrack,
    defaultSubtitleTrack,
});
</script>
<template>
    <VideoPopover
        ref="subtitles-popover"
        :margin="80"
        :player="player"
        :popoverClass="cn('max-w-40! rounded-lg h-18 md:h-fit', { 'right-0!': usingPlayerModernUI })"
        :button-attributes="{
            'target-element': player,
            'use-tooltip': true,
            offset: videoButtonOffset,
        }"
        title="Subtitles"
    >
        <template #buttonIcon>
            <LucideCaptions v-if="isShowingSubtitles" class="size-4" />
            <LucideCaptionsOff v-else class="size-4" />
        </template>
        <template #content>
            <section class="scrollbar-minimal flex h-14 max-h-28 flex-col overflow-y-auto transition-transform md:h-fit">
                <VideoPopoverItem v-for="(item, index) in playerSubtitleItems" :key="index" v-bind="item" class="capitalize" />
            </section>
        </template>
    </VideoPopover>
</template>
