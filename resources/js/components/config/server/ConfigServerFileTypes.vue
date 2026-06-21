<script setup lang="ts">
import type { MediaConfigRequest } from '@/contracts/server';

import { COMMON_AUDIO_EXTENSIONS, COMMON_EXTENSIONS, COMMON_SUBTITLE_EXTENSIONS, COMMON_VIDEO_EXTENSIONS } from '@/constants/config/mediaExtensions';
import { ButtonBase, ButtonCorner, ButtonForm, ButtonText } from '@/components/cedar-ui/button';
import { UseUpdateMediaConfig } from '@/service/server/mutations';
import { useGetConfig } from '@/service/server/queries';
import { ref, watch } from 'vue';
import { FormInput } from '@/components/cedar-ui/form';
import { cn, toast } from '@aminnausin/cedar-ui';
import { FLAGS } from '@/config/featureFlags';

import SettingsCard from '@/components/cards/layout/SettingsCard.vue';
import ConfigHeader from '@/components/config/ConfigHeader.vue';
import FormError from '@/components/cedar-ui/form/FormError.vue';
import useForm from '@/composables/useForm';

const { data: serverConfig, isLoading } = useGetConfig();
const saveConfig = UseUpdateMediaConfig();

const SECTIONS: { name: 'video' | 'audio' | 'subtitles'; extensions: string[] }[] = [
    { name: 'video', extensions: COMMON_VIDEO_EXTENSIONS },
    { name: 'audio', extensions: COMMON_AUDIO_EXTENSIONS },
    { name: 'subtitles', extensions: COMMON_SUBTITLE_EXTENSIONS },
];

const selectedExtensions = ref<Set<string>>(new Set());

const customExtensions = ref<string[]>([]);
const customExtensionError = ref<string>('');
const customExtensionInput = ref('');

const form = useForm<MediaConfigRequest>({
    supported_extensions: [...selectedExtensions.value],
});

function toggleExtension(ext: string) {
    if (selectedExtensions.value.has(ext)) selectedExtensions.value.delete(ext);
    else selectedExtensions.value.add(ext);

    updateForm();
}

function addCustomExtension() {
    const raw = customExtensionInput.value.trim().toLowerCase().replace(/^\./, '');
    if (!/^(?=.*[a-z])[a-z0-9]{2,5}$/.test(raw)) {
        customExtensionError.value = 'Invalid extension';
        return;
    }
    if (selectedExtensions.value.has(raw)) {
        customExtensionError.value = 'This extension already exists';
        return;
    }
    customExtensionError.value = '';
    customExtensionInput.value = '';
    selectedExtensions.value.add(raw);

    if (!COMMON_EXTENSIONS.has(raw)) customExtensions.value.push(raw);

    document.getElementById('custom-extension')?.focus();
    updateForm();
}

function removeCustomExtension(ext: string) {
    customExtensions.value = customExtensions.value.filter((e) => e !== ext);
    selectedExtensions.value.delete(ext);
    updateForm();
}

function updateForm() {
    form.fields.supported_extensions = [...selectedExtensions.value];
}

const handleSaveFileTypes = () => {
    form.submit((fields) => saveConfig.mutateAsync(fields), {
        onSuccess: () => toast.success('File type settings saved.'),
        onError: () => toast.error('Failed to save file type settings.'),
    });
};

const setSavedExtensions = () => {
    if (!serverConfig.value) return;

    const currentExtensions = serverConfig.value.values.media.supported_extensions;

    if (currentExtensions.length === 0) {
        setDefaultExtensions();
        return;
    }

    selectedExtensions.value = new Set(currentExtensions);
    customExtensions.value = currentExtensions.filter((ext) => !COMMON_EXTENSIONS.has(ext));

    form.init({ supported_extensions: currentExtensions });
};

