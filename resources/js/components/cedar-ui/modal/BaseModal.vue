<script setup lang="ts">
import { RelativeHoverCard } from '@/components/cedar-ui/hover-card';
import { useModalStore } from '@/stores/ModalStore';
import { ButtonCorner } from '@/components/cedar-ui/button';

const modalStore = useModalStore();
</script>

<template>
    <section v-if="!modalStore.props?.hideHeader" class="flex flex-wrap items-center gap-2">
        <RelativeHoverCard class="flex-1 scroll-mt-16 sm:scroll-mt-12" :content="modalStore.props.titleTooltip">
            <template #trigger>
                <h3 ref="modalTitle" id="modalTitle" class="text-xl font-semibold">
                    <slot name="title">
                        {{ modalStore.props?.title ?? 'Modal Title' }}
                    </slot>
                </h3>
            </template>
        </RelativeHoverCard>
        <ButtonCorner @click="modalStore.close" class="static! m-0!" />
        <p class="text-foreground-2 w-full text-sm" v-if="$slots.description" id="modalDescription">
            <slot name="description"> </slot>
        </p>
    </section>
    <slot> </slot>
</template>
