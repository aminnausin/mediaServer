<template>
    <div class="animate-pulse space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="bg-surface-2 h-5 w-24 rounded"></div>
                <div class="bg-surface-2 h-4 w-4 rounded-full"></div>
                <div class="bg-surface-2 h-5 w-20 rounded"></div>
            </div>
            <div class="flex gap-2">
                <div v-for="i in 5" :key="i" class="bg-surface-2 h-6 w-10 rounded"></div>
            </div>
        </div>

        <div class="flex gap-2">
            <div class="bg-surface-2 h-9 w-32 rounded-md"></div>
            <div class="bg-surface-2 h-9 w-28 rounded-md"></div>
            <div class="bg-surface-2 h-9 w-24 rounded-md"></div>
        </div>

        <div class="grid w-full grid-cols-2 gap-6 sm:grid-cols-4 lg:grid-cols-6">
            <div class="bg-surface-2 col-span-2 row-span-3 h-full min-h-30 rounded-xl lg:col-span-2"></div>
            <div class="bg-surface-2 col-span-2 h-40 rounded-xl lg:col-span-4"></div>
            <div class="bg-surface-2 col-span-2 row-span-2 hidden h-80 rounded-xl lg:col-span-4 lg:block"></div>

            <div
                v-for="i in 4"
                :key="i"
                class="bg-surface-2/50 border-surface-2 flex h-48 flex-col gap-3 rounded-xl border p-4 shadow-sm"
                :class="i % 2 === 0 ? 'col-span-2' : 'col-span-2 lg:col-span-4'"
            >
                <div class="flex items-center gap-2">
                    <div class="bg-surface-4 h-5 w-5 rounded-md"></div>
                    <div class="bg-surface-4 h-4 w-24 rounded"></div>
                </div>
                <div class="bg-surface-4 mt-auto h-8 w-1/2 rounded"></div>
                <div class="bg-surface-4 h-3 w-full rounded"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ensure your background colors match your theme's surface levels */
/* .bg-surface-1 {
    @apply bg-gray-100 dark:bg-[#1a1a1a];
}
.bg-surface-2 {
    @apply bg-gray-200 dark:bg-[#2a2a2a];
} */
</style>
