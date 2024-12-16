<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { getStats } from '@/service/siteAPI';

import PulseServers from '@/components/pulseCards/PulseServers.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import StatsCard from '@/components/cards/StatsCard.vue';
import useModal from '@/composables/useModal';

const cancelModal = useModal({ title: 'Cancel Job?', submitText: 'Confim' });
const appStore = useAppStore();
const stats = ref<{ title: string; count: number }[]>([]);

const { pageTitle, selectedSideBar } = storeToRefs(appStore);

const handleCancel = () => {
    // cachedID.value = id;
    cancelModal.toggleModal(true);
};

const setPeriod = (period: string) => {
    let query = new URLSearchParams(window.location.search);
    if (period === '1_hour') {
        query.delete('period');
    } else {
        query.set('period', period);
    }

    // window.location = `${location.pathname}?${query}`;
};

onMounted(async () => {
    pageTitle.value = 'Dashboard';
    selectedSideBar.value = '';
    // (async () => {
    //     await getRecords();
    //     loading.value = false;
    // })();
    const { data } = await getStats();
    stats.value = data;
});
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-dashboard" class="flex flex-wrap gap-4 flex-col min-h-[80vh]">
                <PulseServers />
                <span class="mx-auto grid grid-flow-row auto-rows-max gap-6 w-full">
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
            <div class="flex p-1 text-ri">
                <h1 id="sidebar-title" class="text-2xl h-8 w-full capitalize dark:text-white">{{ 'Management' }}</h1>
            </div>

            <hr class="mt-2 mb-3" />

            <section class="sm:text-lg flex flex-col gap-2">
                <div
                    class="flex justify-between items-center bg-white rounded-lg p-2 shadow ring-inset ring-purple-600 hover:ring-[0.125rem]"
                >
                    Pulse
                    <div class="flex items-center">
                        <button
                            @click="setPeriod('1_hour')"
                            class="p-1 font-semibold sm:text-lg text-gray-300 dark:text-gray-600 hover:text-gray-400 dark:hover:text-gray-500"
                        >
                            1h
                        </button>
                        <button
                            @click="setPeriod('6_hours')"
                            class="p-1 font-semibold sm:text-lg text-gray-300 dark:text-gray-600 hover:text-gray-400 dark:hover:text-gray-500"
                        >
                            6h
                        </button>
                        <button @click="setPeriod('24_hours')" class="p-1 font-semibold sm:text-lg text-gray-700 dark:text-gray-300">
                            24h
                        </button>
                        <button
                            @click="setPeriod('7_days')"
                            class="p-1 font-semibold sm:text-lg text-gray-300 dark:text-gray-600 hover:text-gray-400 dark:hover:text-gray-500"
                        >
                            7d
                        </button>
                    </div>
                </div>
                <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow">Activity</div>
                <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow">Jobs</div>
                <div class="flex justify-between items-center bg-white rounded-lg p-2 shadow">Content</div>
            </section>
        </template>
    </LayoutBase>
</template>
