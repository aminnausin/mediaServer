<script setup lang="ts">
import { useTranscript } from '@/components/video/transcript/useTranscript';
import { ButtonCorner } from '@/components/cedar-ui/button';
import { useAppStore } from '@/stores/AppStore';

import PlayerTranscript from '@/components/video/transcript/PlayerTranscript.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';

import ProiconsPanelRight from '~icons/proicons/panel-right';
import ProiconsCancel from '~icons/proicons/cancel';

const { cycleSideBar } = useAppStore();
const { placement, isShowingTranscript } = useTranscript();
</script>

<template>
    <SidebarHeader :text="'Transcript'">
        <ButtonCorner
            @click="cycleSideBar('transcript', 'list-card')"
            title="Move to Player"
            colour-classes="hover:bg-transparent"
            text-classes="text-white/70 hover:text-white"
            position-classes="size-5 me-1.5"
        >
            <template #icon><ProiconsPanelRight class="scale-x-[-1]" /></template>
        </ButtonCorner>
        <ButtonCorner
            @click="isShowingTranscript = false"
            title="Close Transcript"
            colour-classes="hover:bg-transparent"
            text-classes="text-white/70 hover:text-white"
            position-classes="size-5"
        >
            <template #icon><ProiconsCancel class="scale-x-[-1]" /></template>
        </ButtonCorner>
    </SidebarHeader>

    <section id="list-content-transcript" class="full-height-sidebar flex flex-col flex-wrap gap-2">
        <PlayerTranscript :is-visible="placement === 'sidebar'" />
    </section>
</template>
