<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import ToggleBase from './ToggleBase.vue';

const { lightMode } = storeToRefs(useAppStore());
</script>

<template>
    <ToggleBase knob-style="shadow-[inset_7px_-2px_0_0_#fff]" title="Toggle Light Mode" name="light-mode-toggle" v-model="lightMode" />
</template>
