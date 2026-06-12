<script setup lang="ts">
import type { ImageResource, ImageType, MetadataResource } from '@/types/resources';
import type { MediaImageFormState, MediaImageType, MediaImageUpdateRequest } from '@/types/imageRequests';

import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { ButtonForm, ButtonText } from '@/components/cedar-ui/button';
import { updateMediaImage } from '@/service/mediaAPI.ts';
import { useImageManager } from '@/composables/editor/useImageManager';
import { TextInput } from '@/components/cedar-ui/input';
import { MediaType } from '@/types/types';
import { cn, toast } from '@aminnausin/cedar-ui';

import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';
import TablerUpload from '@/components/icons/TablerUpload.vue';
import ImageCard from '@/components/cards/data/ImageCard.vue';
import useForm from '@/composables/useForm';

const filters: ImageType[] = ['poster', 'preview'];

const { pending, addFile, addUrl, removePending, cleanup } = useImageManager();

const props = defineProps<{ media: MetadataResource; images: ImageResource[] }>();
const emit = defineEmits(['handleFinish']);

const filteredType = ref<ImageType>('poster');
const urlInput = ref('');

const deletedImages = ref<Set<number>>(new Set());

const filteredImages = computed(() => props.images.filter((i) => i.type === filteredType.value && !deletedImages.value.has(i.id)));
const pendingImage = computed(() => pending.value[filteredType.value]);
const isAudio = computed(() => props.media.media_type === MediaType.AUDIO);

const form = useForm<MediaImageUpdateRequest>({
    type: 'poster',
    mode: 'existing',
    image_id: props.media.poster_image?.id ?? null,
});

const resetForm = () => {
    form.reset('type', 'mode', 'image_id');
    deletedImages.value.clear();
    cleanup();
};

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            const request = buildRequest(fields);
            const formData = new FormData();

            formData.append('type', fields.type);
            formData.append('mode', fields.mode);

            if (request.mode === 'existing') formData.append('image_id', String(request.image_id));
            if (request.mode === 'upload') formData.append('image', request.file);
            if (request.mode === 'url') formData.append('url', request.url);
            return updateMediaImage(props.media.id, formData);
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add('Success', { type: 'success', description: 'Edit submitted!' });
            },
            onError: () => {
                toast.add('Error', { type: 'danger', description: 'Unable to update media images.' });
            },
        },
    );
};

function buildRequest(fields: MediaImageFormState): MediaImageUpdateRequest {
    switch (fields.mode) {
        case 'existing':
            return { mode: 'existing', type: fields.type, image_id: fields.image_id as number };
        case 'upload':
            return { mode: 'upload', type: fields.type, file: fields.file as File };
        case 'url':
            return { mode: 'url', type: fields.type, url: fields.url as string };
        case 'remove':
            return { mode: 'remove', type: fields.type };
    }
}

function handleFileInput(e: Event) {
    handleUploadFile((e.target as HTMLInputElement).files?.[0]);
}

function handleDrop(e: DragEvent) {
    e.preventDefault();
    handleUploadFile(e.dataTransfer?.files[0]);
}

function handlePaste(e: ClipboardEvent) {
    if (filteredType.value === 'preview') return;

    const target = e.target as HTMLElement;

    if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') return;

    e.preventDefault();
    handleUploadFile(e.clipboardData?.files.item(0));
}

function handleUploadFile(file?: File | null) {
    if (!file?.type.startsWith('image/')) return;

    form.fields.image_id = addFile(file, filteredType.value).tempId;
    form.fields.mode = 'upload';
    form.fields.file = file;
}

function handleUrlFetch() {
    const url = urlInput.value.trim();
    if (!url) return;

    form.fields.image_id = addUrl(url, filteredType.value).tempId;
    form.fields.url = url;
    form.fields.mode = 'url';
    urlInput.value = '';
}

function handleSelect(id: number | string | null) {
    form.fields.image_id = id;
    if (id === null) form.fields.mode = 'remove';
    else if (typeof id === 'number') form.fields.mode = 'existing';
    form.fields.mode = typeof id === 'string' ? form.fields.mode : 'existing';
}

function handleDelete(id: number) {
    // unimplemented
    // deletedImages.value.add(id);
    // if (filteredPrimaryId(filteredType.value) === id) handleSelect(null);
}

/**
 * Gets the primary id corresponding to a given type from the provided metadata
 * @param type ImageType -> Only supports `poster` for `metadata`
 */
const filteredPrimaryId = (type: ImageType): number | null => {
    switch (type) {
        case 'poster':
            return props.media.poster_image?.id ?? null;
        default:
            return null;
    }
};

// Resets to the default primary id when type is changed
// persists dirty between tabs
watch(filteredType, (type) => {
    form.fields.type = type as MediaImageType;
    form.fields.mode = 'existing';
    form.fields.image_id = filteredPrimaryId(type);
});

