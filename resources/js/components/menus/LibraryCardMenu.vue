<script setup lang="ts">
import type { CategoryResource, FolderResource } from '@/types/resources';

import { InputSelect } from '@/components/cedar-ui/select';
import { ButtonText } from '@/components/cedar-ui/button';
import { FormLabel } from '@/components/cedar-ui/form';
import { useAuth } from '@/composables/auth/useAuth';
import { FLAGS } from '@/config/featureFlags';
import { toast } from '@aminnausin/cedar-ui';

import TablerDownloadOff from '@/components/icons/TablerDownloadOff.vue';
import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';
import TablerDownload from '@/components/icons/TablerDownload.vue';
import ProIconsPhoto from '@/components/icons/ProIconsPhoto.vue';
import SectionLabel from '@/components/labels/SectionLabel.vue';

import ProiconsArrowSync from '~icons/proicons/arrow-sync';
import ProiconsLockOpen from '~icons/proicons/lock-open';
import ProiconsDelete from '~icons/proicons/delete';
import ProiconsLock from '~icons/proicons/lock';

const props = withDefaults(
    defineProps<{
        data?: CategoryResource;
        folders: FolderResource[];
        defaultFolder?: FolderResource;
        processing: boolean;
        handleSetDefaultFolder: (newFolder: { value: number }) => Promise<void>;
        handleStartScan: (verifyOnly?: boolean) => Promise<void>;
        handleStartGenerateStoryboards: () => Promise<void>;
        handleToggleSetting: (
            setting: keyof Pick<CategoryResource, 'is_private' | 'downloads_enabled' | 'downloads_require_auth' | 'storyboard_enabled'>,
            currentValue: boolean,
            successMessage: (newValue: boolean) => string,
        ) => Promise<void>;
    }>(),
    {},
);

const { isAdmin } = useAuth();
</script>

