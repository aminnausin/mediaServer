<script setup lang="ts">
import { toast, useForm } from '@aminnausin/cedar-ui';
import { ButtonForm } from '@/components/cedar-ui/button';
import { FormInput } from '@/components/cedar-ui/form';

import SettingsFormLabel from '@/components/settings/SettingsFormLabel.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';

const mediaAPI = {
    updateServerConfig: async (type: string, data: any) => {},
};

interface PathsConfigRequest {
    cache_path: string;
    metadata_path: string;
}

const form = useForm<PathsConfigRequest>({
    cache_path: '',
    metadata_path: '',
});

const handleSavePaths = () => {
    form.submit((fields) => mediaAPI.updateServerConfig('paths', fields), {
        onSuccess: () => toast.success('Path settings saved.'),
        onError: () => toast.error('Failed to save path settings.'),
    });
};
</script>

<template>
    <SettingsCard class="flex-col gap-6">
        <SettingsHeader>
            <h3 class="text-base font-medium">Storage Paths</h3>
            <p class="text-foreground-2">Override where the server writes data.</p>
        </SettingsHeader>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <SettingsFormLabel for="cache-path" text="Cache path" :subtext="'Temporary files are written here. Point to an SSD to improve speed.'" />
                <FormInput v-model="form.fields.cache_path" :field="{ name: 'cache-path', type: 'text', placeholder: 'storage/cache/' }" class="h-8" />
            </div>
            <div class="flex flex-col gap-1">
                <SettingsFormLabel for="metadata-path" text="Metadata path" :subtext="'Metadata files (subtitles, thumbnails, art) are written here.'" />
                <FormInput v-model="form.fields.metadata_path" :field="{ name: 'metadata-path', type: 'text', placeholder: 'storage/metadata/' }" class="h-8" />
            </div>
        </div>

        <div class="flex justify-end">
            <ButtonForm variant="submit" class="h-8" :disabled="form.processing" @click="handleSavePaths"> Save </ButtonForm>
        </div>
    </SettingsCard>
</template>
