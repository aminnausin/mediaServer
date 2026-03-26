<script setup lang="ts">
import { toFormattedDuration } from '@/service/util';
import { CopyToClipboard } from '@/components/cedar-ui/clipboard';
import { useModalStore } from '@/stores/ModalStore';
import { computed, ref } from 'vue';
import { BaseModal } from '@/components/cedar-ui/modal';
import { TextInput } from '../cedar-ui/input';
import { FormLabel } from '../cedar-ui/form';

import ToggleBase from '../inputs/ToggleBase.vue';
import { cn } from '@aminnausin/cedar-ui';

const modalStore = useModalStore();

const timestampFormatted = ref();
const timestampSeconds = ref();
const useTimestamp = ref(false);

const shareLink = computed(() => {
    return useTimestamp.value && timestampSeconds.value ? `${modalStore.props.shareLink}&t=${timestampSeconds.value}` : modalStore.props.shareLink;
});

const formatTimestamp = (value: string) => {
    if (!value) {
        timestampFormatted.value = toFormattedDuration(0, true, 'digital');
        return;
    }
    const parts = value
        .split(':')
        .reverse()
        .map((p) => parseInt(p) || 0);
    const multipliers = [1, 60, 3600];

    const seconds = parts.reduce((total, part, i) => total + part * (multipliers[i] ?? 0), 0);
    timestampSeconds.value = seconds;
    timestampFormatted.value = toFormattedDuration(seconds, true, 'digital');
};

formatTimestamp(`${modalStore.props.defaultTimestamp}`);
</script>

<template>
    <BaseModal>
        <template #title>{{ modalStore.props.title }}</template>
        <template #description> Copy link to clipboard to share it.</template>
        <CopyToClipboard :text="shareLink" />
        <div v-if="modalStore.props.defaultTimestamp !== undefined" class="flex items-center gap-1 text-sm">
            <ToggleBase
                v-model="useTimestamp"
                :name="'use-share-timestamp'"
                class="mr-2 h-6 w-12 shrink-0 rounded-full dark:border-neutral-700/70 dark:has-checked:border-neutral-700"
            />
            <FormLabel :text="'Start at'" :for="'use-share-timestamp'" class="text-nowrap" />
            <TextInput
                :class="
                    cn(
                        'h-6 w-auto rounded-none bg-transparent! px-0 font-mono shadow-none ring-0!',
                        'focus:border-foreground-0 mt-0.5 border-0 border-b border-solid border-b-transparent text-center transition-colors focus-within:border-b',
                        'disabled:opacity-disabled',
                    )
                "
                :style="{ width: `${timestampFormatted?.length + 1 || 6}ch`, 'min-width': '6ch' }"
                id="share-timestamp"
                v-model="timestampFormatted"
                :disabled="!useTimestamp"
                @blur="formatTimestamp(timestampFormatted)"
            />
        </div>
    </BaseModal>
</template>
