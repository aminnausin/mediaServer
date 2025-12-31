<script setup lang="ts">
// Legacy

import { ref, useTemplateRef, watch } from 'vue';
import { ButtonCorner, ButtonForm } from '@/components/cedar-ui/button';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';
import { useAppStore } from '@/stores/AppStore';

const props = withDefaults(
    defineProps<{
        modalData: any;
        // Should not be part of the modal
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
// Should not be part of the modal
const processing = ref(false);

// Should not be part of the modal
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
                leave-active-class="ease-in duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="modalData.modalOpen" class="bg-opacity-70 absolute inset-0 h-full w-full backdrop-blur-xs"></div>
            </Transition>
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 sm:scale-95"
                enter-to-class="opacity-100 sm:scale-100"
                leave-active-class="ease-in duration-(--duration-input)"
                leave-from-class="opacity-100 sm:scale-100"
                leave-to-class="opacity-0 sm:scale-95"
            >
                <UseFocusTrap v-if="modalData.modalOpen" class="scrollbar-hide relative flex h-full max-h-screen w-full items-center overflow-y-scroll px-6 py-10 sm:py-6">
                    <OnClickOutside
                        @trigger="closeModal"
                        @keydown.esc="closeModal"
                        class="3xl:max-w-xl bg-overlay-2-t border-overlay-border m-auto flex w-full flex-col gap-4 rounded-2xl border p-6 shadow-lg drop-shadow-md backdrop-blur-lg sm:max-w-lg xl:max-w-xl"
                        tabindex="-1"
                    >
                        <section class="flex flex-wrap items-center gap-2">
                            <h3 ref="modalTitle" id="modalTitle" class="flex-1 scroll-mt-16 text-xl font-semibold sm:scroll-mt-12">{{ modalData?.title ?? 'Modal Title' }}</h3>
                            <ButtonCorner @click="closeModal" class="hover:bg-overlay-accent static p-1.5" :use-default-style="false" />
                            <p class="text-foreground-2 w-full text-sm" v-if="$slots.description" id="modalDescription">
                                <slot name="description"> </slot>
                            </p>
                        </section>
                        <slot name="content"> </slot>
                        <slot v-if="useControls" name="controls">
                            <section class="relative flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
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
