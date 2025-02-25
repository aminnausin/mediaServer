<script setup lang="ts">
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';
import { useAppStore } from '@/stores/AppStore';
import { ref, useTemplateRef, watch } from 'vue';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';

const props = withDefaults(
    defineProps<{
        modalData: any;
        action?: () => void;
        useControls?: boolean;
        isProcessing?: boolean;
    }>(),
    {
        action: () => {},
        useControls: true,
        isProcessing: false,
    },
);

const { setScrollLock } = useAppStore();
const title = useTemplateRef('modalTitle');
const processing = ref(false);

const submitModal = async (action: any, modalData: any) => {
    processing.value = true;
    await action();
    processing.value = false;
    modalData.toggleModal(false);
};

watch(
    () => props.modalData.isAnimating,
    (value) => {
        if ((props.modalData.modalOpen && value) || !value) {
            setScrollLock(props.modalData.modalOpen);
            title.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },
);
</script>
<template>
    <Teleport to="body">
        <div
            v-show="modalData.modalOpen || modalData.isAnimating"
            class="modal fixed top-0 left-0 z-[300] flex items-center justify-center w-screen h-screen text-neutral-900 dark:text-neutral-200"
            v-cloak
        >
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="modalData.modalOpen" class="absolute inset-0 w-full h-full backdrop-blur-sm bg-opacity-70"></div>
            </Transition>
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 -translate-y-2 sm:scale-95"
            >
                <UseFocusTrap v-if="modalData.modalOpen" class="relative w-full px-6 py-10 sm:py-6 max-h-screen h-full overflow-y-scroll scrollbar-hide flex items-center">
                    <OnClickOutside
                        @trigger="modalData.toggleModal(false)"
                        @keydown.esc="modalData.toggleModal(false)"
                        class="drop-shadow-md m-auto w-full p-6 bg-white dark:bg-neutral-800/90 backdrop-blur-lg border shadow-lg border-neutral-200 dark:border-neutral-700 sm:max-w-lg rounded-md sm:rounded-lg"
                        tabindex="-1"
                    >
                        <div class="flex items-center justify-between pb-3">
                            <h3 ref="modalTitle" class="text-xl font-semibold scroll-mt-16 sm:scroll-mt-12">{{ modalData?.title ?? 'Modal Title' }}</h3>
                            <ButtonCorner @click="modalData.toggleModal(false)" />
                        </div>
                        <slot name="content">
                            <div class="relative w-auto pb-8">
                                <p>This is placeholder text. Replace it with your own content.</p>
                            </div>
                        </slot>
                        <slot v-if="useControls" name="controls">
                            <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                                <button
                                    @click="modalData.toggleModal(false)"
                                    type="button"
                                    class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none"
                                    :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'"
                                    :disabled="processing || isProcessing"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="submitModal(action, modalData)"
                                    type="button"
                                    class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none"
                                    :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '"
                                    :disabled="processing || isProcessing"
                                >
                                    {{ modalData.submitText }}
                                </button>
                            </div>
                        </slot>
                    </OnClickOutside>
                </UseFocusTrap>
            </Transition>
        </div>
    </Teleport>
</template>
