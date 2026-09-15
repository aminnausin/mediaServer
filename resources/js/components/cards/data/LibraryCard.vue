<script setup lang="ts">
import type { CategoryResource, FolderResource } from '@/types/resources';

import { startGenerateStoryboardsTask, startScanFilesTask, startVerifyFilesTask } from '@/service/siteAPI';
import { updateLibrarySettings, updateLibraryDefaultFolder } from '@/service/mediaAPI';
import { formatFileSize, handleStorageURL, toFormattedDate } from '@/service/util';
import { computed, ref, useTemplateRef, watch } from 'vue';
import { useQueryClient } from '@tanstack/vue-query';
import { BasePopover } from '@/components/cedar-ui/popover';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { HoverCard } from '@/components/cedar-ui/hover-card';
import { cn, toast } from '@aminnausin/cedar-ui';

import LibraryCardMenu from '@/components/menus/LibraryCardMenu.vue';
import PlayerOSDBase from '@/components/video/OSD/PlayerOSDBase.vue';
import BlurhashImage from '@/components/lazy/BlurhashImage.vue';

import ProiconsTextFontSize from '~icons/proicons/text-font-size';
import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import TablerDownload from '@/components/icons/TablerDownload.vue';
import ProIconsPhoto from '@/components/icons/ProIconsPhoto.vue';
import ProiconsLock from '~icons/proicons/lock';
import IconFolder from '@/components/icons/IconFolder.vue';
import IconShare from '@/components/icons/IconShare.vue';
import IconFile from '@/components/icons/IconFile.vue';

const props = defineProps<{ data?: CategoryResource }>();
const defaultFolder = ref<FolderResource>();
const queryClient = useQueryClient();
const processing = ref(false);

const popover = useTemplateRef('popover');

const folders = computed(() => {
    const foldersCopy = [...(props.data?.folders || [])];
    return foldersCopy.sort((itemA, itemB) => itemA.name.localeCompare(itemB.name));
});

const formattedDate = computed(() => {
    if (!props.data?.created_at) return { short: 'N/A', long: 'N/A' };
    return {
        short: toFormattedDate(new Date(props.data.created_at + ' UTC'), false, { year: 'numeric', month: '2-digit', day: '2-digit' }),
        long: toFormattedDate(new Date(props.data.created_at + ' UTC')),
    };
});
const activeFeatureCount = computed(() => [props.data?.is_private, props.data?.downloads_enabled, props.data?.storyboard_enabled].filter(Boolean).length);

const handleSetDefaultFolder = async (newFolder: { value: number }) => {
    if (processing.value || !props.data?.id || newFolder.value === props.data.default_folder_id) return;

    try {
        processing.value = true;
        await updateLibraryDefaultFolder(props.data.id, { default_folder_id: newFolder.value });
        await queryClient.invalidateQueries({ queryKey: ['auth-only', 'categories'] });
        toast.success(`Default folder set to ${defaultFolder.value?.title}`);
    } catch (error) {
        toast('Unable to set Default Folder', { type: 'danger', description: `${error}` });
        console.error(error);
    } finally {
        processing.value = false;
    }
};

