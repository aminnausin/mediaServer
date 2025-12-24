<script setup lang="ts">
import { useRecordsLimited } from '@/service/records/useRecords';
import { ref } from 'vue';

import TableLoadingSpinner from '@/components/table/TableLoadingSpinner.vue';
import ButtonClipboard from '@/components/pinesUI/ButtonClipboard.vue';
import SidebarHeader from '@/components/headers/SidebarHeader.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';
import RecordCard from '@/components/cards/RecordCard.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';
import useModal from '@/composables/useModal';

const shareModal = useModal({ title: 'Share Video' });
const shareLink = ref('');

const { stateRecords, isLoading: isLoadingRecords } = useRecordsLimited(10);

const handleShare = (link: string) => {
    if (!link || link[0] !== '/') return;

    shareLink.value = window.location.origin + link;
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
                to="/history"
                :title="'View All Watch History'"
                :class="'bg-primary-800! dark:bg-primary-dark-800/70! dark:hover:bg-primary-dark-600! mx-auto mt-2 mb-2 line-clamp-1 h-8 truncate rounded-full! text-sm hover:bg-white! hover:ring-2 hover:ring-violet-400! dark:hover:ring-violet-700!'"
                :variant="'form'"
                target=""
            >
                <template #text>View More</template>
            </ButtonText>
        </template>
    </section>

    <ModalBase :modalData="shareModal">
        <template #description> Copy link to clipboard to share it.</template>
        <template #controls>
            <ButtonClipboard :text="shareLink" />
        </template>
    </ModalBase>
</template>
