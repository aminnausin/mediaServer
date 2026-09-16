<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { cn } from '@aminnausin/cedar-ui';

import IconShare from '@/components/icons/IconShare.vue';

defineProps<{ title: string; value?: any; to?: string; valueClass?: string; iconClass?: string; tooltip?: string }>();
</script>
<template>
    <div class="space-y-0.5" :title="tooltip">
        <div class="text-nowrap">{{ title }}</div>
        <slot>
            <component
                :class="cn('text-foreground-1 flex items-center gap-1 text-xs text-nowrap', { 'hover:text-primary dark:hover:text-primary-muted': to }, valueClass)"
                :is="to ? RouterLink : 'div'"
                :to="to"
            >
                {{ value ?? '—' }}
                <IconShare v-if="to" :class="cn('size-4', iconClass)" />
            </component>
        </slot>
    </div>
</template>
