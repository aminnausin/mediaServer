<script setup>
import { storeToRefs } from 'pinia';
import { useAppStore } from '../../stores/AppStore';

const appStore = useAppStore();
const { lightMode } = storeToRefs(appStore);
</script>

<template>
    <section class="toggle-switch shrink-0 flex flex-col" title="Toggle Light Mode">
        <label class="switch-label" for="light-mode-toggle">
            <input
                type="checkbox"
                class="checkbox invisible text-white"
                id="light-mode-toggle"
                name="light-mode-toggle"
                v-model="lightMode"
                v-bind:checked="lightMode"
            />
            <span class="slider"></span>
        </label>
    </section>
</template>
