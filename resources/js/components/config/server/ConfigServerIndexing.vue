<script setup lang="ts">
import type { ScannerConfigRequest } from '@/contracts/server';

import { UseUpdateScannerConfig } from '@/service/server/mutations';
import { toast, useForm } from '@aminnausin/cedar-ui';
import { FormErrorList } from '@/components/cedar-ui/form';
import { useGetConfig } from '@/service/server/queries';
import { ButtonForm } from '@/components/cedar-ui/button';
import { watch } from 'vue';

import ConfigToggleRow from '@/components/config/ConfigToggleRow.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
import ConfigHeader from '@/components/config/ConfigHeader.vue';

const { data: serverConfig, isLoading } = useGetConfig();
const saveConfig = UseUpdateScannerConfig();

const form = useForm<ScannerConfigRequest>({
    uuid_embed: false,
    uuid_write_cache: false,
    attachments_extract: false,
    thumbnails_generate: false,
    art_extract: false,
});

const handleSaveScanner = () => {
    form.submit((fields) => saveConfig.mutateAsync(fields), {
        onSuccess: () => toast.success('Scanner config saved.'),
        onError: () => toast.error('Failed to save scanner config.'),
    });
};

const resetForm = (config?: ScannerConfigRequest) => {
    if (!config) return;
    form.fields = { ...config };
};

watch(
    serverConfig,
    (config) => {
        if (!config?.values.scanner) return;
        resetForm(config.values.scanner);
    },
    { immediate: true },
);
</script>

<template>
    <SettingsCard :class="['flex-col gap-6', { 'animate-pulse': isLoading }]">
        <template v-if="isLoading">
            <div>
                <div class="mb-1 h-6 w-32 rounded bg-neutral-200 dark:bg-neutral-700" />
                <div class="mb-5 h-5 w-72 rounded bg-neutral-200/70 dark:bg-neutral-700/50" />
            </div>

            <div v-for="i in 5" :key="i" class="divide-d flex items-center justify-between">
                <div class="space-y-1.5">
                    <div class="h-5 w-96 max-w-2/3 rounded bg-neutral-200 dark:bg-neutral-700" />
                    <div class="h-3 w-4/5 rounded bg-neutral-200/70 dark:bg-neutral-700/50" />
                </div>
                <div class="h-5 w-9 rounded-full bg-neutral-300 dark:bg-neutral-600" />
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <div class="h-8 w-16 rounded-md bg-neutral-300 dark:bg-neutral-700" />
                <div class="h-8 w-14 rounded-md bg-neutral-300 dark:bg-neutral-700" />
            </div>
        </template>

        <template v-else>
            <ConfigHeader :heading="'File Scanner'"> Control what gets extracted when files are indexed. </ConfigHeader>

            <div class="divide-d space-y-4 divide-y *:pb-4 *:last:pb-0">
                <ConfigToggleRow
                    id="uuid_embed"
                    label="Embed UUID in files"
                    description="Links files to persistent metadata in the database. Increases index time and re-writes each file to disk."
                    v-model="form.fields.uuid_embed"
                    :errors="form.errors"
                />

                <ConfigToggleRow
                    id="uuid_write_cache"
                    label="Use cache for UUID writes"
                    description="Write temporary files to cache. Improves index speed when cache is on an SSD."
                    v-model="form.fields.uuid_write_cache"
                    :disabled="!form.fields.uuid_embed"
                    :errors="form.errors"
                />

                <ConfigToggleRow
                    id="attachments_extract"
                    label="Extract embedded attachments"
                    description="Automatically extract fonts and subtitle files at index time."
                    v-model="form.fields.attachments_extract"
                    :errors="form.errors"
                />

                <ConfigToggleRow
                    id="thumbnails_generate"
                    label="Generate thumbnails"
                    description="Automatically generate thumbnails at index time."
                    v-model="form.fields.thumbnails_generate"
                    :errors="form.errors"
                />

                <ConfigToggleRow
                    id="art_extract"
                    label="Extract album art"
                    description="Automatically extract album art from music at index time."
                    v-model="form.fields.art_extract"
                    :errors="form.errors"
                />
            </div>

            <FormErrorList :errors="form.errors" />

            <div class="flex flex-wrap justify-end gap-2 *:h-8">
                <ButtonForm @click="resetForm(serverConfig?.values.scanner)" type="button" variant="reset" :disabled="form.processing"> Reset </ButtonForm>
                <ButtonForm variant="submit" :disabled="form.processing" @click="handleSaveScanner"> Save </ButtonForm>
            </div>
        </template>
    </SettingsCard>
</template>