onMounted(() => document.addEventListener('paste', handlePaste));
onUnmounted(() => {
    document.removeEventListener('paste', handlePaste);
    cleanup();
});
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <ButtonText
            class="h-8 rounded-lg px-3 py-0.5 text-sm"
            v-for="filter in filters"
            :key="filter"
            @click="filteredType = filter"
            :class="cn('hocus:ring-1 dark:bg-white/5', { 'bg-surface-i! text-foreground-i!': filter === filteredType })"
        >
            {{ filter }}
        </ButtonText>
    </div>
    <form class="flex flex-col flex-wrap gap-4 text-sm sm:flex-row sm:justify-between" @submit.prevent="handleSubmit">
        <div
            class="group hover:border-primary-muted/60 hover:bg-primary-muted/5 border-foreground-0/15 text-foreground-2 relative w-full rounded-xl border-2 border-dashed p-3 text-center transition"
            v-if="filteredType !== 'preview'"
        >
            <input type="file" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" @input="handleFileInput" @drop="handleDrop" />

            <TablerUpload class="group-hover:text-foreground-1 dark:text-foreground-3 dark:group-hover:text-foreground-1 mx-auto mb-2 size-6 transition" />

            <p class="text-foreground-1 dark:text-foreground-0 font-medium">Paste, drag, or click to upload</p>
            <p>jpg, jpeg, png, webp · max 10 MB</p>

            <div class="relative z-1 mt-3 flex gap-2" onclick="event.stopPropagation()">
                <TextInput
                    type="url"
                    v-model="urlInput"
                    placeholder="Or enter a URL to download…"
                    :class="'hocus:ring-1 focus:ring-primary-muted/60! focus:placeholder:text-foreground-2 text-foreground-0 h-full dark:bg-white/6 dark:ring-white/10 dark:not-focus:placeholder:text-white/30'"
                />

                <ButtonText
                    :type="'button'"
                    :class="
                        cn('hocus:ring-1 h-full px-3 dark:bg-white/6 dark:not-focus:not-hover:ring-white/10 dark:hover:bg-white/10', {
                            'text-foreground-0': urlInput.length > 0,
                        })
                    "
                    @click="handleUrlFetch"
                    :disabled="!urlInput"
                >
                    Fetch
                </ButtonText>
            </div>
        </div>

        <div class="w-full space-y-2" v-if="filteredImages.length + (pendingImage ? 1 : 0) !== 0">
            <div class="text-foreground-2 flex w-full items-center justify-between gap-2 text-xs">
                <p class="uppercase">current</p>
                <p>{{ filteredImages.length + (pendingImage ? 1 : 0) }} images</p>
            </div>
            <div :class="['grid w-full grid-cols-1 gap-2', { 'sm:grid-cols-2': filteredImages.length + (pendingImage ? 1 : 0) > 1 }]">
                <ImageCard
                    v-if="pendingImage"
                    :data="{
                        id: -1,
                        type: pendingImage.type,
                        path: pendingImage.previewUrl,
                        source: pendingImage.sourceUrl ? 'url' : 'uploaded',
                    }"
                    :is-primary="form.fields.image_id === pendingImage.tempId"
                    :is-audio="isAudio"
                    @select="handleSelect(pendingImage.tempId)"
                    @deselect="handleSelect(null)"
                    @delete="
                        handleSelect(media.poster_image?.id ?? null);
                        removePending(pendingImage.type);
                    "
                />

                <ImageCard
                    v-for="image in filteredImages"
                    :key="image.id"
                    :data="image"
                    :is-primary="image.id == form.fields.image_id"
                    :is-audio="isAudio"
                    @select="handleSelect(image.id)"
                    @deselect="handleSelect(null)"
                    @delete="handleDelete(image.id)"
                />
            </div>
        </div>
        <div v-else class="text-foreground-1 flex w-full items-center justify-center gap-1 py-8 tracking-widest">
            <ProIconsPhotoOff class="size-6" /> <span> No images available </span>
        </div>

        <div :class="['text-danger-2 w-full text-center dark:text-rose-400']" v-if="form.dirty && filteredType !== 'preview'">
            <p v-if="form.fields.image_id === null">Deleting Poster Image!</p>
            <p v-else-if="form.fields.image_id !== media.poster_image?.id">Replacing Poster Image!</p>
            <p v-if="deletedImages.size">Deleting {{ deletedImages.size }} images from disk!</p>
        </div>
        <div class="relative mt-2 flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end" v-if="filteredType !== 'preview'">
            <ButtonForm @click="$emit('handleFinish')" variant="reset" :disabled="false"> Cancel </ButtonForm>
            <ButtonForm
                variant="danger"
                :class="
                    cn('transition-reveal h-9 overflow-hidden', {
                        'h-9 py-2 opacity-100': form.dirty,
                        '-mt-2 h-0 py-0 opacity-0 sm:mt-0 sm:h-9 sm:py-2': !form.dirty,
                        'sm:mx-0 sm:w-18': form.dirty,
                        'w-full sm:-mx-1 sm:w-0 sm:px-0': !form.dirty,
                    })
                "
                :disabled="false"
                @click="resetForm"
            >
                Reset
            </ButtonForm>
            <ButtonForm @click="handleSubmit" variant="submit" :disabled="false" class="h-9"> Save </ButtonForm>
        </div>
    </form>
</template>
