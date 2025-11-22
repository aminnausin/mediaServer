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
        class="text-left relative flex flex-col xs:flex-row xs:gap-4 xs:p-3 rounded-xl dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5 dark:hover:bg-primary-dark-600 hover:bg-primary-800 dark:text-white shadow-sm w-full group divide-gray-300 dark:divide-neutral-400"
    >
        <img
            class="aspect-square h-full max-h-28 xs:max-h-16 my-auto rounded-t-xl xs:rounded-full object-cover"
            :src="`https://ui-avatars.com/api/?name=${data.name[0]}&amp;color=7F9CF5&amp;background=random`"
            :alt="data.name ?? 'user profile'"
        />
        <div class="flex flex-col gap-4 flex-wrap flex-1 max-w-full max-h-full p-3 xs:p-0">
            <section class="flex justify-between gap-2 w-full items-center">
                <h2 class="truncate capitalize flex-1" :title="data.name ?? 'username'">
                    {{ data.name }}
                </h2>

                <div class="flex justify-end gap-1 flex-1">
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
                                    <h4 class="font-medium leading-none">Manage User</h4>
                                </div>

                                <div class="grid gap-2">
                                    <ButtonText class="h-8 dark:bg-neutral-950!" :title="'Scan for Folder Changes'" :to="'/profile'">
                                        <template #text> View Profile </template>
                                        <template #icon> <ProiconsPersonCircle class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText class="h-8 dark:bg-neutral-950! disabled:opacity-60" :title="'Set User Access Permissions'" disabled>
                                        <template #text> Manage Permissions </template>
                                        <template #icon> <ProiconsLockOpen class="h-4 w-4" /></template>
                                    </ButtonText>
                                    <ButtonText
                                        class="h-8 text-rose-600 dark:bg-rose-700! disabled:opacity-60"
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
            <section class="flex flex-col sm:flex-row sm:justify-between w-full text-sm text-neutral-600 dark:text-neutral-400">
                <h3 class="w-full text-wrap truncate sm:text-nowrap" :title="`Date joined`">
                    Date Joined:
                    {{ data.created_at ? toFormattedDate(new Date(data.created_at), false, { year: 'numeric', month: 'short', day: 'numeric' }) : 'Unknown' }}
                </h3>
                <h3 class="truncate sm:text-right w-full line-clamp-2 capitalize" v-show="data.last_active" title="Last date of activity">
                    Last Active:
                    {{ data.last_active ? toTimeSpan(new Date(data.last_active)) : 'Unknown' }}
                </h3>
            </section>
        </div>
    </div>
</template>
