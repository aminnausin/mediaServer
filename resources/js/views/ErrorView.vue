
<template>
    <main class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center pt-8 sm:justify-start sm:pt-0">
                <div class="px-4 text-lg text-gray-400 border-r border-gray-400 tracking-wider">
                    {{ $route.name }}                   
                </div>

                <div class="ml-4 text-lg text-gray-500 uppercase tracking-wider">
                    {{ $route.meta.message }} : {{ $route.query?.message ?? 'No message' }}     
                </div>
            </div>
        </div>
    </main>
</template>