<script setup lang="ts">
import type { FolderRelationEntry, RelationUpsert, RelationsUpdateRequest } from '@/types/relationRequests';
import type { FolderRelationEditorProps } from '@/types/modals';
import type { SeriesResource } from '@/contracts/media';
import type { AxiosError } from 'axios';

import { searchSeries, updateSeriesRelations } from '@/service/mediaAPI';
import { handleStorageURL, toPlural } from '@/service/util';
import { ButtonCorner, ButtonForm } from '@/components/cedar-ui/button';
import { computed, ref, watch } from 'vue';
import { TableLoadingSpinner } from '@/components/cedar-ui/table';
import { FormErrorList } from '@/components/cedar-ui/form';
import { InputSelect } from '@/components/cedar-ui/select';
import { TextInput } from '@/components/cedar-ui/input';
import { cn, toast } from '@aminnausin/cedar-ui';

import FolderRelationEntryCard from '@/components/cards/data/relations/FolderRelationEntryCard.vue';
import SeriesSearchResultCard from '@/components/cards/data/relations/SeriesSearchResultCard.vue';
import ModalFormFooter from '@/components/forms/ModalFormFooter.vue';
import useForm from '@/composables/useForm';

import LucideWaypoints from '@/components/icons/LucideWaypoints.vue';

const RELATION_TYPE_OPTIONS = [
    { title: 'Sequel', value: 'sequel' },
    { title: 'Prequel', value: 'prequel' },
    { title: 'Spin Off', value: 'spinoff' },
    { title: 'Side Story', value: 'side_story' },
    { title: 'Summary', value: 'summary' },
    { title: 'Soundtrack', value: 'soundtrack' },
    { title: 'Soundtrack for', value: 'main_series' },
    { title: 'Alternative', value: 'alternative' },
    { title: 'Related', value: 'related' },
    { title: 'Parent', value: 'parent' },
];

const ASSIGNABLE_OPTIONS = RELATION_TYPE_OPTIONS.filter((option) => option.value !== 'parent');
const optionsFor = (entry: FolderRelationEntry) => (entry.originalType === 'parent' ? RELATION_TYPE_OPTIONS : ASSIGNABLE_OPTIONS);

const DEFAULT_IMAGE = '/storage/thumbnails/default.webp';

const props = defineProps<FolderRelationEditorProps>();
const emit = defineEmits(['handleFinish']);

const toImage = (series?: Pick<SeriesResource, 'poster_image' | 'thumbnail_url'>) => series?.poster_image?.path ?? handleStorageURL(series?.thumbnail_url) ?? DEFAULT_IMAGE;
const toEntry = (series: SeriesResource, title: string, type: string, originalType: string | null): FolderRelationEntry => ({
    seriesId: series.id,
    title,
    library: series.library_name,
    image: toImage(series),
    blurhash: series.poster_image?.blur_hash,
    type,
    originalType,
    isRemoved: false,
});

const buildEntries = (): FolderRelationEntry[] =>
    props.relations.flatMap((relation) => {
        const series = relation.folder.series;
        return series ? [toEntry(series, relation.folder.title, relation.type, relation.type)] : [];
    });

const entries = ref<FolderRelationEntry[]>(buildEntries());

const activeCount = computed(() => entries.value.filter((e) => !e.isRemoved).length);
const upserts = computed<RelationUpsert[]>(() => entries.value.filter((e) => !e.isRemoved && e.type !== e.originalType).map((e) => ({ series_id: e.seriesId, type: e.type })));
const deletes = computed(() => entries.value.filter((e) => e.isRemoved && e.originalType !== null).map((e) => e.seriesId));
const isDirty = computed(() => upserts.value.length > 0 || deletes.value.length > 0);

const form = useForm<RelationsUpdateRequest>({ upserts: [], deletes: [] });

//#region Search

const query = ref('');
const results = ref<SeriesResource[]>([]);
const isSearching = ref(false);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;
let searchToken = 0;

const availableResults = computed(() => results.value.filter((s) => s.id !== props.seriesId && !entries.value.some((e) => e.seriesId === s.id)));

async function runSearch(q: string) {
    const token = ++searchToken;

    try {
        const found = await (await searchSeries(q)).data.data;
        if (token === searchToken) results.value = found;
    } catch (error) {
        console.error(error);
        if (token === searchToken) {
            results.value = [];
            toast.error('Error', { description: 'Failed to search series.' });
        }
    } finally {
        if (token === searchToken) isSearching.value = false;
    }
}

watch(query, (value) => {
    clearTimeout(searchTimeout);

    const q = value.trim();

    if (q.length < 2) {
        searchToken++;
        results.value = [];
        isSearching.value = false;
        return;
    }

    isSearching.value = true;
    searchTimeout = setTimeout(() => runSearch(q), 250);
});

//#endregion

function handleAdd(series: SeriesResource) {
    entries.value.push(toEntry(series, series.title ?? '', 'related', null));
    query.value = '';
    form.clearErrors();
}

function handleRemove(entry: FolderRelationEntry) {
    if (entry.originalType === null) {
        entries.value = entries.value.filter((e) => e !== entry);
        return;
    }

    entry.isRemoved = true;
}

