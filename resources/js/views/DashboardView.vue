<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

import LayoutBase from '@/layouts/LayoutBase.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import useModal from '@/composables/useModal';

const cancelModal = useModal({ title: 'Cancel Job?', submitText: 'Confim' });
const appStore = useAppStore();

const { pageTitle, selectedSideBar } = storeToRefs(appStore);

const handleCancel = () => {
    // cachedID.value = id;
    cancelModal.toggleModal(true);
};

onMounted(() => {
    pageTitle.value = 'Dashboard';
    selectedSideBar.value = '';
    // (async () => {
    //     await getRecords();
    //     loading.value = false;
    // })();
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-dashboard" class="space-y-2 min-h-[80vh]"></section>
            <ModalBase :modalData="cancelModal" :action="handleCancel">
                <template #content>
                    <div class="relative w-auto pb-8">
                        <p>Are you sure you want to delete this record?</p>
                    </div>
                </template>
            </ModalBase>
        </template>
        <template v-slot:sidebar>
            <!-- <HistorySidebar /> -->
        </template>
    </LayoutBase>
</template>
