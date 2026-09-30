<script setup lang="ts">
import type { SubtitleResource } from '@/contracts/media';

import { useContentStore } from '@/stores/ContentStore';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { API } from '@/service/api';
import { cn } from '@aminnausin/cedar-ui';

import TranscriptStatus from '@/components/video/transcript/TranscriptStatus.vue';

import ProiconsAlertTriangle from '~icons/proicons/alert-triangle';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsSparkle from '~icons/proicons/sparkle';
import IconCaptions from '@/components/icons/IconCaptions.vue';

type TranscriptStatus = 'idle' | 'requesting' | 'queued' | 'processing' | 'error';

const emit = defineEmits<{ generated: [track: { url: string; track: SubtitleResource }] }>();

const { stateVideo } = storeToRefs(useContentStore());

const status = ref<TranscriptStatus>('idle');

const isBusy = computed(() => status.value === 'requesting' || status.value === 'queued' || status.value === 'processing');

const copy = computed(() => {
    switch (status.value) {
        case 'error':
            return {
                heading: "Couldn't generate transcript",
                description: 'Something went wrong',
                button: 'Retry',
            };
        case 'requesting':
            return {
                heading: 'Requesting transcript',
                button: 'Requesting…',
            };
        case 'queued':
            return {
                heading: 'Transcript queued',
                button: 'Queued…',
            };
        case 'processing':
            return {
                heading: 'Generating transcript',
                description: 'This will take a moment',
                button: 'Generating…',
            };
        default:
            return {
                heading: 'No transcript available',
                description: 'Generate a transcript to follow along with the video',
                button: 'Generate Transcript',
            };
    }
});

async function requestTranscript() {
    status.value = 'requesting';

    try {
        // Todo: implement ws based transcript generation
        // const { jobId } = await api.requestTranscript(videoId);
        // status.value = 'queued';
        //
        // subscribeToTranscriptJob(jobId, {
        //     onQueued: () => { status.value = 'queued'; },
        //     onProcessing: () => { status.value = 'processing'; },
        //     onComplete: (track) => {
        //         status.value = 'idle';
        //         emit('generated', track);
        //     },
        //     onFailed: () => {
        //         status.value = 'error';
        //     },
        // });

        const metadataId = stateVideo.value.metadata?.id;

        if (!metadataId) {
            throw new Error('requestTranscript is not implemented yet');
        }

        status.value = 'processing';

        const { data } = await API.post(`/metadata/${metadataId}/transcript`, undefined, {
            headers: {
                'X-Skip-Toast': true,
            },
        });

        status.value = 'idle';

        if (stateVideo.value.metadata?.id !== metadataId) {
            console.error('Selected video changed while loading transcript.', { old: metadataId, new: stateVideo.value.metadata?.id });
            return;
        }

        emit('generated', {
            url: data.subtitle_url,
            track: data.subtitle as SubtitleResource,
        });
    } catch (error) {
        status.value = 'error';
    }
}
</script>

<template>
    <TranscriptStatus
        :class="$attrs.class"
        :is-busy="isBusy"
        :copy="copy"
        :badge-class="cn({ 'bg-danger-2/10 text-danger-2': status === 'error', 'bg-white/8 text-white/40': status === 'idle' })"
        @action="requestTranscript"
    >
        <template #icon>
            <ProiconsAlertTriangle v-if="status === 'error'" class="size-4 sm:size-5" />
            <IconCaptions v-else class="size-4 sm:size-5" />
        </template>
        <template #buttonIcon>
            <ProiconsArrowSync v-if="status === 'error'" class="size-3.5" />
            <ProiconsSparkle v-else class="size-3.5" />
        </template>
    </TranscriptStatus>
</template>
