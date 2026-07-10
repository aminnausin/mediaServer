<script setup lang="ts">
import ProiconsClosedCaptions from '~icons/proicons/closed-captions';
withDefaults(defineProps<{ variant?: 'Proicons' | 'Lucide' | 'Tabler' }>(), { variant: 'Lucide' });
</script>
<template>
    <svg v-if="variant === 'Lucide'" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
        <path d="M0 0h24v24H0z" fill="none" />
        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
            <rect width="18" height="14" x="3" y="5" rx="3" ry="3" />
            <path d="M7 15h4m4 0h2M7 11h2m4 0h4" />
        </g>
    </svg>
    <ProiconsClosedCaptions v-else-if="variant === 'Proicons'" />
    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <!-- Tabler -->
        <path
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M18 5a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V8a3 3 0 0 1 3-3zM7 15h5m3 0h2m0-3h-3m-3 0h-1"
        />
    </svg>
</template>
