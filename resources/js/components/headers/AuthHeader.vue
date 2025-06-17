<template>
    <div class="flex items-center pt-8 px-6 text-center sm:justify-start sm:pt-0 flex-col gap-1">
        <h1 class="px-4 text-2xl tracking-wider">Media Server</h1>
        <slot name="subtext">
            <p class="text-neutral-500 dark:text-neutral-400 max-w-60 sm:max-w-none">
                <slot> </slot>
            </p>
        </slot>
    </div>
</template>
