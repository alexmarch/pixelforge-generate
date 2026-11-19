<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import Navbar from '@/shared/Navbar.vue';
import Sidebar from '@/shared/Sidebar.vue';
const page = usePage();
</script>

<template>
    <main class="min-h-screen flex flex-col bg-zinc-950 text-white">
        <Navbar :auth="page.props.auth" />
        <Sidebar :auth="page.props.auth" :appName="page.props.name" />
        <div :class="{ 'md:w-[calc(100%-16rem)] md:ml-64': page.props.auth?.user }" class="w-full p-5">
            <slot />
        </div>
    </main>
</template>
