<script setup lang="ts">
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import ProiconsArrowClockwise from '~icons/proicons/arrow-rotate-clockwise';

defineProps<{
    isBusy?: boolean;
    isLocked?: boolean;
    copy: { heading?: string; description?: string; button?: string };
    badgeClass?: string;
    iconClass?: string;
    buttonIconClass?: string;
}>();
defineEmits<{ action: [] }>();
</script>

<template>
    <div :class="cn('text-foreground-0 flex h-full flex-col items-center justify-center gap-3 px-8 py-10 text-center', $attrs.class)" aria-live="polite">
        <div
            :class="
                cn(
                    'flex size-8 shrink-0 items-center justify-center rounded-full transition-colors sm:size-11',
                    'text-foreground-3 bg-foreground-0/8',
                    { 'text-foreground-1 bg-foreground-0/8': isBusy || isLocked },
                    badgeClass,
                )
            "
        >
            <ProiconsArrowClockwise v-if="isBusy" :class="cn('size-4 animate-spin sm:size-5', iconClass)" />
            <slot v-else name="icon"></slot>
        </div>

        <div class="max-w-64 space-y-1">
            <p class="text-xs font-medium sm:text-sm">{{ copy.heading }}</p>
            <p class="text-foreground-2 leading-5">{{ copy.description }}</p>
        </div>

        <ButtonBase
            v-if="copy.button"
            :disabled="isBusy || isLocked"
            :use-size="false"
            :aria-busy="isBusy"
            :title="copy.button"
            type="button"
            :class="
                cn(
                    'mt-1 flex items-center gap-1.5 rounded-md py-1.5 ps-2.5 pe-3 font-medium',
                    'dark:text-foreground-i bg-white transition hover:bg-white/90',
                    'ring-r-button hover:ring-primary-muted focus:ring-primary hocus:ring-2 ring-1 focus:outline-hidden',
                )
            "
            @click="$emit('action')"
        >
            <ProiconsArrowClockwise v-if="isBusy || isLocked" :class="cn('size-3.5 shrink-0 animate-spin', buttonIconClass)" />
            <slot name="buttonIcon" v-else> </slot>
            <span class="line-clamp-1 break-all">{{ copy.button }}</span>
        </ButtonBase>
    </div>
</template>