<template>
    <div class="space-y-4 text-xs lg:p-1 lg:text-sm" v-if="data">
        <div class="space-y-2 text-sm">
            <p class="leading-none font-medium">Manage Library</p>
            <p class="text-foreground-1">Set Library Properties.</p>
        </div>
        <div class="flex flex-col gap-2 *:h-8 dark:*:bg-neutral-900">
            <div class="flex h-auto! flex-col gap-1 bg-transparent!">
                <FormLabel text="Default Folder" for="default-folder" class="font-normal" />
                <InputSelect
                    v-if="isAdmin"
                    name="default-folder"
                    root-class="flex-1 rounded-l-none capitalize w-full! whitespace-nowrap! col-span-2 text-xs lg:text-sm"
                    class="h-8! py-0 ps-2! dark:bg-neutral-900!"
                    :placeholder="'Select Default Folder'"
                    :default-item="folders.findIndex((folder) => folder.id == defaultFolder?.id) ?? 0"
                    :disabled="processing || !folders.length"
                    :title="'Select Default Folder'"
                    :menu-margin="{ top: 'mt-10', bottom: 'mb-10' }"
                    @selectItem="handleSetDefaultFolder"
                    :options="
                        folders.map((folder) => {
                            return { title: folder.title, value: folder.id };
                        })
                    "
                />
                <ButtonText
                    v-else
                    class="justify-normal dark:bg-neutral-900!"
                    id="default-folder"
                    @click="
                        toast.info(defaultFolder ? `The default folder is '${defaultFolder?.name}'` : 'No default folder assigned', {
                            description: `The selected folder will open automatically when you navigate to '/${data.name}'`,
                        })
                    "
                >
                    {{ defaultFolder?.name }}
                </ButtonText>
            </div>

            <ButtonText title="Scan for Changes" @click="handleStartScan(false)">
                <p class="flex-1 text-start">Scan Library</p>
                <template #icon> <ProiconsArrowSync class="size-4" /></template>
            </ButtonText>
            <ButtonText title="Verify File Metadata" @click="handleStartScan(true)">
                <p class="flex-1 text-start">Verify Library</p>
                <template #icon> <ProiconsArrowSync class="size-4" /></template>
            </ButtonText>

            <template v-if="isAdmin">
                <SectionLabel class="-mb-1 hidden h-auto! bg-transparent!"> Access Control </SectionLabel>
                <ButtonText
                    :title="'Toggle Storyboards'"
                    @click="handleToggleSetting('storyboard_enabled', data.storyboard_enabled, (v) => `${v ? 'Enabled' : 'Disabled'} Storyboard Generation`)"
                    :disabled="processing"
                >
                    <p class="flex-1 text-start">{{ data.storyboard_enabled ? 'Disable Storyboard' : 'Enable Storyboard' }}</p>
                    <template #icon> <ProIconsPhotoOff v-if="!data.storyboard_enabled" class="size-4" /> <ProIconsPhoto v-else class="size-4" /></template>
                </ButtonText>

                <ButtonText :title="'Generate Storyboards'" @click="handleStartGenerateStoryboards()" :disabled="processing" v-if="data.storyboard_enabled">
                    <p class="flex-1 text-start">Build Storyboards</p>
                    <template #icon> <ProIconsPhotoOff v-if="!data.storyboard_enabled" class="size-4" /> <ProIconsPhoto v-else class="size-4" /></template>
                </ButtonText>

                <ButtonText
                    :title="'Toggle Downloads'"
                    @click="handleToggleSetting('downloads_enabled', data.downloads_enabled, (v) => `${v ? 'Enabled' : 'Disabled'} Library Downloads`)"
                    :disabled="processing"
                >
                    <p class="flex-1 text-start">{{ data.downloads_enabled ? 'Disable Downloads' : 'Enable Downloads' }}</p>
                    <template #icon> <TablerDownloadOff v-if="!data.downloads_enabled" class="size-4" /> <TablerDownload v-else class="size-4" /></template>
                </ButtonText>

                <ButtonText
                    v-if="data.downloads_enabled"
                    :title="`${data.downloads_require_auth ? 'Enable' : 'Disable'} Guest Downloads`"
                    :disabled="processing"
                    @click="handleToggleSetting('downloads_require_auth', data.downloads_require_auth, (v) => `${v ? 'Disabled' : 'Enabled'} Guest Downloads`)"
                >
                    <p class="flex-1 text-start">{{ data.downloads_require_auth ? 'Guest Downloads' : 'Private Downloads' }}</p>
                    <template #icon> <TablerDownloadOff v-if="!data.downloads_require_auth" class="size-4" /> <TablerDownload v-else class="size-4" /></template>
                </ButtonText>

                <ButtonText
                    :title="'Toggle Privacy'"
                    @click="handleToggleSetting('is_private', data.is_private ?? false, (v) => `Library set to ${v ? 'Private' : 'Public'}`)"
                    :disabled="processing"
                    :class="[{ 'text-danger dark:text-foreground-0 dark:bg-danger-3! dark:hocus:bg-danger!': data.is_private }]"
                >
                    <p class="flex-1 text-start">{{ data.is_private ? 'Set to Public' : 'Set to Private' }}</p>
                    <template #icon> <ProiconsLock v-if="data.is_private" class="size-4" /> <ProiconsLockOpen v-else class="size-4" /></template>
                </ButtonText>
            </template>
            <ButtonText
                @click.stop.prevent="$emit('clickAction')"
                class="text-danger dark:text-foreground-0 dark:bg-danger-3! dark:hocus:bg-danger!"
                title="Remove From Server"
                disabled
                v-if="FLAGS.USE_REMOVABLE_LIBRARIES"
            >
                <p class="flex-1 text-start">Remove Library</p>
                <template #icon> <ProiconsDelete class="size-4" /></template>
            </ButtonText>
        </div>
    </div>
</template>
