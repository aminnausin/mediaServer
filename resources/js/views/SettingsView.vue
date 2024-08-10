<script setup>
import LayoutBase from '../layouts/LayoutBase.vue';
import FormInputLabel from '../components/labels/FormInputLabel.vue'

import { storeToRefs } from 'pinia';
import { useAppStore } from '../stores/AppStore';
import { onMounted } from 'vue';
import ToggleBase from '../components/inputs/ToggleBase.vue';
// import { useToast } from '../composables/useToast';

// const toast = useToast();

const appStore = useAppStore();

const { pageTitle, ambientMode } = storeToRefs(appStore);

onMounted(() => {
    pageTitle.value = "Settings";
})
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-settings" class="space-y-2 cursor-pointer min-h-[80vh]">
                <FormInputLabel :field="{name: 'ToggleAmbientMode', text: 'Ambient Mode'}"/>
                <div class="flex items-center gap-2">
                    <ToggleBase v-model="ambientMode"/>
                    <p>{{ ambientMode ? 'On' : 'Off' }}</p>
                </div>
            </section>
        </template>
        <template v-slot:sidebar>
            <!-- <Settings /> -->
        </template>
    </LayoutBase>
</template>