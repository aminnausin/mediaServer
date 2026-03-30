<script setup lang="ts">
import type { SubtitleResource } from '@/types/resources';
import type { PopoverItem } from '@aminnausin/cedar-ui';
import type { Ref } from 'vue';

import { computed, inject, nextTick, ref, useTemplateRef } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';
import { round } from 'lodash-es';
import { cn } from '@aminnausin/cedar-ui';

import useOctopusRenderer from '@/components/video/subtitles/OctopusRenderer';
import VideoPopoverSlider from '@/components/video/VideoPopoverSlider.vue';
import VideoPopoverItem from '@/components/video/VideoPopoverItem.vue';
import VideoPopover from '@/components/video/VideoPopover.vue';

import ProiconsTextFontSize from '~icons/proicons/text-font-size';
import ProiconsCheckmark from '~icons/proicons/checkmark';
import LucideCaptionsOff from '~icons/lucide/captions-off';
import LucideCaptions from '~icons/lucide/captions';

interface PlayerSubtitlesProps {
    videoButtonOffset: number;
    usingPlayerModernUI?: boolean;
}

const { instantiateOctopus, clearOctopus, resizeOctopus } = useOctopusRenderer();

//#region Shared State
const { stateVideo } = storeToRefs(useContentStore());
const player = inject<Ref<HTMLVideoElement>>('player');
const props = defineProps<PlayerSubtitlesProps>();
//#endregion

//#region Local State
const currentSubtitleTrack = ref<SubtitleResource>();
const currentSubtitleTrackUrl = computed<string>(() => buildSubtitleUrl(currentSubtitleTrack.value));

const isShowingSubtitles = ref(false);
const subtitleSizeMultiplier = ref(1);
const subtitleSizeMin = 0.5;
const subtitleSizeMax = 2;
const subtitleSizeDelta = 0.1;

const subtitlesPopover = useTemplateRef('subtitles-popover');

const playerSubtitleItems = computed(() => {
    const items: PopoverItem[] = stateVideo.value.subtitles.map((track) => {
        const isCurrentTrack = isShowingSubtitles.value && currentSubtitleTrack.value?.track_id === track.track_id;

        const lang = track.language ?? 'und';
        const isDefault = track.is_default ? '[default]' : null;
        const isForced = track.is_forced ? '[forced]' : null;
        const isExternal = track.track_id === 0 ? '[external]' : '';
        const codec = track.codec ?? 'und';

        const capitalisedLang = lang.length > 1 ? lang[0].toUpperCase() + lang.slice(1) : lang;

        const text = [capitalisedLang, isDefault, isForced, isExternal].filter(Boolean).join(' ');
        return {
            icon: LucideCaptions,
            text,
            title: [text, `Title: ${track.title ?? 'und'}`, `Track: ${track.track_id}`, `Codec: ${codec}`].join('\n'),
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

const handleSizeChange = (_: Event, dir: number = 0) => {
    if (dir === 0) return;

    subtitleSizeMultiplier.value = round(Math.max(Math.min(Number.parseFloat(`${subtitleSizeMultiplier.value}`) + subtitleSizeDelta * dir, subtitleSizeMax), subtitleSizeMin), 2);
};

const handleSizeWheel = (event: WheelEvent) => {
    event.preventDefault();

    handleSizeChange(new Event('SizeChange'), event.deltaY < 0 ? 1 : -1);
};

//#endregion

//#region Functions

/**
 * Handle Subtitles Toggle
 * @param track -> Defaults to first available subtitle only if not currently showing anything
 */
const handleSubtitles = async (track?: SubtitleResource) => {
    const nextTrack = track ?? (isShowingSubtitles.value ? undefined : defaultSubtitleTrack.value);

    isShowingSubtitles.value = !!nextTrack;
    subtitlesPopover.value?.handleClose();

    if (currentSubtitleTrack.value?.track_id === nextTrack?.track_id && currentSubtitleTrack.value?.metadata_uuid === nextTrack?.metadata_uuid) return; // If no change, don't bother calculating anything

    currentSubtitleTrack.value = nextTrack;

    if (!player?.value) {
        clearOctopus();
        return;
    }

    currentSubtitleTrack.value = nextTrack;

    if (nextTrack?.codec === 'ass') {
        instantiateOctopus(nextTrack, stateVideo.value.metadata?.frame_rate);
        hideAllTracks();
        return;
    }
    clearOctopus();
    await nextTick();
    for (const textTrack of player.value.textTracks) {
        textTrack.mode = isShowingSubtitles.value ? 'showing' : 'hidden';
    }
};

/**
 * Set subtitles to blank.
 */
const clearSubtitles = () => {
    clearOctopus();
    currentSubtitleTrack.value = undefined;
    isShowingSubtitles.value = false;
    subtitlesPopover.value?.handleClose();
    hideAllTracks();
};

const hideAllTracks = () => {
    if (!player?.value) return;
    for (const textTrack of player.value.textTracks) {
        textTrack.mode = 'hidden';
    }
};

const buildSubtitleUrl = (subtitle?: SubtitleResource): string => {
    if (!subtitle) return '';

    const { metadata_uuid, track_id, language, codec } = subtitle;
    const languageSlug = track_id === 0 ? `.${language}` : '';
    const extension = codec === 'ass' ? '.ass' : '.vtt';
    return `/data/subtitles/${metadata_uuid}/${track_id}${languageSlug}${extension}`;
};

//#endregion
defineExpose({
    handleSubtitles,
    clearSubtitles,
    resizeOctopus,
    isShowingSubtitles,
    defaultSubtitleTrack,
    currentSubtitleTrack,
    currentSubtitleTrackUrl,
    subtitleSizeMultiplier,
    subtitlesPopover,
});
</script>
<template>
    <VideoPopover
        ref="subtitles-popover"
        :margin="80"
        :player="player"
        :popoverClass="cn('max-w-40! rounded-lg h-fit', { 'right-0!': usingPlayerModernUI })"
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
            <section :class="['scrollbar-minimal flex h-fit max-h-21 flex-col overflow-y-auto transition-transform md:max-h-35', { 'pe-0.5': playerSubtitleItems.length > 3 }]">
                <VideoPopoverSlider
                    v-if="playerSubtitleItems.length > 1 && currentSubtitleTrack && currentSubtitleTrack?.codec !== 'ass'"
                    v-model="subtitleSizeMultiplier"
                    :text="`Font Size`"
                    :shortcut="`${Math.round(subtitleSizeMultiplier * 100)}%`"
                    :icon="ProiconsTextFontSize"
                    :min="0.5"
                    :max="2"
                    :step="0.1"
                    :action="handleSizeChange"
                    :wheel-action="handleSizeWheel"
                    :title="'Change Subtitle Font Size'"
                />
                <VideoPopoverItem v-for="(item, index) in playerSubtitleItems" :key="index" v-bind="item" class="*:truncate" />
            </section>
        </template>
    </VideoPopover>
</template>
<style lang="css">
.libassjs-canvas-parent {
    z-index: 3;
    position: static !important;
}
</style>
