<script setup lang="ts">
import type { Component } from 'vue';

import ProiconsFullScreenMaximize from '~icons/proicons/full-screen-maximize';

const props = withDefaults(defineProps<{ icon?: Component; title?: string }>(), { icon: ProiconsFullScreenMaximize });
</script>
<template>
    <button class="transition-opacity ease-in opacity-80 hover:opacity-100" :title="title ?? 'Video Button'">
        <slot name="icon">
            <icon class="w-4 h-4" />
        </slot>
    </button>
</template>
