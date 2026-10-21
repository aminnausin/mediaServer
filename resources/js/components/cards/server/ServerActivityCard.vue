<script setup lang="ts">
import { useGetSiteAnalytics } from '@/service/queries';
import { periodForHumans } from '@/service/pulseUtil';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import DashboardCard from '@/components/cards/layout/DashboardCard.vue';

import LucideChartNoAxesCombined from '~icons/lucide/chart-no-axes-combined';
import LucideTrendingDown from '~icons/lucide/trending-down';
import LucideTrendingUp from '~icons/lucide/trending-up';
import ProiconsLibrary from '~icons/proicons/library';
import LucideMinus from '~icons/lucide/minus';
import LucideUsers from '~icons/lucide/users';
import IconFolder from '@/components/icons/IconFolder.vue';
import LucideTags from '~icons/lucide/tags';
import IconMusic from '@/components/icons/IconMusic.vue';
import IconPlay from '@/components/icons/IconPlay.vue';
import IconEye from '@/components/icons/IconEye.vue';

const statIcons = {
    libraries: ProiconsLibrary,
    folders: IconFolder,
    videos: IconPlay,
    songs: IconMusic,
    users: LucideUsers,
    tags: LucideTags,
    views: IconEye,
} as const;

const props = defineProps<{ period: string }>();
const period = computed(() => props.period);

const { data: stats, isLoading } = useGetSiteAnalytics(period);

const getStatIcon = (title: string) => statIcons[title as keyof typeof statIcons];

const getChangeIcon = (change: number) => {
    if (change > 0) return LucideTrendingUp;
    if (change < 0) return LucideTrendingDown;
    return LucideMinus;
};
</script>
<template>
    <DashboardCard
        title="Server activity over time"
        name="Activity"
        cols="2"
        :rows="4"
        :details="`past ${periodForHumans(period)}`"
        class="@container lg:col-span-6 2xl:col-span-6"
    >
        <template #icon>
            <LucideChartNoAxesCombined class="h-6 w-6" />
        </template>

        <div class="grid grid-cols-1 gap-2 @[13rem]:grid-cols-2 @lg:grid-cols-4 @[64rem]:grid-cols-7">
            <template v-if="isLoading">
                <div v-for="i in 7" :key="i" class="data-card pulse-card flex min-w-0 flex-col gap-3 rounded-lg p-3">
                    <div class="flex animate-pulse items-center gap-2">
                        <div class="bg-foreground-3/40 size-4 shrink-0 rounded" />
                        <div class="bg-foreground-3/40 h-3 w-20 max-w-full rounded" />
                    </div>

                    <div class="flex animate-pulse flex-wrap items-center justify-between gap-x-3 gap-y-1">
                        <div class="bg-foreground-3/80 h-4 w-8 rounded" />
                        <div class="bg-foreground-3/40 ms-auto size-4 shrink-0 rounded" />
                    </div>
                </div>
            </template>

            <template v-else>
                <div v-for="stat in stats?.changes" :key="stat.title" class="data-card pulse-card flex min-w-0 flex-col gap-2 rounded-lg p-3">
                    <div class="text-foreground-2 flex items-center gap-2">
                        <component :is="getStatIcon(stat.title)" class="*:stroke-1.5 size-4 shrink-0" />
                        <span class="truncate capitalize">
                            {{ stat.title }}
                        </span>
                    </div>

                    <div class="xsm:flex-nowrap flex flex-wrap items-center justify-between gap-x-3 gap-y-1 sm:flex-wrap md:flex-nowrap">
                        <span class="truncate text-base font-semibold tabular-nums" :title="stat.count ?? 0">
                            {{ (stat.count ?? 0).toLocaleString() }}
                        </span>

                        <div
                            :class="
                                cn('xsm:w-fit 3xl:truncate flex w-full items-center gap-1 text-xs sm:w-full md:w-fit', 'text-foreground-3', {
                                    'dark:text-success text-green-700': stat.change > 0,
                                    'text-rose-500': stat.change < 0,
                                })
                            "
                            :title="`${stat.change_pct}% change`"
                        >
                            <component :is="getChangeIcon(stat.change)" class="size-3.5 shrink-0" />
                            <span class="tabular-nums"> {{ stat.change > 0 ? '+' : '' }}{{ stat.change.toLocaleString() }} </span>
                            <span class="xsm:hidden 3xl:block truncate sm:block md:hidden"> this period </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </DashboardCard>
</template>
