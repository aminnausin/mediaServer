import type { SeriesSizeHistory } from '@/contracts/media';
import type { ComputedRef } from 'vue';

import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { API } from '@/service/api';

export const useSeriesSizeHistory = (seriesId: ComputedRef<number | undefined>) =>
    useQuery({
        queryKey: ['series-size-history', seriesId],
        queryFn: () => (seriesId.value ? API.get<SeriesSizeHistory[]>(`/series/${seriesId.value}/size-history`).then((r) => r.data) : []),
        enabled: computed(() => !!seriesId.value),
    });
