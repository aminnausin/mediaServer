<script setup lang="ts">
import type { LyricsUpdateRequest } from '@/types/requests';
import type { VideoResource } from '@/types/resources';
import type { FormField } from '@/types/types';

import { generateLyricsSearchQuery, updateLyrics } from '@/service/lyricsService';
import { onMounted, reactive, watch } from 'vue';
import { toFormattedDuration } from '@/service/util';
import { useLyricStore } from '@/stores/LyricStore';
import { storeToRefs } from 'pinia';
import { toast } from '@/service/toaster/toastService';

import FormInputNumber from '@/components/inputs/FormInputNumber.vue';
import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import FormErrorList from '@/components/labels/FormErrorList.vue';
import FormTextArea from '@/components/inputs/FormTextArea.vue';
import ButtonForm from '@/components/inputs/ButtonForm.vue';
import LrcLibCard from '@/components/cards/LrcLibCard.vue';
import DatePicker from '@/components/pinesUI/DatePicker.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import useForm from '@/composables/useForm';

import SvgSpinners90RingWithBg from '~icons/svg-spinners/90-ring-with-bg';

const { isLoadingLyrics, stateLyrics, searchResults, hasSearchedForLyrics, dirtyLyric } = storeToRefs(useLyricStore());
const { handlePreviewLyrics, handleSelectLyrics, handleSearchSyncedLyrics, resetLyrics } = useLyricStore();

const emit = defineEmits(['handleFinish', 'preview']);
const props = defineProps<{ video: VideoResource }>();

const fields = reactive<FormField[]>([
    {
        name: 'track',
        text: 'Track Title',
        type: 'text',
        required: true,
        value: props.video.title,
        default: props.video.title,
        max: 255,
    },
    {
        name: 'artist',
        text: 'Artist Name',
        type: 'text',
        required: true,
        placeholder: 'Use the artist name to refine your search',
        value: dirtyLyric.value?.artistName,
        max: 255,
    },
    {
        name: 'album',
        text: 'Album Name',
        type: 'text',
        required: true,
        placeholder: 'Use the album name to refine your search',
        value: dirtyLyric.value?.albumName,
        max: 255,
    },
    {
        name: 'duration',
        text: `Song Duration`,
        type: 'text',
        value: toFormattedDuration(props.video.metadata?.duration),
        disabled: !props.video.metadata?.duration,
        subtext: 'Try to match this value',
        placeholder: `No duration set`,
    },
    {
        name: 'lyrics',
        text: `Lyrics`,
        type: 'textArea',
        value: props.video.metadata?.lyrics,
        subtext: 'Format: [mm:ss] line',
        placeholder: `No lyrics yet`,
        default: '',
    },
]);

const form = useForm<LyricsUpdateRequest>({
    track: props.video.metadata?.title ?? props.video.title ?? '',
    lyrics: props.video.metadata?.lyrics ?? '',
    artist: props.video.metadata?.artist ?? '',
    album: props.video.metadata?.album ?? '',
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            if (!props.video.metadata?.id) {
                throw new Error('Metadata Malformed!');
            }
            return updateLyrics(props.video.metadata.id, fields);
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add('Success', { type: 'success', description: 'Edit submitted!', life: 3000 });
            },
            onError: () => {
                toast.add('Error', { type: 'danger', description: 'Unable to update lyrics details.', life: 3000 });
            },
        },
    );
};

const handleLoadLyricInfo = () => {
    if (!dirtyLyric.value?.id) {
        form.reset(...Object.keys(form.fields));
        return;
    }

    form.fields.album = dirtyLyric.value.albumName;
    form.fields.artist = dirtyLyric.value.artistName;
    form.fields.lyrics = stateLyrics.value;
};

onMounted(() => {
    handleLoadLyricInfo();
});

watch(
    () => dirtyLyric.value,
    () => {
        handleLoadLyricInfo();
    },
);
</script>

