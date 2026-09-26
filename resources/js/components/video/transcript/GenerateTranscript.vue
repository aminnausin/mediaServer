<script setup lang="ts">
import type { SubtitleResource } from '@/contracts/media';

import { useContentStore } from '@/stores/ContentStore';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn, toast } from '@aminnausin/cedar-ui';
import { API } from '@/service/api';

import ProiconsArrowClockwise from '~icons/proicons/arrow-rotate-clockwise';
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

        if (!stateVideo.value.metadata?.id) {
            throw new Error('requestTranscript is not implemented yet');
        }

        status.value = 'processing';

        const { data } = await API.post(`/metadata/${stateVideo.value.metadata.id}/transcript`);

        status.value = 'idle';
        emit('generated', {
            url: data.subtitle_url,
            track: data.subtitle as SubtitleResource,
        });
    } catch (error) {
        status.value = 'error';
        toast.error(error instanceof Error ? error.message : 'Failed to generate transcript');
    }
}
</script>

<template>
    <div class="flex h-full flex-col items-center justify-center gap-3 px-8 py-10 text-center" role="status" aria-live="polite">
        <div
            :class="
                cn('flex size-11 items-center justify-center rounded-full transition-colors', {
                    'bg-danger-2/10 text-danger-2': status === 'error',
                    'bg-white/8 text-white/40': status === 'idle',
                    'bg-white/8 text-white/70': isBusy,
                })
            "
        >
            <ProiconsArrowClockwise v-if="isBusy" class="size-5 animate-spin" />
            <ProiconsAlertTriangle v-else-if="status === 'error'" class="size-5" />
            <IconCaptions v-else class="size-5" />
        </div>

        <div class="max-w-64 space-y-1">
            <p class="text-sm font-medium text-white/90">{{ copy.heading }}</p>
            <p class="text-xs leading-5 text-white/40">{{ copy.description }}</p>
        </div>

        <ButtonBase
            type="button"
            :disabled="isBusy"
            :use-size="false"
            :aria-busy="isBusy"
            class="mt-1 flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-neutral-900 transition hover:bg-white/90 disabled:cursor-not-allowed disabled:opacity-60"
            @click="requestTranscript"
        >
            <ProiconsArrowClockwise v-if="isBusy" class="size-3.5 animate-spin" />
            <ProiconsArrowSync v-else-if="status === 'error'" class="size-3.5" />
            <ProiconsSparkle v-else class="size-3.5" />
            <span>{{ copy.button }}</span>
        </ButtonBase>
    </div>
</template>
