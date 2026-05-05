<script setup lang="ts">
import { toast, useForm } from '@aminnausin/cedar-ui';
import { ButtonForm } from '@/components/cedar-ui/button';

import SettingToggleRow from '@/components/settings/SettingToggleRow.vue';
import SettingsHeader from '@/components/settings/SettingsHeader.vue';
import SettingsCard from '@/components/cards/layout/SettingsCard.vue';

const mediaAPI = {
    updateServerConfig: async (type: string, data: any) => {},
};

interface ScannerConfigRequest {
    embed_uuid: boolean;
    embed_uuid_cache: boolean;
    extract_attachments: boolean;
    extract_thumbnails: boolean;
    extract_art: boolean;
}

const form = useForm<ScannerConfigRequest>({
    embed_uuid: false,
    embed_uuid_cache: false,
    extract_attachments: true,
    extract_thumbnails: true,
    extract_art: true,
});

const handleSaveScanner = () => {
    form.submit((fields) => mediaAPI.updateServerConfig('scanner', fields), {
        onSuccess: () => toast.success('Scanner settings saved.'),
        onError: () => toast.error('Failed to save scanner settings.'),
    });
};
</script>

<template>
    <SettingsCard class="flex-col gap-6">
        <SettingsHeader>
            <h3 class="text-base font-medium">File Scanner</h3>
            <p class="text-foreground-2">Control what gets extracted when files are indexed.</p>
        </SettingsHeader>

        <div class="divide-d space-y-4 divide-y *:pb-4 *:last:pb-0">
            <SettingToggleRow
                id="embed-uuid"
                label="Embed UUID in files"
                description="Links files to persistent metadata in the database. Increases index time and re-writes each file to disk."
                v-model="form.fields.embed_uuid"
            />

            <SettingToggleRow
                id="embed-uuid-cache"
                label="Use cache for UUID writes"
                description="Write temporary files to cache. Improves index speed when cache is on an SSD."
                v-model="form.fields.embed_uuid_cache"
                :disabled="!form.fields.embed_uuid"
            />

            <SettingToggleRow
                id="extract-attachments"
                label="Extract embedded attachments"
                description="Automatically extract fonts and subtitle files at index time."
                v-model="form.fields.extract_attachments"
            />

            <SettingToggleRow
                id="extract-thumbnails"
                label="Generate thumbnails"
                description="Automatically generate thumbnails at index time."
                v-model="form.fields.extract_thumbnails"
            />

            <SettingToggleRow
                id="extract-art"
                label="Extract album art"
                description="Automatically extract album art from music at index time."
                v-model="form.fields.extract_art"
            />
        </div>

        <div class="flex justify-end gap-2 *:h-8">
            <ButtonForm @click="form.reset(...Object.keys(form.fields))" type="button" variant="reset" :disabled="form.processing"> Reset </ButtonForm>
            <ButtonForm variant="submit" :disabled="form.processing" @click="handleSaveScanner"> Save </ButtonForm>
        </div>
    </SettingsCard>
</template>
