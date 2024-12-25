<script setup>
import { useAppStore } from '../stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useToast } from '../composables/useToast';

import { toast } from 'vue-sonner';

import LayoutBase from '../layouts/LayoutBase.vue';
import ButtonText from '../components/inputs/ButtonText.vue';

const toastr = useToast();
const appStore = useAppStore();
const { pageTitle, selectedSideBar } = storeToRefs(appStore);

onMounted(() => {
    pageTitle.value = 'Tests';
    selectedSideBar.value = '';
});
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
                                <ButtonText
                                    @click="
                                        toastr.add('Default Toast Notification', {
                                            type: 'danger',
                                            description: 'This is an example toast notification',
                                            life: 3000,
                                        })
                                    "
                                >
                                    <template #text> Test Notification </template>
                                </ButtonText>
                                <ButtonText
                                    @click="
                                        toastr.add('Default Toast Notification', {
                                            type: 'success',
                                            description: 'This is an example toast notification',
                                            life: 3000,
                                        })
                                    "
                                >
                                    <template #text> Test </template>
                                </ButtonText>
                                <ButtonText
                                    @click="
                                        toastr.add('Default Toast Notification', {
                                            type: 'warning',
                                            description: 'This is an example toast notification',
                                            life: 3000,
                                        })
                                    "
                                >
                                    <template #text> Test </template>
                                </ButtonText>
                                <ButtonText
                                    @click="
                                        toastr.add('Default Toast Notification', {
                                            type: 'info',
                                            life: 3000,
                                        })
                                    "
                                >
                                    <template #text> Test </template>
                                </ButtonText>
                                <ButtonText @click="toast.success('Event has been created')">
                                    <template #text> Test </template>
                                </ButtonText>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </LayoutBase>
</template>
