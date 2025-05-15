<script setup>
import { useAppStore } from '../stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { toast } from '@/service/toaster/toastService';

import LayoutBase from '../layouts/LayoutBase.vue';
import ButtonText from '../components/inputs/ButtonText.vue';

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
                            <div class="relative flex flex-col px-10 space-y-2 sm:space-x-5 sm:space-y-0 sm:flex-row sm:px-0">
                                <ButtonText
                                    @click="
                                        toast.add('Default Toast Notification', {
                                            type: 'danger',
                                            description: `The taxi driver Odokawa lives a very mundane life. He has no family, doesn't really hang out with others, and he's an oddball who is narrow-minded and doesn't talk much. The only people he can call his friends are his doctor, Gouriki and his classmate from high school, Kakihana.

All of his patrons seem to be slightly odd themselves. The college student who wants the world to notice him online, Kabasawa. A nurse with secrets named Shirakawa. A comedy duo that just can't catch a break named the Homosapiens. A local hoodlum named Dobu. An idol group that's just starting out named Mystery Kiss... All these mundane conversations somehow eventually lead to a girl who's gone missing.
`,
                                            life: 3000,
                                            position: 'top-right',
                                        })
                                    "
                                >
                                    <template #text> Test Notification </template>
                                </ButtonText>
                                <ButtonText
                                    @click="
                                        toast.add('Default Toast Notification', {
                                            type: 'success',
                                            description: 'This is an example toast notification',
                                            life: 3000,
                                            position: 'bottom-left',
                                        })
                                    "
                                >
                                    <template #text> Test </template>
                                </ButtonText>
                                <ButtonText
                                    @click="
                                        toast.add('Default Toast Notification', {
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
                                        toast.add('Default Toast Notification', {
                                            type: 'info',
                                            life: 3000,
                                        })
                                    "
                                >
                                    <template #text> Test </template>
                                </ButtonText>
                                <ButtonText @click="toast('Event has been created')">
                                    <template #text> Test </template>
                                </ButtonText>
                                <ButtonText
                                    @click="
                                        toast.error(`The taxi driver Odokawa lives a very mundane life. He has no family, doesn't really hang out with others, and he's an oddball who is narrow-minded and doesn't talk much. The only people he can call his friends are his doctor, Gouriki and his classmate from high school, Kakihana.

All of his patrons seem to be slightly odd themselves. The college student who wants the world to notice him online, Kabasawa. A nurse with secrets named Shirakawa. A comedy duo that just can't catch a break named the Homosapiens. A local hoodlum named Dobu. An idol group that's just starting out named Mystery Kiss... All these mundane conversations somehow eventually lead to a girl who's gone missing.
`)
                                    "
                                >
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
