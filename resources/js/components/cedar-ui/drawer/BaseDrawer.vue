<script setup lang="ts">
import type { DrawerProps, SwipeDirection } from '@aminnausin/cedar-ui';

import { cn, useSwipeHandler, SWIPE_THRESHOLD, VELOCITY_THRESHOLD, useDrawer } from '@aminnausin/cedar-ui';
import { ButtonText } from '../button';
import { RootDrawer } from '.';
import { ref } from 'vue';

import DrawerHandle from './DrawerHandle.vue';

const drawer = useDrawer();

const props = withDefaults(defineProps<DrawerProps>(), { direction: 'bottom' });
const swipeDirections = ref<SwipeDirection[]>([props.direction]);

const closeDrawer = () => {
    drawer.close();
    emit('close');
};

const { offset, isSwiping, onPointerDown, onPointerMove, onPointerUp, onPointerCancel, isTapGesture } = useSwipeHandler({
    directions: swipeDirections,
    swipeThreshold: { px: SWIPE_THRESHOLD },
    velocityThreshold: VELOCITY_THRESHOLD,
    onSwipeOut: closeDrawer,
});

const emit = defineEmits<(e: 'close') => void>();
</script>
<template>
    <RootDrawer
        @keydown.esc="closeDrawer"
        @pointerdown="onPointerDown"
        @pointermove="onPointerMove"
        @pointerup="onPointerUp"
        @dragend="onPointerUp"
        @pointercancel="onPointerCancel"
        :class="cn(drawer.props.value?.rootClass, 'drawer transition-all duration-300 ease-out')"
        :data-isSwiping="isSwiping"
        :style="{
            '--offset-y': `${Math.max(-10, offset.y)}px`,
        }"
        id="drawer"
    >
        <div class="flex flex-col items-center justify-center gap-3 p-4 md:p-3">
            <div class="group flex w-full cursor-pointer pb-1 sm:p-0">
                <slot name="handle">
                    <DrawerHandle
                        :aria-expanded="drawer.isOpen.value"
                        @keydown.enter.prevent="drawer.close"
                        @keydown.space.prevent="drawer.close"
                        @pointerdown="onPointerDown"
                        @pointermove="onPointerMove"
                        @dragend="onPointerUp"
                        @pointercancel="onPointerCancel"
                        @pointerup="
                            () => {
                                onPointerUp();
                                if (isTapGesture()) closeDrawer();
                            }
                        "
                    />
                </slot>
            </div>
            <div class="flex w-full flex-col gap-3" v-if="$slots.header || drawer.props.value.title || drawer.props.value.description">
                <slot name="header">
                    <div class="flex w-full flex-col gap-1.5" v-if="drawer.props.value.title || drawer.props.value.description">
                        <h2 id="drawerTitle" class="text-foreground flex-1 text-xl font-semibold">
                            <slot name="title">
                                {{ drawer.props.value.title ?? title ?? 'Title' }}
                            </slot>
                        </h2>
                        <p class="text-foreground-1 w-full text-sm" v-if="$slots.description || drawer.props.value.description || description" id="drawerDescription">
                            <slot name="description">{{ drawer.props.value.description ?? description ?? 'Description' }}</slot>
                        </p>
                    </div>
                </slot>
            </div>
            <div v-if="$slots.default" class="scrollbar-hide flex max-h-[60vh] w-full flex-col gap-3 overflow-y-scroll">
                <slot> </slot>
            </div>
            <div v-if="$slots.footer || !$slots.default" class="flex w-full flex-col gap-3">
                <slot name="footer">
                    <ButtonText :class="'h-8 w-full text-sm capitalize ring-1'" :variant="'default'" @click="closeDrawer"> Close Drawer </ButtonText>
                </slot>
            </div>
        </div>
    </RootDrawer>
</template>
<style lang="css" scoped>
.drawer {
    transform: translateY(var(--offset-y, 0px)) translateX(var(--offset-x, 0px));
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
