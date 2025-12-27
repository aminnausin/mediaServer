<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { cn } from '@aminnausin/cedar-ui';
const props = defineProps<{ label?: string; colour?: string; URL: string | URL }>();
</script>

<template>
    <router-link
        :id="'btn-nav-' + props.label"
        :to="props.URL"
        :class="
            cn(
                `dark:bg-primary-dark-800 bg-white`,
                'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg shadow-lg hover:bg-neutral-100 dark:text-neutral-200 dark:hover:bg-violet-700',
                'transition-colors duration-300',
            )
        "
        :aria-label="props.label"
    >
        <slot name="icon"></slot>
    </router-link>
</template>
