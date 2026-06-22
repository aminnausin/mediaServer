<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { FLAGS } from '@/config/featureFlags';

import SettingsToggleRow from '@/components/settings/SettingsToggleRow.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';

const { ambientMode, playbackHeatmap, usingPlayerModernUI, lightMode, showAutoSubtitles, showSeekButtons } = storeToRefs(useAppStore());
</script>

<template>
    <SettingsCard>
        <template #content>
            <SettingsHeader>
                <h3 class="text-base font-medium">Player Settings</h3>
                <p class="text-foreground-1">Also available directly in the player</p>
            </SettingsHeader>
            <div class="flex w-full flex-col gap-4 sm:max-w-xs">
                <SettingsToggleRow
                    id="settings-player-ambient"
                    label="Ambient Mode"
                    :description="lightMode ? 'Only active in dark mode.' : ''"
                    v-model="ambientMode"
                    :disabled="lightMode"
                />
                <SettingsToggleRow id="settings-player-heatmap" label="Playback Heatmap" v-model="playbackHeatmap" />
                <SettingsToggleRow v-if="!FLAGS.FORCE_MODERN_PLAYER_UI" id="settings-player-modern-ui" label="Modern UI" v-model="usingPlayerModernUI" />
                <SettingsToggleRow id="settings-player-auto-subtitles" label="Auto Subtitles" v-model="showAutoSubtitles" />
                <SettingsToggleRow id="settings-player-seek-buttons" label="Seek Buttons" v-model="showSeekButtons" />
            </div>
        </template>
    </SettingsCard>
</template>
