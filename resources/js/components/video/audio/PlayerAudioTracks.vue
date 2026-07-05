<script setup lang="ts">
import type { Ref } from 'vue';

import { computed, inject, onMounted, ref, useTemplateRef } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';
import { cn } from '@aminnausin/cedar-ui';

import VideoPopoverItem from '@/components/video/popover/VideoPopoverItem.vue';
import VideoPopover from '@/components/video/popover/VideoPopover.vue';

import ProiconsCheckmark from '~icons/proicons/checkmark';
import LucideLanguages from '~icons/lucide/languages';

defineProps<{
    videoButtonOffset: number;
    usingPlayerModernUI?: boolean;
}>();

const { isStateVideoAudio: isAudio } = storeToRefs(useContentStore());

const audioTracksPopover = useTemplateRef('audio-tracks-popover');

const player = inject<Ref<HTMLVideoElement>>('player');

const preferredLanguage = ref<string>();
const activeTracks = ref<AudioTrack[]>([]);
const activeTrackId = ref<string>();

const playerAudioTracks = computed(() =>
    activeTracks.value.map((track) => {
        const lang = track.language || 'und';
        const text = [lang, track.id].filter(Boolean).join(' ');

        return {
            icon: LucideLanguages,
            text,
            title: [text, `Title: ${track.label || 'und'}`, `Track: ${track.id}`].join('\n'),
            selected: track.id === activeTrackId.value,
            selectedIcon: ProiconsCheckmark,
            selectedIconStyle: 'text-primary',
            action: () => handleTrack(track),
        };
    }),
);

const applyPreferredTrack = () => {
    if (!activeTracks.value) return;

    const match = preferredLanguage.value ? activeTracks.value.find((track) => track.language === preferredLanguage.value) : undefined;
    const target = match ?? activeTracks.value.find((track) => track.enabled) ?? activeTracks.value[0];

    handleTrack(target, !!match);
};

const handleTrack = (track: AudioTrack, setLanguage: boolean = true) => {
    if (!activeTracks.value.length) return;

    for (const tr of activeTracks.value) tr.enabled = tr.id === track.id;
    activeTrackId.value = track.id;

    if (setLanguage) preferredLanguage.value = track.language || preferredLanguage.value;
    if (audioTracksPopover.value?.popoverOpen) audioTracksPopover.value?.handleClose();
};

const onTracksLoaded = () => {
    activeTracks.value = Array.from(player?.value?.audioTracks ?? []);
    applyPreferredTrack();
};

const attachTrackListEvents = () => {
    if (!player?.value?.audioTracks) return;

    player.value.audioTracks.onaddtrack = onTracksLoaded;
};

onMounted(() => {
    attachTrackListEvents();
});

defineExpose({ audioTracksPopover });
</script>
<template>
    <VideoPopover
        v-if="!isAudio && playerAudioTracks.length"
        ref="audio-tracks-popover"
        :margin="80"
        :player="player"
        :popoverClass="cn('max-w-42! rounded-lg h-fit', { 'right-0!': usingPlayerModernUI })"
        :button-attributes="{
            'target-element': player,
            'use-tooltip': !audioTracksPopover?.popoverOpen,
            offset: videoButtonOffset,
        }"
        title="Audio Tracks"
    >
        <template #buttonIcon>
            <LucideLanguages class="size-3" />
        </template>
        <template #content>
            <section :class="['scrollbar-minimal flex h-fit max-h-21 flex-col overflow-y-auto transition-transform md:max-h-35', { 'pe-0.5': playerAudioTracks.length > 3 }]">
                <VideoPopoverItem v-for="(item, index) in playerAudioTracks" :key="index" v-bind="item" class="capitalize *:truncate" />
            </section>
        </template>
    </VideoPopover>
</template>
