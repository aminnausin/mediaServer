<script setup lang="ts">
import type { UserResource } from '@/types/resources';

import { toFormattedDate, toTimeSpan } from '@/service/util';
import { ButtonCorner, ButtonText } from '@/components/cedar-ui/button';
import { BasePopover } from '@/components/cedar-ui/popover';

import LazyImage from '@/components/lazy/LazyImage.vue';

import ProiconsMoreVertical from '~icons/proicons/more-vertical';
import ProiconsPersonCircle from '~icons/proicons/person-circle';
import ProiconsLockOpen from '~icons/proicons/lock-open';
import ProiconsDelete from '~icons/proicons/delete';

const props = defineProps<{ data: UserResource }>();
</script>
<template>
    <div class="xs:flex-row xs:gap-4 xs:p-3 group data-card relative flex w-full flex-col rounded-xl text-left shadow-sm ring-1 ring-gray-900/5">
        <LazyImage
            :wrapper-class="'my-auto flex flex-col w-auto sm:aspect-square relative'"
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
                            positionClasses: 'size-7 p-1! ml-auto sm:hidden',
                            textClasses: 'hover:text-primary dark:hover:text-primary-muted',
                            colourClasses: 'dark:hover:bg-neutral-800 hover:bg-gray-200',
                            label: 'Manage Permissions',
                        }"
                    >
                        <template #buttonIcon>
                            <ProiconsMoreVertical class="size-4" />
                        </template>
                        <template #content>
                            <div class="grid gap-4">
                                <h4 class="leading-none font-medium">Manage User</h4>

                                <div class="grid gap-2">
                                    <ButtonText class="justify-between dark:bg-neutral-950" title="Scan for Folder Changes" to="/profile">
                                        View Profile
                                        <template #icon> <ProiconsPersonCircle class="size-4" /></template>
                                    </ButtonText>
                                    <ButtonText class="justify-between dark:bg-neutral-950" title="Set User Access Permissions" disabled>
                                        Manage Permissions
                                        <template #icon> <ProiconsLockOpen class="size-4" /></template>
                                    </ButtonText>
                                    <ButtonText
                                        class="text-danger dark:text-foreground-0 dark:bg-danger-3 dark:hocus:bg-danger justify-between"
                                        title="Remove User From Server"
                                        @click.stop.prevent="$emit('clickAction')"
                                        disabled
                                    >
                                        Remove User
                                        <template #icon> <ProiconsDelete class="size-4" /></template>
                                    </ButtonText>
                                </div>
                            </div>
                        </template>
                    </BasePopover>
                    <ButtonCorner
                        class="hover:text-primary dark:hover:text-primary-muted hover:dark:bg-surface-1 hover:bg-surface-6 size-7 transition-none"
                        :to="`/profile/${data.name}`"
                        :label="'View Profile'"
                        :useDefaultStyle="false"
                    >
                        <template #icon>
                            <ProiconsPersonCircle width="20" height="20" />
                        </template>
                    </ButtonCorner>

                    <ButtonCorner
                        :useDefaultStyle="false"
                        :label="'Manage Permissions'"
                        class="hover:text-primary dark:hover:text-primary-muted hover:dark:bg-surface-1 hover:bg-surface-6 size-7 transition-none"
                        disabled
                    >
                        <template #icon>
                            <ProiconsLockOpen width="20" height="20" />
                        </template>
                    </ButtonCorner>
                    <ButtonCorner
                        @click.stop.prevent="$emit('clickAction')"
                        :useDefaultStyle="false"
                        :label="'Remove User'"
                        class="text-danger-3/80 hover:text-danger-2 hover:dark:bg-surface-1 hover:bg-surface-6 hidden size-7 transition-none *:size-5 sm:flex"
                    />
                </div>
            </section>
            <section class="text-foreground-1 flex w-full flex-col text-sm sm:flex-row sm:justify-between">
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
