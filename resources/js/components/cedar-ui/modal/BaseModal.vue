<script setup lang="ts">
import type { useModalCore } from './useModalCore';

import { ButtonCorner } from '../button';

defineProps<{
    modal: ReturnType<typeof useModalCore>;
}>();
</script>

<template>
    <section v-if="!modal.props?.hideHeader" class="flex flex-wrap items-center gap-2">
        <h3 ref="modalTitle" id="modalTitle" class="flex-1 scroll-mt-16 text-xl font-semibold sm:scroll-mt-12">
            <slot name="title">
                {{ modal.props?.title ?? 'Modal Title' }}
            </slot>
        </h3>
        <ButtonCorner @click="modal.close" class="static! m-0!" />
        <p class="w-full text-sm text-foreground-2" v-if="$slots.description" id="modalDescription">
            <slot name="description"> </slot>
        </p>
    </section>
    <slot> </slot>
</template>