<template>
    <form class="flex flex-col sm:flex-row sm:justify-between flex-wrap gap-4" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields.filter((field) => !field.disabled)" :key="index" class="w-full text-sm" :class="field.class">
            <FormInputLabel :field="field" />
            <FormInput v-if="field.name === 'duration'" :field="field" v-model="field.value" disabled title="Song Duration" />
            <FormTextArea v-else-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" />
            <DatePicker v-else-if="field.type === 'date'" v-model="form.fields[field.name]" :field="field" />
            <FormInputNumber v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" />
            <FormInput v-else v-model="form.fields[field.name]" :field="field" />
            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </div>

        <div class="flex w-full gap-4 flex-wrap xs:flex-nowrap">
            <div class="flex items-center gap-2 flex-wrap">
                <ButtonForm
                    :class="'h-8 line-clamp-1 truncate !rounded-full hover:!ring-violet-400 hover:dark:!ring-violet-700 hover:ring-[0.125rem]'"
                    :disabled="isLoadingLyrics"
                    @click="handleSearchSyncedLyrics(generateLyricsSearchQuery(video.metadata, form.fields.track, form.fields.album, form.fields.artist))"
                >
                    <template #text>Search for Lyrics</template>
                </ButtonForm>
                <ButtonForm :class="'h-8 !rounded-full hover:!ring-violet-400 hover:dark:!ring-violet-700 hover:ring-[0.125rem]'" :disabled="isLoadingLyrics" @click="resetLyrics">
                    <template #text>Reset</template>
                </ButtonForm>
            </div>
            <p class="text-sm ml-auto h-8 text-nowrap items-center flex min-w-fit" v-show="hasSearchedForLyrics">Results: {{ searchResults.length }}</p>
        </div>

        <div class="flex flex-col gap-2 w-full" v-if="isLoadingLyrics || hasSearchedForLyrics || searchResults.length !== 0">
            <div class="flex gap-2 flex-col w-full">
                <LrcLibCard v-for="result in searchResults" :key="result.id" :data="result" @preview="handlePreviewLyrics(result)" @select="handleSelectLyrics(result)" />
                <div
                    v-if="isLoadingLyrics || (hasSearchedForLyrics && searchResults.length === 0)"
                    class="col-span-full flex items-center justify-center text-center text-gray-500 text-sm dark:text-gray-400 uppercase tracking-wider w-full gap-2"
                >
                    <p>{{ isLoadingLyrics ? '...Loading' : 'No Results' }}</p>
                    <SvgSpinners90RingWithBg v-show="isLoadingLyrics" />
                </div>
            </div>
            <ButtonForm
                v-show="!isLoadingLyrics && !hasSearchedForLyrics && searchResults.length !== 0"
                :class="'mx-auto h-8 !rounded-full'"
                :disabled="isLoadingLyrics"
                @click="handleSearchSyncedLyrics(generateLyricsSearchQuery(video.metadata, form.fields.track, form.fields.album, form.fields.artist))"
            >
                <template #text> show more </template>
            </ButtonForm>
        </div>

        <p class="text-sm text-rose-600 dark:text-rose-400 text-center w-full" v-if="form.fields.lyrics !== video.metadata?.lyrics && video.metadata?.lyrics">
            Overwriting Existing Lyrics!
        </p>

        <p class="text-sm text-rose-600 dark:text-rose-400 text-center w-full" v-if="form.fields.artist !== video.metadata?.artist && video.metadata?.artist">
            Overwriting Existing Artist Name!
        </p>

        <p class="text-sm text-rose-600 dark:text-rose-400 text-center w-full" v-if="form.fields.album !== video.metadata?.album && video.metadata?.album">
            Overwriting Existing Album Name!
        </p>

        <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 w-full">
            <button
                @click="
                    () => {
                        $emit('handleFinish');
                        resetLyrics();
                    }
                "
                type="button"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'"
                :disabled="form.processing"
            >
                Cancel
            </button>
            <button
                @click="handleSubmit"
                type="button"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '"
                :disabled="form.processing"
            >
                Save Changes
            </button>
        </div>
    </form>
</template>
