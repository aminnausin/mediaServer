<script setup lang="ts">
import { OnClickOutside } from '@vueuse/components';
import { useModalStore } from '@/stores/ModalStore';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<{ teleportDisabled?: boolean }>(), { teleportDisabled: false });

const modalStore = useModalStore();
</script>
<template>
    <Teleport :to="modalStore.props.teleportTarget ?? 'body'" :disabled="teleportDisabled">
        <div
            v-show="modalStore.isOpen || modalStore.isAnimating"
            class="modal fixed top-0 left-0 z-300 flex h-screen w-screen items-center justify-center bg-transparent"
            v-cloak
            aria-modal="true"
            aria-labelledby="modalTitle"
            aria-describedby="modalDescription"
        >
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-(--duration-input)"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="modalStore.isOpen" class="absolute inset-0 h-full w-full backdrop-blur-xs"></div>
            </Transition>
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 sm:scale-95"
                enter-to-class="opacity-100 sm:scale-100"
                leave-active-class="ease-in duration-(--duration-input)"
                leave-from-class="opacity-100 sm:scale-100"
                leave-to-class="opacity-0 sm:scale-95"
            >
                <UseFocusTrap
                    v-if="modalStore.isOpen"
                    class="scrollbar-hide pointer-events-auto relative flex h-full max-h-screen w-full items-center overflow-y-scroll px-4 py-10 sm:py-6"
                >
                    <OnClickOutside
                        @trigger="modalStore.close"
                        @keydown.esc="modalStore.close"
                        :class="
                            cn(
                                '3xl:max-w-xl bg-overlay-2-t border-overlay-border m-auto flex w-full flex-col gap-4 rounded-xl border p-6 shadow-lg drop-shadow-md backdrop-blur-lg sm:max-w-lg xl:max-w-xl',
                                modalStore.props.rootClass,
                            )
                        "
                        tabindex="-1"
                    >
                        <component :is="modalStore.component" v-bind="modalStore.props.value" />
                    </OnClickOutside>
                </UseFocusTrap>
            </Transition>
        </div>
    </Teleport>
</template>
