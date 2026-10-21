<script setup lang="ts">
import type { DiskFilesystem } from '@/contracts/server';

import { formatFileSize } from '@/service/util';

import ProiconsDatabase from '~icons/proicons/database';

withDefaults(
    defineProps<{
        version?: 1 | 2 | 3;
        disks: (DiskFilesystem & {
            title: string;
            percentUsed: number;
            segments: {
                percent: number;
                label: string;
                colour: string;
                key: any;
                bytes: number;
            }[];
            contents: string;
            libraryNames: string;
            colour: string;
        })[];
        diskKinds: Record<string, { label: string; colour: string }>;
    }>(),
    { version: 3 },
);
</script>
<template>
    <template v-if="version === 1">
        <div class="flex items-center gap-2 text-base">
            <ProiconsDatabase class="text-foreground-2 size-4" />
            <h3 class="text-foreground-2">Disks</h3>
            <div class="text-foreground-2 ml-auto flex items-center gap-3 text-xs">
                <span v-for="kind in diskKinds" :key="kind.label" class="flex items-center gap-1.5">
                    <span :class="kind.colour" class="size-2 rounded-full"></span>{{ kind.label }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <div v-for="disk in disks" :key="disk.id" class="flex flex-col gap-2 rounded-lg p-3 ring-1 ring-neutral-500/20">
                <div class="flex items-center gap-2 text-sm">
                    <h3>{{ disk.title }}</h3>
                    <span class="text-foreground-2 ml-auto flex items-center gap-1.5 text-xs">
                        <span :class="disk.colour" class="size-2 rounded-full"></span>
                        {{ disk.percentUsed }}% used
                    </span>
                </div>

                <p class="text-lg leading-none">
                    {{ formatFileSize(disk.freeSpace) }}
                    <span class="text-foreground-2 text-xs">free of {{ formatFileSize(disk.totalSpace) }}</span>
                </p>

                <div class="flex h-2 w-full gap-px overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700" :aria-label="`${disk.percentUsed}% used`">
                    <div
                        v-for="segment in disk.segments"
                        :key="segment.key"
                        :class="segment.colour"
                        class="min-w-0.5"
                        :style="{ width: `${segment.percent}%` }"
                        :title="`${segment.label} ${formatFileSize(segment.bytes)}`"
                    ></div>
                </div>

                <p v-if="disk.contents" class="text-foreground-2 text-xs" :title="disk.libraryNames">{{ disk.contents }}</p>
            </div>
        </div>
    </template>

    <template v-else-if="version === 2">
        <div class="flex items-center gap-2 text-base">
            <ProiconsDatabase class="text-foreground-2 size-4" />
            <h3 class="text-foreground-2">Disks</h3>
        </div>
        <div v-for="disk in disks" :key="disk.id" class="flex flex-col gap-1.5">
            <div class="flex items-baseline gap-2">
                <h3 class="text-sm">{{ disk.title }}</h3>
                <span class="text-foreground-2 truncate text-xs">{{ disk.contents }}</span>
                <span class="ml-auto shrink-0 text-sm">
                    {{ formatFileSize(disk.freeSpace) }} free
                    <span class="text-foreground-2 text-xs">of {{ formatFileSize(disk.totalSpace) }}</span>
                </span>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700" :aria-label="`${disk.percentUsed}% used`">
                <div :class="disk.colour" class="h-full" :style="{ width: `${disk.percentUsed}%` }"></div>
            </div>
        </div>
    </template>

    <template v-else-if="version === 3">
        <div class="flex items-center gap-2 text-base">
            <ProiconsDatabase class="text-foreground-2 size-4" />

            <h3 class="text-foreground-2">Disks</h3>
            <div class="text-foreground-2 ml-auto flex items-center gap-3 text-xs">
                <span v-for="kind in diskKinds" :key="kind.label" class="flex items-center gap-1.5">
                    <span :class="kind.colour" class="size-2 rounded-full"></span>{{ kind.label }}
                </span>
            </div>
        </div>

        <div v-for="disk in disks" :key="disk.id" class="flex flex-col gap-1.5 rounded-lg">
            <div class="flex items-baseline gap-2">
                <h3 class="text-nowrap tabular-nums" :title="disk.id">{{ disk.title }}</h3>
                <span class="text-foreground-2 truncate text-xs lowercase" :title="disk.libraryNames || 'No libraries'">{{ disk.contents }}</span>
                <span class="ml-auto shrink-0">
                    {{ formatFileSize(disk.freeSpace) }} free
                    <span class="text-foreground-2 text-xs">of {{ formatFileSize(disk.totalSpace) }}</span>
                </span>
                <span :class="[disk.colour, 'size-2 shrink-0 rounded-full']" :title="`${disk.percentUsed}% used`"></span>
            </div>

            <div class="flex h-1.5 w-full gap-px overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700" :aria-label="`${disk.percentUsed}% used`">
                <div
                    v-for="segment in disk.segments"
                    :key="segment.key"
                    :class="[segment.colour, 'min-w-1']"
                    :style="{ width: `${segment.percent}%` }"
                    :title="`${segment.label} ${formatFileSize(segment.bytes)}`"
                ></div>
            </div>
        </div>
    </template>
</template>
