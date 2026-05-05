<script setup lang="ts">
import { FormNumberField } from '@/components/cedar-ui/number-field';
import { toast, useForm } from '@aminnausin/cedar-ui';
import { ButtonForm } from '@/components/cedar-ui/button';

import SettingsFormLabel from '@/components/settings/SettingsFormLabel.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';

const mediaAPI = {
    updateServerConfig: async (type: string, data: any) => {},
};

interface PerformanceConfigRequest {
    max_queue_workers: number;
    max_websocket_workers: number;
}

const form = useForm<PerformanceConfigRequest>({
    max_queue_workers: 10,
    max_websocket_workers: 5,
});

const handleSavePerformance = () => {
    form.submit((fields) => mediaAPI.updateServerConfig('performance', fields), {
        onSuccess: () => toast.success('Performance settings saved.'),
        onError: () => toast.error('Failed to save performance settings.'),
    });
};
</script>

<template>
    <SettingsCard class="flex-col gap-6">
        <SettingsHeader>
            <h3 class="text-base font-medium">Performance</h3>
            <p class="text-foreground-2">Tune concurrency limits to match your hardware. Changes require the queue worker to restart.</p>
        </SettingsHeader>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <SettingsFormLabel
                    for="max-queue-workers"
                    text="Max parallel scan workers"
                    subtext="Number of processes handling file indexing, thumbnail generation, and UUID embedding."
                />
                <FormNumberField
                    v-model="form.fields.max_queue_workers"
                    :field="{ name: 'max-queue-workers', type: 'number', min: 1, max: 30, default: 10, placeholder: '10' }"
                    class="h-8"
                />
            </div>
            <div class="flex flex-col gap-1">
                <SettingsFormLabel for="max-ws-workers" text="Max parallel event workers" subtext="Number of processes handling real-time notifications and page updates." />
                <FormNumberField
                    v-model="form.fields.max_websocket_workers"
                    :field="{ name: 'max-ws-workers', type: 'number', min: 1, max: 30, default: 5, placeholder: '5' }"
                    class="h-8"
                />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <ButtonForm variant="reset" class="h-8" :disabled="form.processing" @click="form.reset(...Object.keys(form.fields))"> Reset </ButtonForm>
            <ButtonForm variant="submit" class="h-8" :disabled="form.processing" @click="handleSavePerformance"> Save </ButtonForm>
        </div>
    </SettingsCard>
</template>
