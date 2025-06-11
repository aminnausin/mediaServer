<script setup lang="ts">
import { ref, useTemplateRef, watch } from 'vue';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';
import { useAppStore } from '@/stores/AppStore';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';

const props = withDefaults(
    defineProps<{
        modalData: any;
        action?: () => Promise<void>;
        useControls?: boolean;
        isProcessing?: boolean;
    }>(),
    {
        useControls: true,
        isProcessing: false,
    },
);

const { setScrollLock } = useAppStore();
const title = useTemplateRef('modalTitle');
const processing = ref(false);

const submitModal = async () => {
    processing.value = true;
    await props.action?.();
    processing.value = false;
    closeModal();
};

const closeModal = () => props.modalData.toggleModal(false);

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
        <dialog
            v-show="modalData.modalOpen || modalData.isAnimating"
            class="modal fixed top-0 left-0 z-[300] flex items-center justify-center w-screen h-screen text-gray-900 dark:text-neutral-200"
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
                <div v-if="modalData.modalOpen" class="absolute inset-0 w-full h-full backdrop-blur-sm bg-opacity-70"></div>
            </Transition>
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 sm:scale-95"
                enter-to-class="opacity-100 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 sm:scale-100"
                leave-to-class="opacity-0 sm:scale-95"
            >
                <UseFocusTrap v-if="modalData.modalOpen" class="relative w-full px-6 py-10 sm:py-6 max-h-screen h-full overflow-y-scroll scrollbar-hide flex items-center">
                    <OnClickOutside
                        @trigger="closeModal"
                        @keydown.esc="closeModal"
                        class="gap-4 flex flex-col drop-shadow-md m-auto w-full p-6 bg-white dark:bg-neutral-800/90 backdrop-blur-lg border shadow-lg border-neutral-200 dark:border-neutral-700 sm:max-w-lg xl:max-w-xl 3xl:max-w-2xl rounded-md sm:rounded-lg"
                        tabindex="-1"
                    >
                        <section class="flex flex-wrap gap-2 items-center">
                            <h3 ref="modalTitle" id="modalTitle" class="text-xl font-semibold scroll-mt-16 sm:scroll-mt-12 flex-1">{{ modalData?.title ?? 'Modal Title' }}</h3>
                            <ButtonCorner @click="closeModal" class="!m-0 !static" />
                            <p class="text-neutral-500 dark:text-neutral-400 text-sm w-full" v-if="$slots.description" id="modalDescription">
                                <slot name="description"> </slot>
                            </p>
                        </section>
                        <slot name="content"> </slot>
                        <slot v-if="useControls" name="controls">
                            <section class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                                <ButtonForm type="button" variant="reset" @click="closeModal" :disabled="processing || isProcessing"> Cancel </ButtonForm>
                                <ButtonForm type="button" variant="submit" @click="submitModal()" :disabled="processing || isProcessing">
                                    {{ modalData.submitText }}
                                </ButtonForm>
                            </section>
                        </slot>
                    </OnClickOutside>
                </UseFocusTrap>
            </Transition>
        </dialog>
    </Teleport>
</template>
