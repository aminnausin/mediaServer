<script setup lang="ts">
import { OnClickOutside } from '@vueuse/components';
import { useModalStore } from '@/stores/ModalStore';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';

const modalStore = useModalStore();
</script>
<template>
    <Teleport to="body">
        <dialog
            v-show="modalStore.isOpen || modalStore.isAnimating"
            class="modal fixed top-0 left-0 z-[300] flex items-center justify-center w-screen h-screen text-gray-900 dark:text-neutral-200 bg-transparent"
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
                <div v-if="modalStore.isOpen" class="absolute inset-0 w-full h-full backdrop-blur-sm bg-opacity-70"></div>
            </Transition>
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 sm:scale-95"
                enter-to-class="opacity-100 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 sm:scale-100"
                leave-to-class="opacity-0 sm:scale-95"
            >
                <UseFocusTrap v-if="modalStore.isOpen" class="relative w-full px-6 py-10 sm:py-6 max-h-screen h-full overflow-y-scroll scrollbar-hide flex items-center">
                    <OnClickOutside
                        @trigger="modalStore.close"
                        @keydown.esc="modalStore.close"
                        class="gap-4 flex flex-col drop-shadow-md m-auto w-full p-6 bg-white dark:bg-neutral-800/90 backdrop-blur-lg border shadow-lg border-neutral-200 dark:border-neutral-700 sm:max-w-lg xl:max-w-xl 3xl:max-w-2xl rounded-md sm:rounded-lg"
                        tabindex="-1"
                    >
                        <component :is="modalStore.component" v-bind="modalStore.props" />
                    </OnClickOutside>
                </UseFocusTrap>
            </Transition>
        </dialog>
    </Teleport>
</template>
