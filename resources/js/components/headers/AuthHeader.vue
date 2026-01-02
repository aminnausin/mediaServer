<template>
    <div class="flex flex-col items-center gap-1 px-6 pt-8 text-center sm:justify-start sm:pt-0">
        <h1 class="px-4 text-2xl tracking-wider">Media Server</h1>
        <slot name="subtext">
            <p class="text-foreground-2 max-w-60 sm:max-w-none">
                <slot> </slot>
            </p>
        </slot>
    </div>
</template>
