<script setup lang="ts">
import { ButtonCorner } from '@/components/cedar-ui/button';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import PlayerTranscript from '@/components/video/transcript/PlayerTranscript.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';

import ProiconsPanelRight from '~icons/proicons/panel-right';

const { selectedSideBar } = storeToRefs(useAppStore());
const { cycleSideBar } = useAppStore();
</script>

<template>
    <SidebarHeader :text="'Transcript'">
        <ButtonCorner
            @click="cycleSideBar('transcript', 'list-card')"
            title="Move to Player"
            colour-classes="hover:bg-transparent"
            text-classes="text-white/70 hover:text-white"
            position-classes="size-5"
        >
            <template #icon><ProiconsPanelRight class="scale-x-[-1]" /></template>
        </ButtonCorner>
    </SidebarHeader>

    <section id="list-content-transcript" class="full-height-sidebar flex flex-col flex-wrap gap-2">
        <PlayerTranscript :is-visible="selectedSideBar === 'transcript'"> </PlayerTranscript>
    </section>
</template>
