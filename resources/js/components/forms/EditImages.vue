<script setup lang="ts" generic="T extends ImageType">
import type { ImageFormState, ImageUpdateRequest } from '@/types/imageRequests';
import type { AxiosError, AxiosResponse } from 'axios';
import type { ImageResource, ImageType } from '@/types/resources';

import { computed, nextTick, onMounted, onUnmounted, reactive, ref, useTemplateRef, watch } from 'vue';
import { ButtonForm, ButtonText } from '@/components/cedar-ui/button';
import { InputShell, TextInput } from '@/components/cedar-ui/input';
import { useImageManager } from '@/composables/editor/useImageManager';
import { FormErrorList } from '@/components/cedar-ui/form';
import { cn, toast } from '@aminnausin/cedar-ui';
import { toPlural } from '@/service/util';

import ProIconsPhotoOff from '@/components/icons/ProIconsPhotoOff.vue';
import TablerUpload from '@/components/icons/TablerUpload.vue';
import ImageCard from '@/components/cards/data/ImageCard.vue';
import useForm from '@/composables/useForm';

const { pending, addFile, addUrl, removePending, cleanup } = useImageManager();

const props = defineProps<{
    images: ImageResource[];
    filters?: T[];
    primaryIds?: Partial<Record<T, number>>;
    isAudio?: boolean;
    readOnlyTypes?: T[];
    submitFn: (data: FormData) => Promise<AxiosResponse<any>>;
}>();

const emit = defineEmits(['handleFinish']);

const mobileUrlInput = useTemplateRef('mobileUrlInput');
const urlInput = ref('');

const activeFilters = computed<ImageType[]>(() => props.filters ?? ['poster', 'preview']);
const filteredImages = computed(() => props.images.filter((i) => i.type === filteredType.value && !i.replaced_at && !deletedImageIds.has(i.id)));
const replacedImages = computed(() => props.images.filter((i) => i.type === filteredType.value && i.replaced_at));
const deletedImages = computed(() => props.images.filter((i) => deletedImageIds.has(i.id)));
const pendingImage = computed(() => pending.value[filteredType.value]);

const filteredImageCount = computed(() => filteredImages.value.length + (pendingImage.value ? 1 : 0));

const deletedImageIds = reactive(new Set<number>());
const filteredType = ref<ImageType>(activeFilters.value[0]);

const isShowingReplaced = ref(false);
const isShowingDeleted = ref(true);
const isMobile = ref(false);
const isReadOnly = computed(() => props.readOnlyTypes?.includes(filteredType.value as T));
const isDirty = computed(() => form.dirty || deletedImageIds.size > 0);

const form = useForm<ImageFormState<T>>({
    type: activeFilters.value[0] as T,
    mode: filteredPrimaryId(activeFilters.value[0]) ? 'existing' : 'remove',
    image_id: filteredPrimaryId(activeFilters.value[0]),
    file: null,
    url: null,
});

const resetForm = () => {
    form.reset();
    deletedImageIds.clear();
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

            deletedImageIds.forEach((id) => formData.append('deleted_images[]', String(id)));

            return props.submitFn(formData);
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add('Success', { type: 'success', description: 'Edit submitted!' });
            },
            onError: (err: AxiosError) => {
                const msg = (err.response?.data as { message?: string })?.message ?? 'Unable to update media images.';
                toast.add(msg ? 'Unable to update media images.' : 'Error', { type: 'danger', description: msg });
            },
        },
    );
};

