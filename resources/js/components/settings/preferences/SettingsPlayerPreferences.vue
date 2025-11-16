<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/SettingsCard.vue';
import InputLabel from '@/components/labels/InputLabel.vue';
import ToggleBase from '@/components/inputs/ToggleBase.vue';

const { ambientMode, playbackHeatmap } = storeToRefs(useAppStore());
</script>

<template>
    <SettingsCard>
        <template #content>
            <SettingsHeader>
                <h3 class="text-base font-medium">Player Settings</h3>
                <p class="text-neutral-600 dark:text-neutral-400">Also available directly in the player</p>
            </SettingsHeader>
            <div class="flex w-full flex-col gap-4 sm:max-w-xs">
                <span class="flex items-center justify-between">
                    <InputLabel name="settings-player-ambient" text="Ambient Mode" class="line-clamp-1" />
                    <div class="flex items-center gap-2">
                        <ToggleBase v-model="ambientMode" :name="'settings-player-ambient'" class="rounded-full dark:border-neutral-700/70 dark:has-checked:border-neutral-700" />
                        <p class="w-8 text-end font-medium uppercase">{{ ambientMode ? 'On' : 'Off' }}</p>
                    </div>
                </span>
                <span class="flex items-center justify-between">
                    <InputLabel name="settings-player-heatmap" text="Playback Heatmap" class="line-clamp-1" />
                    <div class="flex items-center gap-2">
                        <ToggleBase
                            v-model="playbackHeatmap"
                            :name="'settings-player-heatmap'"
                            class="rounded-full dark:border-neutral-700/70 dark:has-checked:border-neutral-700"
                        />
                        <p class="w-8 text-end font-medium uppercase">{{ playbackHeatmap ? 'On' : 'Off' }}</p>
                    </div>
                </span>
            </div>
        </template>
    </SettingsCard>
</template>
