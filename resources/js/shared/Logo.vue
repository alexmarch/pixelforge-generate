<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
<template>
    <Link href="/">
        <span class="grid h-10 w-10 grid-cols-3 gap-0.75 rounded-xl border border-white/10 bg-white/4 p-2 shadow-glow">
            <i class="rounded-xs bg-violet-600"></i><i class="rounded-xs bg-violet-400"></i><i class="rounded-xs bg-lime-600"></i>
            <i class="rounded-xs bg-violet-600/60"></i><i class="rounded-xs bg-lime-600"></i><i class="rounded-xs bg-lime-600/60"></i>
            <i class="rounded-xs bg-lime-600"></i><i class="rounded-xs bg-lime-600/60"></i><i class="rounded-xs bg-violet-600"></i>
        </span>
    </Link>
</template>