const setDefaultExtensions = () => {
    const values = serverConfig.value?.defaults.media.supported_extensions ?? [];
    selectedExtensions.value = new Set(values);

    if (!isLoading.value && values.length === 0) {
        toast.warning('No default values found.');
    }

    updateForm();
};

watch(serverConfig, setSavedExtensions, { immediate: true });
</script>

<template>
    <SettingsCard :class="['flex-col gap-6 truncate', { 'animate-pulse': isLoading }]">
        <template #content>
            <ConfigHeader :heading="'Supported File Types'" :dirty="form.dirty"> Only selected file types will be scanned </ConfigHeader>

            <div class="flex w-full flex-col gap-4 font-medium">
                <div
                    class="flex flex-col gap-2"
                    v-for="section in SECTIONS.filter((sec) => {
                        if (sec.name === 'subtitles' && !FLAGS.CONFIG.USE_SUBTITLE_EXTENSION_CONFIG) return false;
                        return true;
                    })"
                    :key="section.name"
                >
                    <p class="text-foreground-1 text-xs tracking-widest uppercase">{{ section.name }}</p>
                    <div class="flex flex-wrap gap-2 gap-x-1">
                        <ButtonBase
                            v-for="ext in section.extensions"
                            :key="ext"
                            type="button"
                            @click="toggleExtension(ext)"
                            :class="
                                cn(
                                    'bg-overlay-t h-7 rounded-md px-2.5 shadow-sm dark:shadow-none',
                                    selectedExtensions.has(ext)
                                        ? 'bg-primary text-foreground-i dark:text-foreground-0 hover:bg-primary-active'
                                        : 'text-foreground-2 hover:text-foreground-0 hover:bg-surface-2 ring-1 ring-transparent ring-inset hover:ring-current',
                                )
                            "
                        >
                            .{{ ext }}
                        </ButtonBase>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <p class="text-foreground-1 text-xs tracking-widest uppercase">Custom</p>
                    <div class="flex flex-wrap gap-2" v-if="customExtensions.length > 0">
                        <span
                            v-for="ext in customExtensions"
                            :key="ext"
                            class="bg-overlay-t text-foreground-1 hover:bg-surface-2 flex h-7 items-center gap-1 rounded-md px-2.5 shadow-sm dark:shadow-none"
                        >
                            .{{ ext }}
                            <ButtonCorner
                                :title="'Remove custom extension'"
                                :position-classes="''"
                                :class="'text-danger mt-0.5 transition-colors *:size-2.5 hover:bg-transparent'"
                                @click="removeCustomExtension(ext)"
                            />
                        </span>
                    </div>
                    <form class="mt-2 flex flex-wrap items-center gap-1 *:h-8" @submit.prevent="addCustomExtension()">
                        <FormInput
                            v-model="customExtensionInput"
                            :field="{ name: 'custom-extension', type: 'text', placeholder: 'e.g. .mpv' }"
                            class="mt-0 w-full max-w-32 min-w-0"
                            required
                        />
                        <ButtonText type="submit" class="text-xs" :class="'px-3'"> Add </ButtonText>
                    </form>
                    <FormError v-if="customExtensionError">{{ customExtensionError }}</FormError>
                </div>
            </div>

            <div class="xs:justify-end flex flex-wrap gap-1 *:h-8">
                <ButtonForm variant="reset" :disabled="form.processing" @click="setDefaultExtensions"> Load defaults </ButtonForm>
                <ButtonForm
                    variant="reset"
                    :disabled="form.processing || !form.dirty"
                    :class="cn('transition-reveal overflow-hidden', form.dirty ? 'mx-0 w-18 px-4 opacity-100' : '-mx-0.5 w-0 border-0 px-0 opacity-0')"
                    @click="setSavedExtensions"
                >
                    Reset
                </ButtonForm>
                <ButtonForm variant="submit" :disabled="form.processing" @click="handleSaveFileTypes"> Save </ButtonForm>
            </div>
        </template>
    </SettingsCard>
</template>
