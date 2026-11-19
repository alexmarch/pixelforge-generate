<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import Navbar from '@/shared/Navbar.vue';
import Sidebar from '@/shared/Sidebar.vue';
const page = usePage();
</script>

<template>
    <main class="min-h-screen flex flex-col bg-zinc-950 text-white">
        <Navbar :auth="page.props.auth" />
        <Sidebar :appName="page.props.name" />
        <div class="w-full md:w-[calc(100%-16rem)] md:ml-64 p-5">
            <slot />
        </div>
    </main>
</template>