function buildRequest(fields: ImageFormState<T>): ImageUpdateRequest<T> {
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

function handleUploadFile(e: Event) {
    handlePendingFile((e.target as HTMLInputElement).files?.[0]);
}

function handleDropFile(e: DragEvent) {
    e.preventDefault();
    handlePendingFile(e.dataTransfer?.files[0]);
}

function handlePaste(e: ClipboardEvent) {
    if (isReadOnly.value) return;

    if (e.clipboardData?.files.item(0)) {
        e.preventDefault();
        handlePendingFile(e.clipboardData.files.item(0));
        return;
    }

    if (!isMobile.value) return;

    urlInput.value =
        e.clipboardData
            ?.getData('text/plain')
            ?.replace(/[\r\n]+/g, ' ')
            .trim() ?? urlInput.value;

    nextTick(() => placeCursorAtEnd());
}

function handlePendingFile(file?: File | null) {
    if (!file?.type.startsWith('image/')) return;

    form.fields.image_id = addFile(file, filteredType.value).tempId;
    form.fields.mode = 'upload';
    form.fields.file = file;
    form.clearErrors();
}

function handleUrlFetch() {
    try {
        if (!urlInput.value.trim()) return;

        const url = new URL(urlInput.value.trim());

        if (!['http:', 'https:'].includes(url.protocol)) {
            throw new Error();
        }

        form.fields.image_id = addUrl(url.toString(), filteredType.value).tempId;
        form.fields.url = url.toString();
        form.fields.mode = 'url';
        form.clearErrors();
        urlInput.value = '';
    } catch (error) {
        toast.error('Error', { description: 'Invalid URL' });
        urlInput.value = '';
    }
}

function handleSelect(id: number | string | null) {
    form.fields.image_id = id;
    form.clearErrors();

    if (id === null) {
        form.fields.mode = 'remove';
        return;
    }

    form.fields.mode = typeof id === 'string' ? form.fields.mode : 'existing';
}

function handleDeleteExisting(id: number) {
    if (form.fields.image_id === id) handleSelect(null);
    deletedImageIds.add(id);
}

function handleRestorePending(id: number) {
    deletedImageIds.delete(id);
}

/**
 * Gets the primary id corresponding to a given type from the provided metadata
 * @param type ImageType -> Only supports `poster` for `metadata`
 */
function filteredPrimaryId(type: ImageType): number | null {
    return props.primaryIds?.[type as T] ?? null;
}

//#region Mobile URL Input

function handleMobileInput() {
    if (!mobileUrlInput.value) return;
    urlInput.value = mobileUrlInput.value.textContent ?? '';
}

function handleBlur() {
    if (!mobileUrlInput.value) return;
    mobileUrlInput.value.scrollLeft = 0;
}

function placeCursorAtEnd() {
    if (!mobileUrlInput.value) return;

    const range = document.createRange();
    const selection = window.getSelection();

    range.selectNodeContents(mobileUrlInput.value);
    range.collapse(false);

    selection?.removeAllRanges();
    selection?.addRange(range);
}

watch(urlInput, (value) => {
    if (!mobileUrlInput.value || mobileUrlInput.value.textContent === value) return;

    mobileUrlInput.value.textContent = value;

    nextTick(() => {
        placeCursorAtEnd();
    });
});

//#endregion

// Resets to the default primary id when type is changed
// persists dirty between tabs
watch(filteredType, (type) => {
    resetForm();
    const defaultId = filteredPrimaryId(type);
    form.init({
        type: type as T,
        mode: defaultId ? 'existing' : 'remove',
        image_id: defaultId,
        url: null,
        file: null,
    });
});

onMounted(() => {
    document.addEventListener('paste', handlePaste);
    isMobile.value = window.matchMedia('(pointer: coarse)').matches;
});

onUnmounted(() => {
    document.removeEventListener('paste', handlePaste);
    cleanup();
});
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <ButtonText
            v-for="filter in filters"
            :key="filter"
            :class="cn('hocus:ring-1 h-8 rounded-lg px-3 py-0.5 text-sm dark:bg-white/5', { 'bg-surface-i! text-foreground-i!': filter === filteredType })"
            @click="filteredType = filter"
        >
            {{ filter }}
        </ButtonText>
    </div>
    <form class="flex flex-col flex-wrap gap-4 text-sm sm:flex-row sm:justify-between" @submit.prevent="handleSubmit">
        <div
            v-if="!isReadOnly"
            class="group hover:border-primary-muted/60 hover:bg-primary-muted/5 border-foreground-0/15 text-foreground-2 relative w-full rounded-xl border-2 border-dashed p-3 text-center transition"
        >
            <input type="file" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" @input="handleUploadFile" @drop="handleDropFile" />

            <TablerUpload class="group-hover:text-foreground-1 dark:text-foreground-3 dark:group-hover:text-foreground-1 mx-auto mb-2 size-6 transition" />

            <p class="text-foreground-1 dark:text-foreground-0 font-medium">{{ isMobile ? 'Tap' : 'Paste, drag, or click' }} to upload</p>
            <p>jpg, jpeg, png, webp · max 10 MB</p>

            <div class="relative z-1 mt-3 flex flex-wrap gap-2" @click.stop>
                <InputShell v-if="isMobile" :clamp-text="false">
                    <template #input="{ class: inputClass }">
                        <div
                            :class="
                                cn(
                                    inputClass,
                                    'h-full px-3 py-2 text-left',
                                    'hocus:ring-1 focus-within:ring-primary-muted/60! focus-within:placeholder:text-foreground-2 text-foreground-0 focus-within:ring-1 dark:bg-white/6 dark:ring-white/10',
                                    'inline-block h-9 flex-1 cursor-text overflow-hidden whitespace-nowrap',
                                )
                            "
                            @click="mobileUrlInput?.focus()"
                        >
                            <div
                                ref="mobileUrlInput"
                                contenteditable="plaintext-only"
                                role="textbox"
                                spellcheck="false"
                                aria-placeholder="Or enter a URL to download…"
                                :class="cn({ empty: !urlInput }, 'scrollbar-hide flex-1 overflow-hidden focus:overflow-x-auto focus:outline-none')"
                                @keydown.enter.prevent
                                @paste.prevent="handlePaste"
                                @input.prevent="handleMobileInput"
                                @blur="handleBlur"
                            ></div>
                        </div>
                    </template>
                </InputShell>

                <TextInput
                    v-else
                    type="url"
                    v-model="urlInput"
                    placeholder="Or enter a URL to download…"
                    class="hocus:ring-1 focus:ring-primary-muted/60! focus:placeholder:text-foreground-2 text-foreground-0 h-full flex-1 dark:bg-white/6 dark:ring-white/10 dark:not-focus:placeholder:text-white/30"
                />

                <ButtonText
                    type="button"
                    :class="
                        cn('hocus:ring-1 h-full px-3 dark:bg-white/6 dark:not-focus:not-hover:ring-white/10 dark:hover:bg-white/10', {
                            'text-foreground-0': urlInput.length > 0,
                        })
                    "
                    :disabled="!urlInput"
                    @click="handleUrlFetch"
                >
                    Fetch
                </ButtonText>
            </div>
        </div>

        <div class="w-full space-y-2" v-if="filteredImageCount > 0">
            <div class="text-foreground-2 flex w-full items-center justify-between gap-2 text-xs">
                <p class="uppercase">current</p>
                <p>{{ filteredImageCount }} image{{ toPlural(filteredImageCount) }}</p>
            </div>
            <div :class="['grid w-full grid-cols-1 gap-4', { 'xsm:grid-cols-2 gap-2': filteredImageCount > 1 }]">
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
                        handleSelect(null);
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
                    @delete="handleDeleteExisting(image.id)"
                />
            </div>
        </div>

        <div v-else class="text-foreground-1 flex w-full items-center justify-center gap-1 py-8 tracking-widest">
            <ProIconsPhotoOff class="size-6" />
            <span> No images available </span>
        </div>

        <template v-if="deletedImages.length > 0">
            <div class="text-foreground-2 flex w-full items-center justify-between gap-2 text-xs">
                <ButtonText
                    :variant="'ghost'"
                    type="button"
                    class="hover:text-foreground-0 duration-input h-fit gap-1 bg-transparent! p-0 uppercase transition-colors"
                    @click="isShowingDeleted = !isShowingDeleted"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" :class="'size-3 shrink-0'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ isShowingDeleted ? 'Hide' : 'Show' }} delete pending
                </ButtonText>
                <p>{{ deletedImages.length }} image{{ toPlural(deletedImages.length) }}</p>
            </div>

            <div :class="cn('xsm:grid-cols-2 grid max-h-0 w-full gap-2 overflow-hidden', { 'max-h-128': isShowingDeleted })" v-if="isShowingDeleted">
                <ImageCard v-for="image in deletedImages" :key="image.id" :data="image" :is-audio="isAudio" :is-pending-delete="true" @restore="handleRestorePending(image.id)" />
            </div>
        </template>

        <template v-if="replacedImages.length > 0">
            <div class="text-foreground-2 flex w-full items-center justify-between gap-2 text-xs">
                <ButtonText
                    :variant="'ghost'"
                    type="button"
                    class="hover:text-foreground-0 duration-input h-fit gap-1 bg-transparent! p-0 uppercase transition-colors"
                    @click="isShowingReplaced = !isShowingReplaced"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" :class="'size-3 shrink-0'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ isShowingReplaced ? 'Hide' : 'Show' }} deleted
                </ButtonText>
                <p>{{ replacedImages.length }} image{{ toPlural(replacedImages.length) }}</p>
            </div>

            <div :class="cn('xsm:grid-cols-2 grid max-h-0 w-full gap-2 overflow-hidden', { 'max-h-none': isShowingReplaced })" v-if="isShowingReplaced">
                <ImageCard v-for="image in replacedImages" :key="image.id" :data="image" :is-audio="isAudio" />
            </div>
        </template>

        <div :class="['text-danger w-full text-center']" v-if="isDirty && !isReadOnly">
            <p v-if="form.fields.image_id === null && filteredPrimaryId(filteredType as T) !== null">Removing {{ filteredType }} image!</p>
            <p v-else-if="form.fields.image_id !== filteredPrimaryId(filteredType as T)">Replacing {{ filteredType }} image!</p>
            <p v-if="deletedImageIds.size">Deleting {{ deletedImageIds.size }} image{{ deletedImageIds.size > 1 ? 's' : '' }} from disk!</p>
        </div>

        <FormErrorList class="w-full text-center" v-if="form.errors" :errors="form.errors" />

        <div class="relative flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end" v-if="!isReadOnly">
            <ButtonForm variant="reset" class="h-9" :disabled="form.processing" @click="$emit('handleFinish')"> Cancel </ButtonForm>
            <ButtonForm
                variant="danger"
                :class="
                    cn('transition-reveal h-9 overflow-hidden', {
                        'h-9 py-2 opacity-100 sm:mx-0 sm:w-18': isDirty,
                        '-mt-2 h-0 w-full py-0 opacity-0 sm:-mx-1 sm:mt-0 sm:h-9 sm:w-0 sm:px-0 sm:py-2': !isDirty,
                    })
                "
                :disabled="form.processing"
                @click="resetForm"
            >
                Reset
            </ButtonForm>
            <ButtonForm variant="submit" class="h-9" :disabled="form.processing" @click="handleSubmit"> Save </ButtonForm>
        </div>
    </form>
</template>

<style lang="css" scoped>
.empty::before {
    content: attr(aria-placeholder);
    opacity: 0.3;
    pointer-events: none;
    position: absolute;
}
</style>
