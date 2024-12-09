<script setup lang="ts">
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { onMounted, ref } from 'vue';

import LayoutBase from '@/layouts/LayoutBase.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import StatsCard from '@/components/cards/StatsCard.vue';
import useModal from '@/composables/useModal';
import siteAPI from '@/service/siteAPI';

const cancelModal = useModal({ title: 'Cancel Job?', submitText: 'Confim' });
const appStore = useAppStore();
const stats = ref<{ title: string; count: number }[]>([]);

const { pageTitle, selectedSideBar } = storeToRefs(appStore);

const handleCancel = () => {
    // cachedID.value = id;
    cancelModal.toggleModal(true);
};

onMounted(async () => {
    pageTitle.value = 'Dashboard';
    selectedSideBar.value = '';
    // (async () => {
    //     await getRecords();
    //     loading.value = false;
    // })();
    const { data } = await siteAPI.getStats();
    stats.value = data;
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-dashboard" class="space-y-2 min-h-[80vh]">
                <span class="flex flex-wrap gap-4">
                    <StatsCard v-for="stat in stats" :title="`Total ${stat.title}`" :forceCount="stat.count" />
                </span>
            </section>
            <ModalBase :modalData="cancelModal" :action="handleCancel">
                <template #content>
                    <div class="relative w-auto pb-8">
                        <p>Are you sure you want to cancel this job?</p>
                    </div>
                </template>
            </ModalBase>
        </template>
        <template v-slot:sidebar>
            <!-- <HistorySidebar /> -->
        </template>
    </LayoutBase>
</template>
