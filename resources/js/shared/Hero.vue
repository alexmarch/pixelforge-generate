<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

</script>
<template>
    <div class="flex flex-col items-center justify-centertext-center text-white mt-20">
        <h1 class="text-4xl font-bold">Welcome to <span class="text-violet-400 text-6xl">AI</span> Image Generator (pixelforge)</h1>
        <p class="mt-4 text-xl text-stone-400">Create stunning images with the power of AI.</p>
        <Link href="/login" class="flex items-center gap-2 mt-6 w-fit bg-black border border-stone-800 text-white py-2 px-4 rounded">
            Get Started
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </Link>
    </div>
</template>