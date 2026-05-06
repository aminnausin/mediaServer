<script setup lang="ts">
import type { PerformanceConfigRequest } from '@/contracts/server';

import { UseUpdatePerformanceConfig } from '@/service/server/mutations';
import { FormNumberField } from '@/components/cedar-ui/number-field';
import { toast, useForm } from '@aminnausin/cedar-ui';
import { useGetConfig } from '@/service/server/queries';
import { ButtonForm } from '@/components/cedar-ui/button';
import { watch } from 'vue';

import SettingsServerSkeleton from '@/components/settings/server/SettingsServerSkeleton.vue';
import SettingsFormLabel from '@/components/settings/SettingsFormLabel.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';

const { data: serverConfig, isLoading } = useGetConfig();
const saveConfig = UseUpdatePerformanceConfig();

const form = useForm<PerformanceConfigRequest>({
    max_scan_workers: 10,
    max_event_workers: 5,
});

const handleSavePerformance = () => {
    form.submit((fields) => saveConfig.mutateAsync(fields), {
        onSuccess: () => toast.success('Performance settings saved.'),
        onError: (error) => {
            if (error.status === 500) {
                toast.error('Failed to save performance settings.');
            }
            console.log(error);
        },
    });
};

const resetForm = (config?: PerformanceConfigRequest) => {
    if (!config) return;
    form.fields = { ...config };
};

watch(
    serverConfig,
    (config) => {
        if (!config?.values.performance) return;
        resetForm(config.values.performance);
    },
    { immediate: true },
);
</script>

<template>
    <SettingsServerSkeleton v-if="isLoading" />
    <SettingsCard class="flex-col gap-6" v-else>
        <SettingsHeader>
            <h3 class="text-base font-medium">Performance</h3>
            <p class="text-foreground-2">Tune concurrency limits to match your hardware. Changes require the queue worker to restart.</p>
        </SettingsHeader>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <SettingsFormLabel
                    for="max_scan_workers"
                    text="Max parallel scan workers"
                    subtext="Number of processes handling file indexing, thumbnail generation, and UUID embedding."
                />
                <FormNumberField
                    v-model="form.fields.max_scan_workers"
                    :field="{
                        name: 'max_scan_workers',
                        type: 'number',
                        min: 1,
                        max: 20,
                        placeholder: serverConfig?.defaults.performance.max_scan_workers.toString(),
                    }"
                    class="h-8"
                />
            </div>
            <div class="flex flex-col gap-1">
                <SettingsFormLabel for="max_event_workers" text="Max parallel event workers" subtext="Number of processes handling real-time notifications and page updates." />
                <FormNumberField
                    v-model="form.fields.max_event_workers"
                    :field="{
                        name: 'max_event_workers',
                        type: 'number',
                        min: 1,
                        max: 15,
                        placeholder: serverConfig?.defaults.performance.max_event_workers.toString(),
                    }"
                    class="h-8"
                />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <ButtonForm variant="reset" class="h-8" :disabled="form.processing" @click="resetForm(serverConfig?.values.performance)"> Reset </ButtonForm>
            <ButtonForm variant="submit" class="h-8" :disabled="form.processing" @click="handleSavePerformance"> Save </ButtonForm>
        </div>
    </SettingsCard>
</template>
