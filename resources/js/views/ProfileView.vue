<script setup>
import LayoutBase from '../components/layout/LayoutBase.vue';
import useToast from '../composables/useToast';
import ButtonText from '../components/inputs/ButtonText.vue'

import { onMounted } from 'vue';
import { useAppStore } from '../stores/AppStore';
import { storeToRefs } from 'pinia';

const emit = defineEmits(['toast-show']);
const toast = useToast({emit});

const appStore = useAppStore();

const { pageTitle, selectedSideBar } = storeToRefs(appStore);

onMounted(() => {
    pageTitle.value = "Tests";
    selectedSideBar.value = '';
})
</script>

<template>
    <LayoutBase>
        <template #content>
            <section class="min-h-[80vh]">
                <div class="relative w-auto h-auto">
                    <div class="relative space-y-5">
                        <div class="relative">
                            <p class="mb-2 text-xs font-medium text-center text-gray-500 sm:text-left">Tests</p>
                            <div class="relative flex flex-col px-10 space-y-2 sm:space-x-5 sm:space-y-0 sm:flex-row sm:px-0">
                                <ButtonText  @click="toast.popToast();">
                                    <template #text>
                                        Test Notification
                                    </template>
                                </ButtonText>
                                <!-- <button class="inline-flex items-center justify-center flex-shrink-0 px-3 py-1 text-xs font-medium transition-colors border rounded-md h-9 hover:bg-gray-50 active:bg-white focus:bg-white focus:outline-none"
                                    @click="$emit('setPage', null)">
                                    Default
                                </button> -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </LayoutBase>
</template>