function handleReset() {
    entries.value = buildEntries();
    query.value = '';
    form.clearErrors();
}

const handleSubmit = () => {
    if (!isDirty.value) {
        toast.add('Success', { type: 'success', description: 'Relations updated!' });
        emit('handleFinish');
        return;
    }

    form.fields.upserts = upserts.value;
    form.fields.deletes = deletes.value;
    form.submit(async (fields) => updateSeriesRelations(props.seriesId, fields), {
        onSuccess: (response) => {
            emit('handleFinish', response?.data);
            toast.add('Success', { type: 'success', description: 'Relations updated!' });
        },
        onError: (err: AxiosError) => {
            const msg = (err.response?.data as { message?: string })?.message ?? 'Unable to update relations.';
            toast.add('Error', { type: 'danger', description: msg });
        },
    });
};
</script>

<template>
    <form class="xms:text-sm flex flex-1 flex-col gap-4 text-xs" @submit.prevent="handleSubmit">
        <div class="relative w-full shrink-0 space-y-2">
            <div class="relative">
                <TextInput
                    :id="'series-search-query'"
                    v-model="query"
                    type="text"
                    autocomplete="off"
                    aria-label="Search series"
                    placeholder="Search series to relate to…"
                    class="xms:text-sm h-9 pe-8 text-xs"
                />
                <div v-show="!!query" class="pointer-events-none absolute inset-0 flex items-center justify-end pe-2">
                    <ButtonCorner
                        :use-default-style="false"
                        class="text-foreground-1 hocus:text-foreground-0 pointer-events-auto p-0.5 *:size-5"
                        :title="'Clear'"
                        @click="query = ''"
                    />
                </div>
            </div>

            <ul v-if="query.trim().length >= 2" class="scrollbar-minimal max-h-56 space-y-1.5 overflow-y-auto rounded-md p-1">
                <template v-if="isSearching">
                    <li class="flex justify-center px-3 py-5">
                        <TableLoadingSpinner :is-loading="true" class="text-foreground-2 text-sm" />
                    </li>
                </template>

                <template v-else-if="availableResults.length">
                    <li v-for="series in availableResults" :key="series.id" class="">
                        <SeriesSearchResultCard :series="series" :poster-url="toImage(series)" @click="handleAdd(series)" />
                    </li>
                </template>

                <li v-else class="text-foreground-2 px-3 py-5 text-center">No matching series found.</li>
            </ul>
        </div>

        <section v-if="entries.length" class="flex min-h-0 w-full flex-1 flex-col gap-2">
            <div class="flex shrink-0 items-center justify-between gap-2 text-xs">
                <h3 class="text-foreground-1 font-medium">Relations</h3>
                <span class="text-foreground-2 tabular-nums"> {{ activeCount }} item{{ toPlural(activeCount) }} </span>
            </div>

            <ul class="space-y-2">
                <li v-for="entry in entries" :key="entry.seriesId">
                    <FolderRelationEntryCard :entry="entry" :disabled="form.processing" @remove="handleRemove(entry)" @restore="entry.isRemoved = false">
                        <InputSelect
                            v-model="entry.type"
                            :name="`relation-type-${entry.seriesId}`"
                            :options="optionsFor(entry)"
                            :disabled="form.processing"
                            :title="`Relation of ${entry.title}`"
                            :menu-margin="{ top: 'mt-10', bottom: 'mb-10' }"
                            placeholder="Select relation type"
                            root-class="capitalize w-full min-w-0 whitespace-nowrap! text-xs"
                            class="h-7 ps-2! pe-1 dark:bg-neutral-700/30"
                        />
                    </FolderRelationEntryCard>
                </li>
            </ul>
        </section>

        <div v-else class="text-foreground-2 flex flex-1 flex-col items-center justify-center gap-3 py-8 text-center">
            <div class="bg-surface-2 flex size-12 items-center justify-center rounded-full">
                <LucideWaypoints class="size-6" />
            </div>
            <div class="space-y-1">
                <p class="text-foreground-1 text-sm font-medium">No relations yet</p>
                <p>Search for a series above to add a relation</p>
            </div>
        </div>

        <div v-if="deletes.length" class="text-danger w-full text-center">Removing {{ deletes.length }} relation{{ toPlural(deletes.length) }}</div>

        <FormErrorList v-if="form.errors" class="w-full shrink-0 text-center" :errors="form.errors" />

        <ModalFormFooter class="mt-auto h-fit">
            <ButtonForm variant="reset" class="h-9" :disabled="form.processing" @click="$emit('handleFinish')"> Cancel </ButtonForm>
            <ButtonForm
                variant="danger"
                :class="
                    cn('transition-reveal h-9 overflow-hidden', {
                        'xs:mx-0 xs:w-18 h-9 py-2 opacity-100': isDirty,
                        'xs:-mx-1 xs:mt-0 xs:h-9 xs:w-0 xs:px-0 xs:py-2 -mt-2 h-0 w-full py-0 opacity-0!': !isDirty,
                    })
                "
                :disabled="form.processing"
                @click="handleReset"
            >
                Reset
            </ButtonForm>
            <ButtonForm variant="submit" class="h-9" :disabled="form.processing" @click="handleSubmit">Save</ButtonForm>
        </ModalFormFooter>
    </form>
</template>
