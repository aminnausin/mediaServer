<template>
    <section class="flex gap-3 p-3 sm:p-6 flex-wrap sm:flex-nowrap rounded-xl shadow-md dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5">
        <slot name="heading"></slot>

        <slot name="content"></slot>
    </section>
</template>
