<script setup lang="ts">
import { TableLoadingSpinner } from '@/components/cedar-ui/table';
import { useRecordsLimited } from '@/service/records/useRecords';
import { CopyToClipboard } from '@/components/cedar-ui/clipboard';
import { ButtonText } from '@/components/cedar-ui/button';
import { ModalBase } from '@/components/cedar-ui/modal';
import { ref } from 'vue';

import SidebarHeader from '@/components/headers/SidebarHeader.vue';
import RecordCard from '@/components/cards/data/RecordCard.vue';
import useModal from '@/composables/useModal';

const shareModal = useModal({ title: 'Share Video' });
const shareLink = ref('');

const { stateRecords, isLoading: isLoadingRecords } = useRecordsLimited(10);

const handleShare = (link: string) => {
    if (!link || link[0] !== '/') return;

    shareLink.value = globalThis.location.origin + link;
    shareModal.toggleModal(true);
};
</script>

<template>
    <SidebarHeader />

    <section id="list-content-history" class="flex flex-wrap gap-2">
        <TableLoadingSpinner
            v-if="isLoadingRecords || !stateRecords?.length"
            :is-loading="isLoadingRecords"
            :data-length="stateRecords?.length"
            no-results-message="Nothing Yet..."
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

    <ModalBase :modalData="shareModal">
        <template #description> Copy link to clipboard to share it.</template>
        <template #controls>
            <CopyToClipboard :text="shareLink" />
        </template>
    </ModalBase>
</template>
