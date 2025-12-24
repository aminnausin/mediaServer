<script setup lang="ts">
import type { DrawerControllerProps } from '@aminnausin/cedar-ui';

import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';
import { useDrawer } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<DrawerControllerProps>(), {
    teleportTarget: 'body',
});

const drawerStore = useDrawer();
</script>
<template>
    <Teleport :to="teleportTarget">
        <div
            role="dialog"
            aria-modal="true"
            :aria-labelledby="drawerStore.props.value?.title ? 'drawerTitle' : undefined"
            :aria-describedby="drawerStore.props.value?.description ? 'drawerDescription' : undefined"
            class="text-foreground-0 fixed top-0 left-0 z-50 flex h-screen w-screen items-center justify-center bg-transparent"
            v-show="drawerStore.isOpen.value || drawerStore.isAnimating.value"
            v-cloak
        >
            <Transition
                enter-active-class="transition-opacity ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="drawerStore.isOpen.value" :class="['absolute inset-0 h-full w-full bg-black/50 backdrop-blur-xs']" @click="drawerStore.close('backdrop')"></div>
            </Transition>

            <Transition
                enter-active-class="transition-all ease-out duration-200"
                enter-from-class="opacity-0 translate-y-full"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-full"
            >
                <UseFocusTrap
                    v-if="drawerStore.isOpen.value && drawerStore.component.value"
                    class="scrollbar-hide fixed bottom-0 left-0 flex items-center"
                    :options="{ allowOutsideClick: true }"
                >
                    <component :is="drawerStore.component.value" v-bind="drawerStore.props.value" />
                </UseFocusTrap>
            </Transition>
        </div>
    </Teleport>
</template>
