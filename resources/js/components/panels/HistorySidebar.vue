<script setup lang="ts">
import { TableLoadingSpinner } from '@/components/cedar-ui/table';
import { useRecordsLimited } from '@/service/records/useRecords';
import { useModalStore } from '@/stores/ModalStore';
import { queryClient } from '@/service/vue-query';
import { ButtonText } from '@/components/cedar-ui/button';

import SidebarHeader from '@/components/headers/SidebarHeader.vue';
import RecordCard from '@/components/cards/data/RecordCard.vue';
import ShareModal from '@/components/modals/ShareModal.vue';

const modal = useModalStore();

const { stateRecords, isFetching: isLoadingRecords, isFetched } = useRecordsLimited(10);

const handleShare = (link: string) => {
    if (!link || link[0] !== '/') return;

    modal.open(ShareModal, { title: 'Share Track/Video', shareLink: globalThis.location.origin + link });
};

const invalidatePromise = queryClient.invalidateQueries({
    queryKey: ['records', 'limited'],
});

if (!isFetched.value) {
    await invalidatePromise;
}
</script>

<template>
    <SidebarHeader :text="'Recent History'" />

    <section id="list-content-history" class="full-height-sidebar flex flex-col flex-wrap gap-2">
        <TableLoadingSpinner
            v-if="(isLoadingRecords && !stateRecords?.length) || !stateRecords?.length"
            :is-loading="isLoadingRecords && !stateRecords?.length"
            :data-length="stateRecords?.length"
            class="text-base"
            no-results-message="Nothing"
        />
        <template v-else>
            <RecordCard v-for="(record, index) in stateRecords.slice(0, 10)" :key="record.id" :record="record" :index="index" @clickAction="handleShare" />
            <ButtonText
                v-if="stateRecords.length > 0"
                :to="'/history'"
                :title="'View All Watch History'"
                :class="'data-card mt-2 flex h-fit w-full items-center rounded-lg text-sm shadow-sm ring-transparent ring-inset'"
            >
                View More
            </ButtonText>
        </template>
    </section>
</template>
