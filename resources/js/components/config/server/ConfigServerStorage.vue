<script setup lang="ts">
import type { StorageConfigRequest } from '@/contracts/server';

import { UseUpdateStorageConfig } from '@/service/server/mutations';
import { toast, useForm } from '@aminnausin/cedar-ui';
import { useGetConfig } from '@/service/server/queries';
import { ButtonForm } from '@/components/cedar-ui/button';
import { FormInput } from '@/components/cedar-ui/form';
import { watch } from 'vue';

import ConfigServerSkeleton from '@/components/config/server/ConfigServerSkeleton.vue';
import ConfigFormLabel from '@/components/config/ConfigFormLabel.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
import ConfigHeader from '@/components/config/ConfigHeader.vue';

import FormErrorList from '@/components/cedar-ui/form/FormErrorList.vue';

const { data: serverConfig, isLoading } = useGetConfig();
const saveConfig = UseUpdateStorageConfig();

const form = useForm<StorageConfigRequest>({
    cache_path: '',
    metadata_path: '',
});

const handleSavePaths = () => {
    form.submit((fields) => saveConfig.mutateAsync(fields), {
        onSuccess: () => toast.success('Storage settings saved.'),
        onError: (error) => {
            if (error.status === 500) {
                toast.error('Failed to save Storage settings.');
            }
            console.log(error);
        },
    });
};

const resetForm = (config?: StorageConfigRequest) => {
    if (!config) return;
    form.fields = { ...config };
};

watch(
    serverConfig,
    (config) => {
        if (!config?.values.storage) return;
        resetForm(config.values.storage);
    },
    { immediate: true },
);
</script>

<template>
    <ConfigServerSkeleton v-if="isLoading" />
    <SettingsCard class="flex-col gap-6" v-else>
        <ConfigHeader :heading="'Storage Paths'"> Override where the server writes data. </ConfigHeader>

        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <ConfigFormLabel for="cache_path" text="Cache path" :subtext="'Temporary files are written here. Point to an SSD to improve speed.'" />
                <FormInput v-model="form.fields.cache_path" :field="{ name: 'cache_path', type: 'text', placeholder: 'storage/cache/' }" />
                <FormErrorList :errors="form.errors" :field-name="'cache_path'" />
            </div>

            <div class="flex flex-col gap-1">
                <ConfigFormLabel for="metadata_path" text="Metadata path" :subtext="'Metadata files (subtitles, thumbnails, art) are written here.'" />
                <FormInput v-model="form.fields.metadata_path" :field="{ name: 'metadata_path', type: 'text', placeholder: 'storage/metadata/' }" />
                <FormErrorList :errors="form.errors" :field-name="'metadata_path'" />
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-2">
            <ButtonForm variant="reset" class="h-8" :disabled="form.processing" @click="resetForm(serverConfig?.values.storage)"> Reset </ButtonForm>
            <ButtonForm variant="submit" class="h-8" :disabled="form.processing" @click="handleSavePaths"> Save </ButtonForm>
        </div>
    </SettingsCard>
</template>
