<script setup lang="ts">
import type { DrawerProps, SwipeDirection } from '@aminnausin/cedar-ui';

import { cn, useSwipeHandler, SWIPE_THRESHOLD, VELOCITY_THRESHOLD, useDrawer } from '@aminnausin/cedar-ui';
import { ButtonText } from '../button';
import { RootDrawer } from '.';
import { ref } from 'vue';

import DrawerHandle from './DrawerHandle.vue';

const drawer = useDrawer();

const props = withDefaults(defineProps<DrawerProps>(), { direction: 'bottom', showHeader: true, showFooter: true });
const swipeDirections = ref<SwipeDirection[]>([props.direction]);

const { offset, isSwiping, onPointerDown, onPointerMove, onPointerUp, onPointerCancel, isTapGesture } = useSwipeHandler({
    directions: swipeDirections,
    swipeThreshold: { px: SWIPE_THRESHOLD },
    velocityThreshold: VELOCITY_THRESHOLD,
    onSwipeOut: () => drawer.close('swipe'),
});

function handleOnPointerUp(e: PointerEvent) {
    if (onPointerUp()) return;
    if (isTapGesture() && isDrawerCloseTarget(e)) drawer.close('swipe');
}

function isDrawerCloseTarget(e: PointerEvent): boolean {
    const target = e.target as HTMLElement | null;
    return !!target?.closest('[data-drawer-close]');
}
</script>
<template>
    <RootDrawer
        @keydown.esc="drawer.close('escape')"
        @pointerdown="onPointerDown"
        @pointermove="onPointerMove"
        @pointerup="handleOnPointerUp"
        @pointercancel="onPointerCancel"
        @dragend="onPointerUp"
        :class="cn(rootClass, 'drawer transition-all duration-300 ease-out')"
        :data-isSwiping="isSwiping"
        :style="{
            '--offset-y': `${Math.max(-20, offset.y)}px`,
        }"
        id="drawer"
    >
        <div class="xms:px-4 flex flex-col items-center justify-center gap-3 p-3 md:px-6">
            <slot name="handle">
                <DrawerHandle
                    data-drawer-close
                    :aria-expanded="drawer.isOpen.value"
                    @keydown.enter.prevent="drawer.close('escape')"
                    @keydown.space.prevent="drawer.close('escape')"
                />
            </slot>
            <div class="flex w-full flex-col gap-1.5" v-if="props.showHeader">
                <slot name="header">
                    <h2 id="drawerTitle" class="text-foreground flex-1 text-xl font-semibold">
                        <slot name="title">
                            {{ title ?? 'Title' }}
                        </slot>
                    </h2>
                    <p class="text-foreground-1 w-full text-sm" v-if="$slots.description || description" id="drawerDescription">
                        <slot name="description">{{ description ?? 'Description' }}</slot>
                    </p>
                </slot>
            </div>
            <div v-if="$slots.default" class="scrollbar-hide flex max-h-[60vh] w-full flex-col gap-3 overflow-y-scroll">
                <slot> </slot>
            </div>
            <div v-if="props.showFooter" class="flex w-full flex-col gap-3">
                <slot name="footer">
                    <ButtonText :class="'h-8 w-full text-sm capitalize ring-1'" :variant="'default'" @click="drawer.close('programmatic')"> Close Drawer </ButtonText>
                </slot>
            </div>
        </div>
    </RootDrawer>
</template>
<style lang="css" scoped>
.drawer {
    transform: translate3d(var(--offset-x, 0px), var(--offset-y, 0px), 0px);
    touch-action: none;
    will-change: transform;
}

@media (prefers-reduced-motion: reduce) {
    .drawer {
        transition: none !important;
    }
}

[data-isSwiping='true'] {
    transition-property: none !important;
}
</style>
