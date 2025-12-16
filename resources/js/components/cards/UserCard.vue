<script setup lang="ts">
import type { UserResource } from '@/types/resources';

import { toFormattedDate, toTimeSpan } from '@/service/util';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';
import BasePopover from '@/components/pinesUI/BasePopover.vue';
import ButtonText from '@/components/inputs/ButtonText.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import ProiconsPersonCircle from '~icons/proicons/person-circle';
import ProiconsLockOpen from '~icons/proicons/lock-open';
import ProiconsDelete from '~icons/proicons/delete';

const props = defineProps<{ data: UserResource }>();
</script>
<template>
    <div
        class="xs:flex-row xs:gap-4 xs:p-3 dark:bg-primary-dark-800/70 dark:hover:bg-primary-dark-600 hover:bg-primary-800 group relative flex w-full flex-col divide-gray-300 rounded-xl bg-white text-left shadow-sm ring-1 ring-gray-900/5 dark:divide-neutral-400 dark:text-white"
    >
        <img
            class="xs:max-h-16 xs:rounded-full my-auto aspect-square h-full max-h-28 rounded-t-xl object-cover"
            :src="`https://ui-avatars.com/api/?name=${data.name[0]}&amp;color=7F9CF5&amp;background=random`"
            :alt="data.name ?? 'user profile'"
        />
        <div class="xs:p-0 flex max-h-full max-w-full flex-1 flex-col flex-wrap gap-4 p-3">
            <section class="flex w-full items-center justify-between gap-2">
                <h2 class="flex-1 truncate capitalize" :title="data.name ?? 'username'">
                    {{ data.name }}
                </h2>

                <div class="flex flex-1 justify-end gap-1">
                    <BasePopover
                        popoverClass="w-64! rounded-lg "
                        :buttonComponent="ButtonCorner"
                        :button-attributes="{
                            positionClasses: 'w-7 h-7 p-1! ml-auto sm:hidden',
                            textClasses: 'hover:text-violet-600 dark:hover:text-violet-500',
                            colourClasses: 'dark:hover:bg-neutral-800 hover:bg-gray-200',
                            label: 'Manage Permissions',
                        }"
                    >
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="h-4 w-4" />
                        </template>
                        <template #content>
                            <div class="grid gap-4">
                                <div class="space-y-2">
                                    <h4 class="leading-none font-medium">Manage User</h4>
                                </div>

                                <div class="grid gap-2">
                                    <ButtonText class="h-8 dark:bg-neutral-950!" :title="'Scan for Folder Changes'" :to="'/profile'">
                                        <template #text> View Profile </template>
                                        <template #icon> <ProiconsPersonCircle class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText class="h-8 disabled:opacity-60 dark:bg-neutral-950!" :title="'Set User Access Permissions'" :disabled="true">
                                        <template #text> Manage Permissions </template>
                                        <template #icon> <ProiconsLockOpen class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText
                                        class="h-8 text-rose-600 disabled:opacity-60 dark:bg-rose-700!"
                                        :title="'Remove User From Server'"
                                        @click.stop.prevent="$emit('clickAction')"
                                        disabled
                                    >
                                        <template #text> Remove User </template>
                                        <template #icon> <ProiconsDelete class="h-4 w-4" /></template>
                                    </ButtonText>
                                </div>
                            </div>
                        </template>
                    </BasePopover>
                    <ButtonCorner
                        :positionClasses="'w-7 h-7 sm:flex hidden'"
                        :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                        :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-200'"
                        :to="`/profile/${data.name}`"
                        :label="'View Profile'"
                        @click.stop.prevent="() => {}"
                    >
                        <template #icon>
                            <ProiconsPersonCircle width="20" height="20" />
                        </template>
                    </ButtonCorner>
                    <ButtonCorner
                        :positionClasses="'w-7 h-7 sm:flex hidden'"
                        :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                        :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-200'"
                        :label="'Manage Permissions'"
                        @click.stop.prevent="() => {}"
                        disabled
                    >
                        <template #icon>
                            <ProiconsLockOpen width="20" height="20" />
                        </template>
                    </ButtonCorner>
                    <ButtonCorner
                        :positionClasses="'w-7 h-7 sm:flex hidden'"
                        :textClasses="'text-rose-700 hover:text-rose-600'"
                        :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-200'"
                        :label="'Remove User'"
                        @click.stop.prevent="$emit('clickAction')"
                    />
                </div>
            </section>
            <section class="flex w-full flex-col text-sm text-neutral-600 sm:flex-row sm:justify-between dark:text-neutral-400">
                <h3 class="w-full truncate text-wrap sm:text-nowrap" :title="`Date joined`">
                    Date Joined:
                    {{ data.created_at ? toFormattedDate(new Date(data.created_at), false, { year: 'numeric', month: 'short', day: 'numeric' }) : 'Unknown' }}
                </h3>
                <h3 class="line-clamp-2 w-full truncate capitalize sm:text-right" v-show="data.last_active" title="Last date of activity">
                    Last Active:
                    {{ data.last_active ? toTimeSpan(new Date(data.last_active)) : 'Unknown' }}
                </h3>
            </section>
        </div>
    </div>
</template>
