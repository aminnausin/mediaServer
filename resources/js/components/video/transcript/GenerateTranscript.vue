<script setup lang="ts">
import type { SubtitleResource } from '@/contracts/media';

import { useTranscript } from '@/components/video/transcript/useTranscript';
import { computed } from 'vue';
import { useAuth } from '@/composables/auth/useAuth';
import { cn } from '@aminnausin/cedar-ui';

import TranscriptStatus from '@/components/video/transcript/TranscriptStatus.vue';

import ProiconsAlertTriangle from '~icons/proicons/alert-triangle';
import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsSparkle from '~icons/proicons/sparkle';
import IconCaptions from '@/components/icons/IconCaptions.vue';

const emit = defineEmits<{ generated: [track: { url: string; track: SubtitleResource }] }>();

const { generationStatus: status, requestTranscript } = useTranscript();
const { isAuthenticated } = useAuth();

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
            const heading = 'No transcript available';
            if (!isAuthenticated.value) return { heading };

            return {
                heading,
                description: 'Generate a transcript to follow along with the video',
                button: 'Generate Transcript',
            };
    }
});

// Todo: implement ws based transcript generation
async function generateTranscript() {
    try {
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
        status.value = 'idle';
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
