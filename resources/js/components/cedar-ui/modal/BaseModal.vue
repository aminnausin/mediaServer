<script setup lang="ts">
import { useModalStore } from '@/stores/ModalStore';
import { ButtonCorner } from '@/components/cedar-ui/button';

const modalStore = useModalStore();
</script>

<template>
    <section v-if="!modalStore.props?.hideHeader" class="flex flex-wrap items-center gap-2">
        <h3 ref="modalTitle" id="modalTitle" class="flex-1 scroll-mt-16 text-xl font-semibold sm:scroll-mt-12">
            <slot name="title">
                {{ modalStore.props?.title ?? 'Modal Title' }}
            </slot>
        </h3>
        <ButtonCorner @click="modalStore.close" class="static! m-0!" />
        <p class="text-foreground-2 w-full text-sm" v-if="$slots.description" id="modalDescription">
            <slot name="description"> </slot>
        </p>
    </section>
    <slot> </slot>
</template>
