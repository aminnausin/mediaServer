<template>
    <section class="dark:bg-primary-dark-800/70 flex flex-wrap gap-3 rounded-xl bg-white p-3 shadow-md ring-1 ring-gray-900/5 sm:flex-nowrap sm:p-6">
        <slot name="heading"></slot>

        <slot name="content"></slot>

        <slot></slot>
    </section>
</template>