const handleStartScan = async (verifyOnly: boolean = false) => {
    if (!props.data?.id) {
        toast('Error', { description: 'Invalid Category ID!', type: 'danger' });
        popover.value?.handleClose();
        return;
    }

    try {
        if (verifyOnly) await startVerifyFilesTask(props.data.id);
        else await startScanFilesTask(props.data.id);
        toast.add('Success', { type: 'success', description: `Submitted ${verifyOnly ? 'verify' : 'scan'} request!` });
        popover.value?.handleClose();
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to submit ${verifyOnly ? 'verify' : 'scan'} request.` });
        console.error(error);
    }
};

const handleGenerateStoryboards = async () => {
    if (!props.data?.id) {
        toast('Error', { description: 'Invalid Category ID!', type: 'danger' });
        popover.value?.handleClose();
        return;
    }

    try {
        await startGenerateStoryboardsTask(props.data.id);
        toast.add('Success', { type: 'success', description: `Submitted generate storyboards request!` });
        popover.value?.handleClose();
    } catch (error) {
        toast('Failure', { type: 'danger', description: `Unable to generate storyboards.` });
        console.error(error);
    }
};

const handleToggleSetting = async (
    setting: keyof Pick<CategoryResource, 'is_private' | 'downloads_enabled' | 'downloads_require_auth' | 'storyboard_enabled' | 'fonts_enabled'>,
    currentValue: boolean,
    successMessage: (newValue: boolean) => string,
) => {
    if (processing.value || !props.data?.id || currentValue !== props.data[setting]) return;

    try {
        processing.value = true;
        await updateLibrarySettings(props.data.id, { [setting]: !currentValue });
        await queryClient.invalidateQueries({ queryKey: ['auth-only', 'categories'] });
        toast.success(successMessage(!currentValue));
    } catch (error) {
        toast('Failure', { type: 'danger', description: 'Unable to update library settings.' });
        console.error(error);
    } finally {
        processing.value = false;
    }
};

watch(
    () => props.data,
    () => {
        if (!props.data?.folders || props.data.folders.length < 1) return;
        defaultFolder.value = props.data.default_folder_id ? props.data.folders.find((folder) => folder.id === props.data?.default_folder_id) : props.data.folders[0];
    },
    { immediate: true },
);
</script>

<template>
    <div :class="cn('data-card group transition-input library-card')">
        <RouterLink
            :to="`/dashboard/libraries/${data?.id}`"
            class="peer content-auto h-40 w-full [contain-intrinsic-size:auto_160px] focus:-outline-offset-2"
            aria-label="View Folders"
        >
            <BlurhashImage
                class="peer mb-auto h-full w-full rounded-t-lg object-cover"
                alt="Folder Cover Art"
                :src="defaultFolder?.series?.poster_image?.path ?? handleStorageURL(defaultFolder?.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp'"
                :blurhash="defaultFolder?.series?.poster_image?.blur_hash"
            />
            <div v-if="activeFeatureCount" :class="cn('absolute inset-x-0 bottom-2 z-1 px-2.5 transition-[padding] duration-200', { 'px-2': activeFeatureCount > 1 })">
                <PlayerOSDBase
                    :class="
                        cn('ml-auto flex w-fit items-center justify-start gap-2 p-1 text-white backdrop-blur-sm transition-[padding] duration-200', {
                            'px-2': activeFeatureCount > 1,
                        })
                    "
                >
                    <HoverCard :content-title="'Auto Generates Fonts'" :content="'Fonts are auto extracted for every video in this library.'" v-if="data?.fonts_enabled">
                        <template #trigger>
                            <ProiconsTextFontSize class="peer size-4.5 shrink-0" :title="'Fonts enabled'" />
                        </template>
                    </HoverCard>
                    <HoverCard
                        :content-title="'Downloadable Library'"
                        :content="`${data?.downloads_require_auth ? 'Only authenticated users' : 'Any user'} can download from this library.`"
                        v-if="data?.downloads_enabled"
                    >
                        <template #trigger>
                            <TablerDownload class="peer size-4.5 shrink-0" :title="'Downloads enabled'" />
                        </template>
                    </HoverCard>
                    <HoverCard :content-title="'Private Library'" :content="'Only you have access to this library.'" v-if="data?.is_private">
                        <template #trigger>
                            <div class="flex w-fit items-center">
                                <ProiconsLock class="peer size-4.5 shrink-0" :title="'Private library'" />
                                <div
                                    :class="
                                        cn(
                                            'flex items-center overflow-clip drop-shadow-md duration-400',
                                            'max-w-0 origin-left transition-[max-width,padding,margin] ease-out',
                                            'peer-hover:-ms-1 peer-hover:max-w-32 peer-hover:ps-1.5 peer-hover:ease-in',
                                            'hover:-ms-1 hover:max-w-32 hover:ps-1.5 hover:ease-in',
                                        )
                                    "
                                    hidden
                                >
                                    <span class="w-full truncate"> Private </span>
                                </div>
                            </div>
                        </template>
                    </HoverCard>
                    <HoverCard
                        :content-title="'Auto Generates Storyboards'"
                        :content="'Storyboards are auto generated for every video in this library.'"
                        v-if="data?.storyboard_enabled"
                    >
                        <template #trigger>
                            <ProIconsPhoto class="peer size-4.5 shrink-0" :title="'Storyboards enabled'" />
                        </template>
                    </HoverCard>
                </PlayerOSDBase>
            </div>
        </RouterLink>
        <section class="flex h-full flex-1 flex-col gap-2 p-3">
            <div class="flex items-start gap-1.5">
                <RouterLink title="Open Default Folder" class="group-hover:text-primary dark:group-hover:text-primary-muted min-w-0 flex-1" :to="`/${data?.name}`">
                    <h3 class="truncate capitalize">{{ data?.name }}</h3>
                </RouterLink>
                <span class="flex shrink-0 gap-1.5 *:h-6">
                    <ButtonIcon :title="'Open Default Folder In New Tab'" :to="`/${data?.name}`" :target="'_blank'" class="size-6 p-0">
                        <template #icon><IconShare class="size-4" /></template>
                    </ButtonIcon>
                    <BasePopover popoverClass="max-w-50 lg:max-w-56 rounded-lg" :buttonClass="'p-1'" ref="popover">
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="size-4" />
                        </template>
                        <template #content>
                            <LibraryCardMenu
                                :data="data"
                                :folders="folders"
                                :default-folder="defaultFolder"
                                :processing="processing"
                                :handle-set-default-folder="handleSetDefaultFolder"
                                :handle-start-scan="handleStartScan"
                                :handle-toggle-setting="handleToggleSetting"
                                :handle-start-generate-storyboards="handleGenerateStoryboards"
                            />
                        </template>
                    </BasePopover>
                </span>
            </div>
            <div class="text-foreground-1 mt-1 flex h-full w-full flex-col gap-2 text-xs" v-if="data">
                <div class="flex flex-wrap items-start justify-between gap-3 gap-y-1">
                    <span class="flex gap-2">
                        <span class="flex items-center gap-1">
                            <IconFile class="size-3.5" />
                            {{ data?.videos_count ?? '?' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <IconFolder class="size-3.5" />
                            {{ data?.folders_count }}
                        </span>
                    </span>
                    <span class="shrink-0" :title="`Total Size ${formatFileSize(data.total_size)}`">{{ formatFileSize(data.total_size) }}</span>
                </div>

                <div class="bg-hr dark:bg-hr/30 -mx-3 mt-1 h-px shrink-0"></div>

                <div class="xms:flex-nowrap flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                    <p v-if="defaultFolder" class="space-x-1 truncate">
                        <span class="text-foreground-2 shrink-0">Default:</span>
                        <RouterLink class="hover:text-primary dark:hover:text-primary-muted" title="Open Default Folder" :to="`/${data.name}/${defaultFolder.name}`">
                            {{ defaultFolder.name }}
                        </RouterLink>
                    </p>
                    <p v-else class="text-foreground-2 flex-1">No Default Folder</p>

                    <p class="text-nowrap" :title="`Date Added ${formattedDate.long}`">{{ formattedDate.short }}</p>
                </div>
            </div>
        </section>
    </div>
</template>
