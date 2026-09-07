<template>
    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
        <path d="M0 0h24v24H0z" fill="none" />
        <defs>
            <mask id="off-mask-captions">
                <rect width="24" height="24" fill="white" />
                <path stroke-width="4" d="M3 3l18 18" stroke="black" stroke-linecap="round" stroke-linejoin="round" />
            </mask>
        </defs>

        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
            <rect width="18" height="14" x="3" y="5" rx="3" ry="3" mask="url(#off-mask-captions)" />
            <path d="M7 11h4m-4 4h2.5M17 11h-.5" />
            <path stroke-width="1.5" d="M3 3l18 18" />
        </g>
    </svg>
</template>
