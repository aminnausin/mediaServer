import type { SeriesRelation, SeriesSizeHistory } from '@/contracts/media';
import type { ComputedRef, Ref } from 'vue';

import { computed, ref } from 'vue';
import { useQuery } from '@tanstack/vue-query';
import { API } from '@/service/api';

export const useSeriesSizeHistory = (seriesId: ComputedRef<number | undefined>) =>
    useQuery({
        queryKey: ['series-size-history', seriesId],
        queryFn: () => (seriesId.value ? API.get<SeriesSizeHistory[]>(`/series/${seriesId.value}/size-history`).then((r) => r.data) : []),
        enabled: computed(() => !!seriesId.value),
    });

export const useGetSeriesRelations = (seriesId: Ref<number | undefined>, enabled: Ref<boolean> = ref(true)) =>
    useQuery({
        queryKey: ['series-relations', seriesId],
        queryFn: async () => (await API.get(`/series/${seriesId.value!}/relations`)).data as SeriesRelation[],
        enabled: computed(() => !!seriesId.value && enabled.value),
    });
