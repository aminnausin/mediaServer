<script setup lang="ts">
import { useModalCore, cn } from '@aminnausin/cedar-ui';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';

const modalStore = useModalCore();
</script>
<template>
    <Teleport to="body">
        <dialog
            v-show="modalStore.isOpen.value || modalStore.isAnimating.value"
            class="modal fixed top-0 left-0 z-300 flex h-screen w-screen items-center justify-center bg-transparent text-gray-900 dark:text-neutral-200"
            v-cloak
            aria-modal="true"
            aria-labelledby="modalTitle"
            aria-describedby="modalDescription"
        >
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="modalStore.isOpen.value" class="absolute inset-0 h-full w-full backdrop-blur-xs"></div>
            </Transition>
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 sm:scale-95"
                enter-to-class="opacity-100 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 sm:scale-100"
                leave-to-class="opacity-0 sm:scale-95"
            >
                <UseFocusTrap
                    v-if="modalStore.isOpen.value"
                    class="scrollbar-hide relative flex h-full max-h-screen w-full items-center overflow-y-scroll px-6 py-10 sm:py-6"
                >
                    <OnClickOutside
                        @trigger="modalStore.close"
                        @keydown.esc="modalStore.close"
                        :class="
                            cn(
                                '3xl:max-w-2xl m-auto flex w-full flex-col gap-4 rounded-md border border-neutral-200 bg-white p-6 shadow-lg drop-shadow-md backdrop-blur-lg sm:max-w-lg sm:rounded-lg xl:max-w-xl dark:border-neutral-700 dark:bg-neutral-800/90',
                                modalStore.props.rootClass,
                            )
                        "
                        tabindex="-1"
                    >
                        <component :is="modalStore.component.value" v-bind="modalStore.props.value" />
                    </OnClickOutside>
                </UseFocusTrap>
            </Transition>
        </dialog>
    </Teleport>
</template>
