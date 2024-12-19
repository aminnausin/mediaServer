<script setup lang="ts">
import type { CategoryResource } from '@/types/resources';

import { nextTick, onMounted, ref, watch } from 'vue';
import { getCategories } from '@/service/mediaAPI';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { getStats } from '@/service/siteAPI';

import PulseServers from '@/components/pulseCards/PulseServers.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import LayoutBase from '@/layouts/LayoutBase.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import StatsCard from '@/components/cards/StatsCard.vue';
import useModal from '@/composables/useModal';

import LucideChartNoAxesCombined from '~icons/lucide/chart-no-axes-combined?width=24px&height=24px';
import ProiconsAddCircle from '~icons/proicons/add-circle?width=24px&height=24px';
import CircumEdit from '~icons/circum/edit';

const dashboardPages = [
    { title: 'overview', timeControls: true },
    { title: 'content', timeControls: false },
    { title: 'activity', timeControls: false },
    { title: 'users', timeControls: false },
    { title: 'jobs', timeControls: false },
];

const cancelModal = useModal({ title: 'Cancel Job?', submitText: 'Confim' });
const appStore = useAppStore();
const stats = ref<{ title: string; count: number }[]>([]);
const categories = ref<CategoryResource[]>([]);
const dashboardPage = ref('overview');

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

async function cycleSideBar(state: string) {
    if (!state) return;
    await nextTick();
    document.querySelector('#left-card')?.scrollIntoView({ behavior: 'smooth' });
}

onMounted(async () => {
    pageTitle.value = 'Dashboard';
    selectedSideBar.value = 'dashboard';

    const { data } = await getStats();
    stats.value = data;

    const { data: rawCategories } = await getCategories();

    categories.value = rawCategories?.data;
});
watch(() => selectedSideBar.value, cycleSideBar, { immediate: false });
</script>

<template>
    <LayoutBase>
        <template v-slot:content>
            <section id="content-dashboard" class="min-h-[80vh]">
                <section class="flex flex-wrap gap-4 flex-col" v-if="dashboardPage == 'overview'">
                    <PulseServers />
                    <span class="mx-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 w-full">
                        <StatsCard
                            v-for="stat in stats"
                            :title="`Total ${stat.title}`"
                            :forceCount="stat.count"
                            class="col-span-1 row-span-1 w-full"
                        />
                        <div
                            :class="`flex col-span-2 row-span-4 flex-col gap-2 p-4 overflow-clip rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5 w-full`"
                        >
                            <span class="flex items-center gap-2 w-full">
                                <LucideChartNoAxesCombined class="h-6 w-6"></LucideChartNoAxesCombined>
                                <p class="max-w-full text-nowrap">Totals</p>
                            </span>
                            <span v-for="(stat, index) in stats" :key="index" class="flex gap-2 capitalize items-center flex-wrap">
                                <h3 class="text-sm dark:text-slate-400 text-slate-500 w-full text-nowrap">Total {{ stat.title }}</h3>
                                <span>
                                    <h3 class="text-lg">{{ stat.count ?? 0 }}</h3>
                                </span>
                                <h4 class="text-sm text-green-600 ml-auto">+40%</h4>
                            </span>
                        </div>
                    </span>
                </section>
                <section v-if="dashboardPage == 'content'">
                    <span class="flex items-center justify-between">
                        <ButtonText title="Add New Category">
                            <template #text>
                                <div class="flex justify-between items-center gap-2">
                                    <p class="text-nowrap">Add New Category</p>
                                    <ProiconsAddCircle height="24" width="24" />
                                </div>
                            </template>
                        </ButtonText>
                        <p class="text-slate-400 uppercase">Categories: {{ categories.length }}</p>
                    </span>
                    <div class="flex flex-wrap w-full pt-4">
                        <div
                            v-for="(category, index) in categories"
                            :key="index"
                            class="flex flex-col gap-2 p-4 overflow-clip rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5 w-1/3"
                        >
                            <h3 class="capitalize text-lg flex items-end justify-between">
                                {{ category.name }}
                                <ButtonIcon class="w-6 h-6 !p-1">
                                    <template #icon>
                                        <CircumEdit class="w-full h-full" />
                                    </template>
                                </ButtonIcon>
                            </h3>
                            <p class="text-slate-400">Folders: {{ category.folders_count }}</p>
                        </div>
                    </div>
                </section>
            </section>
        </template>
        <template v-slot:leftSidebar>
            <!-- <HistorySidebar /> -->
            <div class="p-3">
                <div class="flex p-1">
                    <h1 id="sidebar-title" class="text-2xl h-8 w-full capitalize dark:text-white">{{ 'Management' }}</h1>
                </div>

                <hr class="mt-2 mb-3" />

                <section class="sm:text-lg flex flex-col gap-2">
                    <div
                        v-for="(page, index) in dashboardPages"
                        :key="index"
                        :class="`
                            flex flex-wrap items-center justify-between gap-2
                            rounded-lg shadow p-3 group cursor-pointer
                            capitalize dark:text-white overflow-hidden
                            dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 hover:bg-gray-200
                            ring-inset ring-purple-600/50 hover:ring-purple-600 hover:ring-[0.125rem] ${dashboardPage == page.title && 'ring-[0.125rem]'}
                        `"
                        @click="dashboardPage = page.title"
                    >
                        {{ page.title }}
                        <div class="flex items-center flex-wrap gap-2" v-if="page.timeControls">
                            <button
                                @click="setPeriod('1_hour')"
                                class="font-semibold sm:text-lg text-gray-300 dark:text-gray-600 hover:text-gray-400 dark:hover:text-gray-500"
                            >
                                1h
                            </button>
                            <button
                                @click="setPeriod('6_hours')"
                                class="font-semibold sm:text-lg text-gray-300 dark:text-gray-600 hover:text-gray-400 dark:hover:text-gray-500"
                            >
                                6h
                            </button>
                            <button @click="setPeriod('24_hours')" class="font-semibold sm:text-lg text-gray-700 dark:text-gray-300">
                                24h
                            </button>
                            <button
                                @click="setPeriod('7_days')"
                                class="font-semibold sm:text-lg text-gray-300 dark:text-gray-600 hover:text-gray-400 dark:hover:text-gray-500"
                            >
                                7d
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </template>
    </LayoutBase>
</template>
