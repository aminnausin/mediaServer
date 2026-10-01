<script setup lang="ts">
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import ProiconsArrowClockwise from '~icons/proicons/arrow-rotate-clockwise';

defineProps<{ isBusy?: boolean; copy: { heading?: string; description?: string; button?: string }; badgeClass?: string; iconClass?: string; buttonIconClass?: string }>();
defineEmits<{ action: [] }>();
</script>

<template>
    <div :class="cn('flex h-full flex-col items-center justify-center gap-3 px-8 py-10 text-center', $attrs.class)" role="status" aria-live="polite">
        <div :class="cn('flex size-8 shrink-0 items-center justify-center rounded-full transition-colors sm:size-11', { 'bg-white/8 text-white/70': isBusy }, badgeClass)">
            <ProiconsArrowClockwise v-if="isBusy" :class="cn('size-4 animate-spin sm:size-5', iconClass)" />
            <slot v-else name="icon"></slot>
        </div>

        <div class="max-w-64 space-y-1">
            <p class="text-xs font-medium text-white/90 sm:text-sm">{{ copy.heading }}</p>
            <p class="leading-5 text-white/40">{{ copy.description }}</p>
        </div>

        <ButtonBase
            v-if="copy.button"
            :disabled="isBusy"
            :use-size="false"
            :aria-busy="isBusy"
            type="button"
            class="mt-1 flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 font-medium text-neutral-900 transition hover:bg-white/90 disabled:cursor-not-allowed disabled:opacity-60"
            @click="$emit('action')"
        >
            <ProiconsArrowClockwise v-if="isBusy" :class="cn('size-3.5 animate-spin', buttonIconClass)" />
            <slot name="buttonIcon" v-else> </slot>
            <span>{{ copy.button }}</span>
        </ButtonBase>
    </div>
</template>
