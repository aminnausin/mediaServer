<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import ToggleBase from '@/components/inputs/ToggleBase.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';

const appStore = useAppStore();

const { pageTitle, ambientMode, playbackHeatmap, selectedSideBar } = storeToRefs(appStore);

onMounted(() => {
    pageTitle.value = 'Settings';
    selectedSideBar.value = '';
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-settings" class="flex flex-col gap-4 [&>*]:space-y-1">
                <span>
                    <FormInputLabel :field="{ name: 'ToggleAmbientMode', text: 'Ambient Mode' }" />
                    <div class="flex items-center gap-2">
                        <ToggleBase v-model="ambientMode" />
                        <p>{{ ambientMode ? 'On' : 'Off' }}</p>
                    </div>
                </span>
                <span>
                    <FormInputLabel :field="{ name: 'TogglePlaybackHeatmap', text: 'Playback Heatmap' }" />
                    <div class="flex items-center gap-2">
                        <ToggleBase v-model="playbackHeatmap" />
                        <p>{{ playbackHeatmap ? 'On' : 'Off' }}</p>
                    </div>
                </span>
            </section>
        </template>
        <template v-slot:sidebar>
            <!-- <Settings /> -->
        </template>
    </LayoutBase>
</template>
